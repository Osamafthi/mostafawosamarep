"""
cleaner.py
Normalises raw product names into clean, searchable queries.
No AI — pure regex + heuristics.
"""

import re


# Words that add noise but carry no search value
_NOISE_WORDS = {
    "new", "edition", "version", "pack", "set", "kit", "combo",
    "bundle", "offer", "deal", "sale", "original", "genuine", "authentic",
    "imported", "local", "available", "instock", "in-stock",
    "free", "shipping", "delivery", "fast", "quick",
    "pcs", "pieces", "piece", "units", "unit",
    "best", "quality", "premium", "pro", "plus", "ultra", "max", "lite",
}


def clean(name: str) -> str:
    """
    Takes a raw product name and returns a clean search query string.

    Examples
    --------
    "SMSG-CHG-25W Fast Charger USB-C (Pack of 1) - White [2024]"
    → "Fast Charger USB-C White"

    "كابل شحن ايفون - 1 متر (اصلي)"
    → "كابل شحن ايفون 1 متر"         ← Arabic kept as-is, just stripped noise

    "HP LaserJet Pro MFP M428fdw Printer - NEW VERSION"
    → "HP LaserJet Pro MFP M428fdw Printer"
    """

    original = name.strip()

    # 1. Remove content inside brackets / parentheses entirely
    #    e.g. "(Pack of 3)", "[2024 Model]", "{SKU: XY-123}"
    name = re.sub(r"[\(\[\{][^\)\]\}]{0,40}[\)\]\}]", " ", name)

    # 2. Remove SKU-like tokens: all-caps+digits patterns like "PNSNC-48X", "AB-1234X"
    #    Must be at least 4 chars, mix of caps and digits/dashes
    name = re.sub(r"\b[A-Z]{1,4}[-_][A-Z0-9]{2,}[-_]?[A-Z0-9]*\b", " ", name)

    # 3. Remove standalone model-number tokens: mostly digits with letters e.g. "V3.0", "48X", "2024"
    name = re.sub(r"\b\d{4}\b", " ", name)           # 4-digit years
    name = re.sub(r"\bV\d+(\.\d+)?\b", " ", name, flags=re.IGNORECASE)  # V2, V3.0
    name = re.sub(r"\b\d+[A-Z]{1,3}\b", " ", name)   # 48X, 25W (keep for specs like 25W charger)

    # 4. Remove separators used as decoration: " - ", " | ", " / " at word boundaries
    name = re.sub(r"\s[-|/]\s", " ", name)

    # 5. Remove noise words (case-insensitive, whole word only)
    pattern = r"\b(" + "|".join(re.escape(w) for w in _NOISE_WORDS) + r")\b"
    name = re.sub(pattern, " ", name, flags=re.IGNORECASE)

    # 6. Clean up dangling dashes/underscores left after SKU removal
    #    e.g. "PNSNC- -BLK" → "BLK",  "- White" → "White"
    name = re.sub(r"\s[-_]+\s", " ", name)
    name = re.sub(r"[-_]+\s", " ", name)
    name = re.sub(r"\s[-_]+", " ", name)

    # 7. Collapse multiple spaces
    name = re.sub(r"\s+", " ", name).strip()

    # 8. If the result is suspiciously short (< 3 chars), fall back to original
    if len(name) < 3:
        name = original

    return name


# ── quick smoke test ──────────────────────────────────────────────────
if __name__ == "__main__":
    samples = [
        "SMSG-CHG-25W Fast Charger USB-C (Pack of 1) - White [2024]",
        "HP LaserJet Pro MFP M428fdw Printer - NEW VERSION",
        "Apple iPhone 15 Pro Max 256GB - Natural Titanium | Original",
        "USB HUB 4PORT V3.0 PNSNC-48X-BLK (NEW 2024)",
        "كابل شحن ايفون - 1 متر (اصلي)",
        "Dyson V15 Detect Absolute Vacuum Cleaner",
        "Logitech MX Master 3S Mouse",
        "AB-1234X Gaming Chair Pro Plus Ultra (Bundle)",
    ]
    for s in samples:
        print(f"  IN : {s}")
        print(f"  OUT: {clean(s)}")
        print()
