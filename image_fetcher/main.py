"""
main.py
Orchestrates the full image-fetching pipeline:
  1. Log in to your Laravel API
  2. Fetch all products without images
  3. For each product:
       a. Clean the product name into a search query
       b. Search Bing for candidate images
       c. Try each candidate: download → validate → upload → update product
       d. Record result in state.json
  4. Print a final summary

Usage:
    python main.py                  # normal run (skips already-processed)
    python main.py --retry-failed   # retry products that failed last time
    python main.py --reset          # wipe state and start from scratch
    python main.py --dry-run        # search & download but don't upload or update DB
    python main.py --limit 50       # only process the first N products (useful for testing)
"""

import argparse
import os
import sys
import time

from dotenv import load_dotenv

import state
import cleaner
import searcher
import downloader
from laravel_api import LaravelApi


# ── CLI args ──────────────────────────────────────────────────────────

def parse_args():
    p = argparse.ArgumentParser(description="Auto-fetch product images for your e-commerce site.")
    p.add_argument("--retry-failed", action="store_true",
                   help="Re-process products that failed in a previous run.")
    p.add_argument("--reset", action="store_true",
                   help="Clear state.json and re-process everything.")
    p.add_argument("--dry-run", action="store_true",
                   help="Search & download images but don't upload or update the database.")
    p.add_argument("--limit", type=int, default=0,
                   help="Stop after processing this many products (0 = no limit).")
    return p.parse_args()


# ── Main ──────────────────────────────────────────────────────────────

def main():
    load_dotenv()
    args = parse_args()

    if args.reset:
        state.reset()

    # ── Config from .env ──────────────────────────────────────────────
    min_size   = int(os.getenv("MIN_IMAGE_SIZE", "600"))
    delay      = float(os.getenv("DELAY_BETWEEN_PRODUCTS", "1.0"))
    max_cands  = int(os.getenv("MAX_CANDIDATES", "5"))

    # ── Laravel login ─────────────────────────────────────────────────
    api = LaravelApi()
    try:
        api.login()
    except Exception as exc:
        print(f"[FATAL] Could not log in to Laravel API: {exc}", file=sys.stderr)
        sys.exit(1)

    # ── Fetch products ────────────────────────────────────────────────
    print("[fetch] Getting products without images …")
    try:
        products = api.get_products_without_images()
    except Exception as exc:
        print(f"[FATAL] Could not fetch products: {exc}", file=sys.stderr)
        sys.exit(1)

    print(f"[fetch] {len(products)} product(s) need images.")

    # Filter out already-processed ones (unless retrying failures)
    if args.retry_failed:
        failed = state.failed_ids()
        products = [p for p in products if p["id"] not in state.done_ids()
                                          and p["id"] not in state.skipped_ids()]
        print(f"[state] Retrying {len(products)} product(s) (including previously failed).")
    else:
        products = [p for p in products if not state.already_processed(p["id"])]
        print(f"[state] {len(products)} product(s) left to process. {state.summary()}")

    if args.limit > 0:
        products = products[: args.limit]
        print(f"[limit] Processing first {args.limit} product(s) only.")

    if not products:
        print("[done] Nothing left to do.")
        return

    # ── Process each product ──────────────────────────────────────────
    counts = {"done": 0, "failed": 0, "error": 0}

    for idx, product in enumerate(products, start=1):
        pid   = product["id"]
        name  = product.get("name", "")
        query = cleaner.clean(name)

        print(f"\n[{idx}/{len(products)}] ID={pid}  '{name}'")
        print(f"          Query: '{query}'")

        # Search
        try:
            candidates = searcher.search(query, min_size=min_size, max_results=max_cands)
        except Exception as exc:
            print(f"    [search] ERROR: {exc}")
            state.mark_failed(pid)
            counts["error"] += 1
            time.sleep(delay)
            continue

        if not candidates:
            print(f"    [search] No candidates found — skipping.")
            state.mark_failed(pid)
            counts["failed"] += 1
            time.sleep(delay)
            continue

        print(f"    [search] {len(candidates)} candidate(s) found.")

        # Try each candidate until one succeeds
        success = False
        for ci, cand in enumerate(candidates, start=1):
            url = cand["content_url"]
            w, h = cand["width"], cand["height"]
            print(f"    [try {ci}] {w}×{h}  {url[:80]}…")

            tmp_path = downloader.download(url, min_size=min_size)
            if tmp_path is None:
                print(f"    [try {ci}] Download/validation failed — next candidate.")
                continue

            if args.dry_run:
                print(f"    [dry-run] Would upload {tmp_path} — skipping actual upload.")
                os.unlink(tmp_path)
                success = True
                break

            # Upload to Laravel
            uploaded_url = api.upload_image(tmp_path)
            os.unlink(tmp_path)  # always clean up the temp file

            if not uploaded_url:
                print(f"    [try {ci}] Upload failed — next candidate.")
                continue

            # Update the product record
            ok = api.set_product_image(pid, uploaded_url)
            if ok:
                print(f"    [✓] Image set: {uploaded_url}")
                state.mark_done(pid)
                counts["done"] += 1
                success = True
                break
            else:
                print(f"    [try {ci}] DB update failed — next candidate.")

        if not success:
            print(f"    [✗] All candidates exhausted — marking as failed.")
            state.mark_failed(pid)
            counts["failed"] += 1

        time.sleep(delay)

    # ── Summary ───────────────────────────────────────────────────────
    print(f"""
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  Run complete
  ✓ Updated : {counts['done']}
  ✗ Failed  : {counts['failed']}
  ⚠ Errors  : {counts['error']}
  State     : {state.summary()}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
""")
    if counts["failed"] + counts["error"] > 0:
        print("Tip: run with --retry-failed to try again for the ones that didn't work.")


if __name__ == "__main__":
    main()
