"""
state.py
Persists progress to state.json so the script can be safely stopped
and resumed without re-processing already-handled products.

State file schema:
{
    "done":   [1, 2, 5, ...],    // product IDs successfully updated
    "failed": [3, 4, ...],       // product IDs where no good image was found
    "skipped": [6, ...]          // product IDs explicitly skipped by user (--skip flag)
}
"""

import json
import os
from typing import Set


STATE_FILE = os.path.join(os.path.dirname(__file__), "state.json")


def _load() -> dict:
    if os.path.exists(STATE_FILE):
        with open(STATE_FILE, "r", encoding="utf-8") as f:
            return json.load(f)
    return {"done": [], "failed": [], "skipped": []}


def _save(state: dict) -> None:
    with open(STATE_FILE, "w", encoding="utf-8") as f:
        json.dump(state, f, indent=2)


def done_ids() -> Set[int]:
    return set(_load().get("done", []))


def failed_ids() -> Set[int]:
    return set(_load().get("failed", []))


def skipped_ids() -> Set[int]:
    return set(_load().get("skipped", []))


def already_processed(product_id: int) -> bool:
    s = _load()
    processed = set(s.get("done", [])) | set(s.get("failed", [])) | set(s.get("skipped", []))
    return product_id in processed


def mark_done(product_id: int) -> None:
    s = _load()
    if product_id not in s["done"]:
        s["done"].append(product_id)
    _save(s)


def mark_failed(product_id: int) -> None:
    s = _load()
    if product_id not in s["failed"]:
        s["failed"].append(product_id)
    _save(s)


def mark_skipped(product_id: int) -> None:
    s = _load()
    if product_id not in s["skipped"]:
        s["skipped"].append(product_id)
    _save(s)


def reset() -> None:
    """Wipe the state file — forces a full re-run next time."""
    if os.path.exists(STATE_FILE):
        os.unlink(STATE_FILE)
    print("[state] State file cleared.")


def summary() -> str:
    s = _load()
    return (
        f"done={len(s.get('done', []))}  "
        f"failed={len(s.get('failed', []))}  "
        f"skipped={len(s.get('skipped', []))}"
    )
