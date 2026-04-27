# Customer store (Flutter)

Mobile client for the Laravel API under `../new-backend` (`/api/v1`). Matches the PHP storefront in `../views/customer/` with a bottom navigation shell (Home, Search, Cart, Account).

## Prerequisites

- Flutter SDK (Dart 3.3+) on a machine where the toolchain runs (this repo was authored on a host where Flutter requires macOS 14+; if `flutter --version` fails, use another machine or CI).
- Backend: `cd ../new-backend && php artisan serve` (optionally `--host 0.0.0.0` for devices on LAN).
- Queue worker for emails: `php artisan queue:work database`.

## First-time setup

From this directory:

```bash
flutter pub get
```

If Android/iOS folders are incomplete or Gradle errors appear, regenerate platform scaffolding **without** overwriting `lib/`:

```bash
# Backup lib, then:
flutter create . --org com.mostafawosama --platforms=android,ios
# Restore lib/ if needed
```

Copy `android/local.properties` from a working Flutter app and set `sdk.dir` and `flutter.sdk`, or let Android Studio create it.

## API base URL (`--dart-define`)

The app reads **`API_BASE_URL`** at compile time (no secrets in source).

| Target | Example |
|--------|---------|
| iOS Simulator | `http://localhost:8000/api/v1` |
| Android Emulator | `http://10.0.2.2:8000/api/v1` |
| Physical device (same Wi‑Fi as PC) | `http://<your-LAN-IP>:8000/api/v1` |

Run:

```bash
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

Default in code if omitted: `http://localhost:8000/api/v1`.

## Backend images (relative paths)

If product `image_url` values are relative (e.g. `/mostafawosama/assets/...`), set in **`new-backend/.env`**:

- `PUBLIC_ASSET_URL=http://localhost/mostafawosama` (or your real XAMPP base)

so the API returns absolute URLs (see `App\Support\AssetUrl`).

## Security notes

- Customer token is stored with **flutter_secure_storage** only.
- **Android**: `network_security_config` allows cleartext for local dev; use HTTPS in production and remove or tighten that config.
- **iOS**: add `NSLocationWhenInUseUsageDescription` in `ios/Runner/Info.plist` if you use checkout location (template projects usually add this when you run `flutter create`).

## Features

- Home (categories + strips + hero), search, category grid, product detail, local cart, checkout (`POST /orders` with optional Bearer), login/register, profile, my orders + detail.
