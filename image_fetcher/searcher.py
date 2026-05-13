"""
searcher.py
Scrapes DuckDuckGo Images — no API key, no account, completely free.
"""

import requests
import json
import re
from typing import List, Dict


def search(query: str, min_size: int = 600, max_results: int = 5) -> List[Dict]:
    session = requests.Session()
    session.headers.update({
        "User-Agent": (
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/124.0.0.0 Safari/537.36"
        )
    })

    # Step 1: get a vqd token (DuckDuckGo requires this for image searches)
    resp = session.get(
        "https://duckduckgo.com/",
        params={"q": query, "iax": "images", "ia": "images"},
        timeout=10,
    )
    vqd_match = re.search(r'vqd=(["\'])([^"\']+)\1', resp.text)
    if not vqd_match:
        # fallback pattern
        vqd_match = re.search(r'vqd=([\d-]+)', resp.text)
        if not vqd_match:
            raise RuntimeError("Could not extract vqd token from DuckDuckGo")
        vqd = vqd_match.group(1)
    else:
        vqd = vqd_match.group(2)

    # Step 2: call the images endpoint
    resp = session.get(
        "https://duckduckgo.com/i.js",
        params={
            "q":   query,
            "vqd": vqd,
            "f":   ",,,,,",   # no filters
            "p":   "1",
            "v7exp": "a",
        },
        headers={"Referer": "https://duckduckgo.com/"},
        timeout=10,
    )
    resp.raise_for_status()

    results = resp.json().get("results", [])

    candidates = []
    for item in results:
        w   = item.get("width", 0)
        h   = item.get("height", 0)
        url = item.get("image", "")
        if not url or w < min_size or h < min_size:
            continue
        candidates.append({
            "content_url":   url,
            "thumbnail_url": item.get("thumbnail", ""),
            "width":         w,
            "height":        h,
            "name":          item.get("title", ""),
        })

    candidates.sort(key=lambda c: c["width"] * c["height"], reverse=True)
    return candidates[:max_results]