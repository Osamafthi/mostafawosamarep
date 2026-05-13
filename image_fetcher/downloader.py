"""
downloader.py
Downloads a candidate image URL to a temp file and validates it.
Returns the local file path on success, None on failure.
"""

import os
import tempfile
import requests
from PIL import Image


# Formats we accept (Pillow format names)
ALLOWED_FORMATS = {"JPEG", "PNG", "WEBP"}

# Hard minimum after actually opening the file with Pillow
HARD_MIN_PX = 400


def download(url: str, min_size: int = 600) -> str | None:
    """
    Download the image at `url` to a temp file.

    Validation steps:
      1. HTTP request succeeds and Content-Type starts with image/
      2. File is a valid image (Pillow can open it)
      3. Format is JPEG, PNG, or WEBP
      4. Both dimensions are >= min_size (or HARD_MIN_PX if min_size is lower)

    Returns the temp file path (str) on success, or None on any failure.
    The caller is responsible for deleting the temp file after use.
    """
    effective_min = max(min_size, HARD_MIN_PX)

    headers = {
        # Pretend to be a browser so servers don't reject us
        "User-Agent": (
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/124.0.0.0 Safari/537.36"
        ),
        "Accept": "image/webp,image/apng,image/*,*/*;q=0.8",
    }

    try:
        resp = requests.get(url, headers=headers, timeout=15, stream=True)
        resp.raise_for_status()

        content_type = resp.headers.get("Content-Type", "")
        if not content_type.startswith("image/"):
            return None  # not an image

        # Write to a named temp file with the right extension
        ext = _ext_from_content_type(content_type)
        tmp = tempfile.NamedTemporaryFile(suffix=ext, delete=False)
        for chunk in resp.iter_content(chunk_size=8192):
            tmp.write(chunk)
        tmp.close()

    except requests.exceptions.RequestException:
        return None

    # Validate with Pillow
    try:
        with Image.open(tmp.name) as img:
            fmt = img.format
            w, h = img.size

        if fmt not in ALLOWED_FORMATS:
            os.unlink(tmp.name)
            return None

        if w < effective_min or h < effective_min:
            os.unlink(tmp.name)
            return None

    except Exception:
        # Pillow couldn't open it → corrupted or unsupported
        try:
            os.unlink(tmp.name)
        except OSError:
            pass
        return None

    return tmp.name


def _ext_from_content_type(content_type: str) -> str:
    """Map Content-Type to file extension."""
    ct = content_type.lower()
    if "webp" in ct:
        return ".webp"
    if "png" in ct:
        return ".png"
    return ".jpg"
