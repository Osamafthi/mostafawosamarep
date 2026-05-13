"""
laravel_api.py
Thin wrapper around your Laravel REST API.
Handles login, fetching all products, uploading an image, and updating a product.
"""

import os
import sys
import requests
from typing import List, Dict, Optional


class LaravelApi:
    def __init__(self):
        self.base = os.environ["LARAVEL_API_BASE"].rstrip("/")
        self.token: Optional[str] = None
        self._session = requests.Session()

    # ── Auth ──────────────────────────────────────────────────────────

    def login(self) -> None:
        """Log in as admin and store the Sanctum bearer token."""
        email    = os.environ["LARAVEL_ADMIN_EMAIL"]
        password = os.environ["LARAVEL_ADMIN_PASSWORD"]

        resp = self._session.post(
            f"{self.base}/auth/admin/login",
            json={"email": email, "password": password},
            timeout=10,
        )
        self._raise_for_api_error(resp, "Login")

        data = resp.json()
        self.token = data["data"]["token"]
        self._session.headers.update({"Authorization": f"Bearer {self.token}"})
        print(f"[auth] Logged in as {email}")

    # ── Products ──────────────────────────────────────────────────────

    def get_all_products(self) -> List[Dict]:
        """
        Fetches every product from the admin endpoint, handling pagination.
        Returns a flat list of product dicts.
        """
        products = []
        page = 1
        limit = 100  # max per page

        while True:
            resp = self._session.get(
                f"{self.base}/admin/products",
                params={"page": page, "limit": limit, "status": "active"},
                timeout=15,
            )
            self._raise_for_api_error(resp, f"Fetch products page {page}")

            data = resp.json()["data"]
            items = data.get("items", [])
            products.extend(items)

            if page >= data.get("last_page", 1):
                break
            page += 1

        return products

    def get_products_without_images(self) -> List[Dict]:
        """
        Returns only products that have no image set yet.
        An image is considered missing if image_url is null, empty, or a
        placeholder like the seeder's random assignment.
        """
        all_products = self.get_all_products()
        missing = []
        for p in all_products:
            img = p.get("image_url") or ""
            # Treat seeder images (assets/js/uploads/) as "missing" too
            # because they're random placeholders, not real product images.
            # Comment out the second condition if you only want truly null images.
            if not img or "assets/js/uploads" in img:
                missing.append(p)
        return missing

    # ── Upload ────────────────────────────────────────────────────────

    def upload_image(self, file_path: str) -> Optional[str]:
        """
        Upload a local image file via POST /admin/upload (multipart).
        Returns the public URL string on success, None on failure.
        """
        with open(file_path, "rb") as f:
            resp = self._session.post(
                f"{self.base}/admin/upload",
                files={"file": f},
                timeout=30,
            )

        if not resp.ok:
            print(f"    [upload] HTTP {resp.status_code}: {resp.text[:200]}")
            return None

        data = resp.json()
        if not data.get("success"):
            print(f"    [upload] API error: {data.get('error')}")
            return None

        return data["data"].get("url")

    # ── Update product ────────────────────────────────────────────────

    def set_product_image(self, product_id: int, image_url: str) -> bool:
        """
        PATCH the product's image_url field.
        Uses PUT /admin/products/{id} with only the image_url field changed
        (the endpoint merges, so other fields are unchanged).
        """
        resp = self._session.put(
            f"{self.base}/admin/products/{product_id}",
            json={"image_url": image_url},
            timeout=10,
        )

        if not resp.ok:
            print(f"    [update] HTTP {resp.status_code}: {resp.text[:200]}")
            return False

        data = resp.json()
        if not data.get("success"):
            print(f"    [update] API error: {data.get('error')}")
            return False

        return True

    # ── Helpers ───────────────────────────────────────────────────────

    @staticmethod
    def _raise_for_api_error(resp: requests.Response, context: str) -> None:
        if not resp.ok:
            try:
                msg = resp.json().get("error", resp.text[:200])
            except Exception:
                msg = resp.text[:200]
            print(f"[ERROR] {context} failed ({resp.status_code}): {msg}", file=sys.stderr)
            resp.raise_for_status()
