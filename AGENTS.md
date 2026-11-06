# Agent Guidance

> **Purpose:** This file is written for AI coding agents that need to work in this repository. It assumes you know Laravel/PHP/JavaScript in general, but nothing about this specific project.
>
> The previous version of this file only pointed to [`CLAUDE.md`](./CLAUDE.md). That pointer is preserved below, but this document has been expanded with a concrete project overview, architecture summary, commands, conventions, testing strategy, and deployment notes derived from the actual source code.

## Further Reading (Human-Facing Docs)

- [`CLAUDE.md`](./CLAUDE.md) — detailed agent guidance: domain model, translation rules, caching patterns, AR/maps/payments, common issues.
- [`README.md`](./README.md) — high-level product overview, feature list, stack table, setup steps (mostly in Indonesian).
- [`DEPLOY.md`](./DEPLOY.md) — Docker Compose deployment guide for VPS/Hostinger (Indonesian).
- [`DESIGN.md`](./DESIGN.md) — mobile-first UI/UX constraints: color palette, typography, components (Indonesian).

---

## User Preferences

> **Do not auto-commit.** After making changes, leave them in the working tree and wait for the user to review/approve before running `git commit`.

---

## 1. Project Overview

**Ganesha Smart Edutourism** is a Laravel 13 mobile-first web application for **Desa Wisata Penglipuran, Bali**. It is designed as a "Super App" (Grab/Gojek-style modular grid) optimized for outdoor, on-site tourist use.

### What It Does

- Publishes multilingual cultural-object stories, audio narration, quizzes, and events.
- Provides an interactive Leaflet map with geocoded locations (cultural sites, UMKM, facilities).
- Serves AR content: marker-based AR.js/A-Frame camera plus iOS AR Quick Look via USDZ models.
- Tickets are sold externally via OTAs (e.g. Traveloka); staff scan them on-site with `TicketScanController` to log visits and deduplicate re-scans via `code_hash`.
- Lets UMKM owners manage their business profile and products.
- Provides real-time crowd tracking and capacity alerts via Laravel Reverb.

### User Roles

Roles are defined in `app/Enums/UserRole.php`:

| Role | Value | Access |
|------|-------|--------|
| Admin | `admin` | `/admin/*` — full system management |
| Ticket Officer | `ticket_officer` | `/staff/*` — scans OTA-purchased tickets (QR) for visit logging |
| UMKM Owner | `umkm_owner` | `/owner/*` — own profile & products |
| Tourist | `tourist` | public + authenticated features |
| Guest | — | public pages only (`/`, `/explore`, `/cultural`, `/umkm`, `/ar-scan`, etc.) |

---

## 2. Technology Stack

| Layer | Technology | Notes |
|-------|------------|-------|
| Backend framework | Laravel 13 | PHP 8.4 required |
| Language | PHP 8.4 | Uses native enum attributes (`#[Fillable]`, `#[Hidden]`) |
| Auth | Session-based + Google OAuth (Socialite) | Sanctum is installed but not used for API auth |
| Database (dev/prod) | MySQL 8.0 | Container name `penglipuran-db` in Docker |
| Database (testing) | SQLite in-memory | `phpunit.xml` sets `DB_CONNECTION=sqlite` |
| Cache / queue / session | Redis | `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis` |
| Real-time | Laravel Reverb + Echo + Pusher-JS | WebSocket server on port `8081` by default |
| Frontend build | Vite 8 | Entry: `resources/css/app.css`, `resources/js/app.js` |
| CSS | TailwindCSS 4.x | `@import "tailwindcss"` in `resources/css/app.css` |
| JS framework | Alpine.js 3 | Sprinkled on Blade views |
| Livewire | Livewire 4.x | Used **only** for navigation, not for components |
| Maps / routing | Leaflet + OpenRouteService | ORS is self-hosted in Docker |
| Large uploads | TUS chunked protocol | `ankitpokhrel/tus-php`, admin AR model uploads |
| QR codes | `bacon/bacon-qr-code` | `qrSvgDataUri()` helper in `app/helpers.php` |
| Rich text | TipTap via `esm.sh` | No local NPM TipTap dependency |
| Translations | `spatie/laravel-translatable` + Laravel `__()` | Dual-layer i18n |
| Spreadsheets | `phpoffice/phpspreadsheet` | Admin XLSX import for cultural objects |
| Log viewer | `opcodesio/log-viewer` | Admin-only gate in `AppServiceProvider` |
| Code formatter | Laravel Pint | No custom config; default Laravel preset |
| CI | GitHub Actions | `.github/workflows/laravel.yml` |

---

## 3. Repository Layout

```text
app/
  Console/Commands/        # Artisan commands (weather, reminders, TUS cleanup, expiry)
  Enums/                   # UserRole
  Events/                  # CrowdAlertSent, VisitorLocationUpdated, etc.
  Http/
    Concerns/              # Reusable controller traits (file uploads, geo, multilingual normalization)
    Controllers/
      Admin/               # CRUD/dashboard controllers for admin role
      Api/                 # RoutingController, TusController
      Owner/               # UMKM owner controllers
      Staff/               # TicketScanController
      * public controllers (Home, Explore, Cultural, UMKM, etc.)
    Middleware/            # Admin, Staff, UmkmOwner, SetUserLocale, SecurityHeaders, RedirectIfAdmin
    Requests/              # Form request classes per role
    Resources/             # API resources (CulturalObject, UMKM)
  Models/                  # Eloquent models
  Models/Concerns/         # HasSlug, HasMapLocation, HasLocalizedAudioNarration, HasTranslatableArrayOutput
  Notifications/           # (package-driven push notifications)
  Observers/               # CacheInvalidationObserver
  Providers/               # AppServiceProvider
  Services/                # TusService, UmkmRecommendationService
  helpers.php              # Global helpers: translateValue(), qrSvgDataUri(), etc.

bootstrap/app.php          # Laravel application bootstrap; registers middleware aliases & CSRF excepts

config/                    # Standard Laravel configs + admin.php

database/
  factories/               # Model factories
  migrations/              # 65 migration files
  seeders/                 # Admin, Staff, routes, entrance tickets, etc.

docs/                      # Extra documentation / screenshots

lang/
  en.json, id.json         # Flat UI strings for public views
  en/, id/                 # PHP validation/auth message files

nginx/default.conf         # Nginx config used in Docker; proxies /app to Reverb

openrouteservice/          # ORS config, graphs, files, logs; started via start-ors.sh

public/                    # Web root; Vite build output at public/build/

resources/
  css/app.css              # Tailwind theme, custom components, Leaflet overrides
  js/
    app.js                 # Swal mixin, imports echo.js
    echo.js                # Laravel Echo + Reverb client config
  views/                   # Blade templates per role (admin, owner, staff, user, auth, layouts, components)

routes/
  web.php                  # All web routes (public, auth, admin, owner, staff)
  api.php                  # AR model lookup, tracking ping/leave
  channels.php             # Private user channel
  console.php              # Scheduled commands

scripts/i18n-sync.mjs      # Scans __() keys and syncs lang/*.json via LibreTranslate

storage/app/public/        # Uploaded files: ar_models, ar_markers, cultural_objects, etc.

tests/
  Browser/                 # Laravel Dusk tests
  Feature/                 # PHPUnit HTTP/feature tests
  Unit/                    # PHPUnit unit tests
  DuskTestCase.php
  TestCase.php
```

---

## 4. Build, Run, and Test Commands

### Initial Setup

```bash
cp .env.example .env
composer setup        # install PHP deps, create .env if missing, generate key, migrate, npm install --ignore-scripts, npm run build
```

### Daily Development

```bash
composer dev          # concurrently runs: php artisan serve, queue:listen, pail, npm run dev
npm run dev           # Vite dev server only
npm run build         # Production Vite build
```

### Code Quality

```bash
vendor/bin/pint --dirty --format agent   # format only changed files before commit
vendor/bin/pint                          # format whole project
```

No `pint.json` exists, so the default Laravel preset is used. `.editorconfig` enforces UTF-8, LF, 4-space indentation (2 for YAML), final newlines, and trimmed trailing whitespace.

### Testing

```bash
composer test                          # php artisan test (Unit + Feature suites, SQLite in-memory)
php artisan test --filter=ARScannerTest
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Dusk (browser tests; uses .env.dusk.local and starts chromedriver automatically)
php artisan dusk
```

### Sharing / Tunneling

```bash
composer share         # Local dev exposed via Cloudflare Tunnel + weather polling + Reverb + watched build
npm run host           # Just the Cloudflare tunnel
```

### Useful Artisan Commands

```bash
php artisan app:update-weather           # fetch weather data
php artisan app:send-event-reminders     # send event reminder emails
php artisan app:cleanup-tus              # clean stale TUS temp files
php artisan reverb:start                 # WebSocket server
php artisan queue:listen --tries=1       # queue worker
php artisan schedule:run                 # run scheduled commands once
```

### i18n Sync

```bash
npm run i18n                  # dry-run missing/orphan keys
npm run i18n:write            # auto-translate missing keys via LibreTranslate and write files
npm run i18n:cleanup-orphans  # remove unused keys
```

Requires the LibreTranslate container running (`docker compose up -d libretranslate`).

---

## 5. Architecture & Code Organization

### Request Lifecycle

1. `routes/web.php` groups routes by role.
2. Global web middleware: `SecurityHeaders`, `SetUserLocale`.
3. Role middleware aliases: `admin`, `staff`, `umkm_owner`, `redirect.admin`.
4. Controllers delegate to `app/Services/` for domain logic.
5. Eloquent observers auto-flush relevant cache tags.
6. Queue jobs / events handle emails (event reminders) and real-time broadcasts.

### Controllers by Area

- **Public:** `HomeController`, `ExploreController`, `CulturalController`, `UmkmCatalogController`, `SmartEdutourismController`, `ARController`, `AuthController`, etc.
- **Admin (`/admin/*`):** `DashboardController`, `CulturalObjectController`, `UmkmController`, `TourRouteController`, `CapacityController`, `ARManagerController`, `MapManagerController`, `ReportController`, `SettingsController`, etc.
- **Owner (`/owner/*`):** `OwnerDashboardController`, `OwnerProductController`, `OwnerCategoryController`.
- **Staff (`/staff/*`):** `TicketScanController` — scan/check/store/stats for OTA ticket visit logging.
- **API (`/api/*`):** `RoutingController`, `TusController`, `TrackingController`.

### Middleware

| Middleware | Purpose |
|------------|---------|
| `SetUserLocale` | Sets locale from `?locale=`, user preference, session, or config default (`id`) |
| `SecurityHeaders` | Adds security response headers |
| `RedirectIfAdmin` | Redirects authenticated admins/owners/staff away from public auth pages to their dashboards |
| `AdminMiddleware` | Restricts to `admin` role |
| `StaffMiddleware` | Restricts to `admin` or `ticket_officer` |
| `UmkmOwnerMiddleware` | Restricts to `umkm_owner` role |

### Service Layer

- `UmkmRecommendationService` — fair-rotation recommendation algorithm using geolocation and `last_recommended_at`.
- `TusService` — resolves TUS temp files and moves them to final storage.

### Models & Concerns

Core models: `User`, `CulturalObject`, `CulturalObjectRating`, `CulturalStory`, `Facility`, `MapLocation`, `TourRoute`, `TourRoutePoint`, `RouteMission`, `RouteSession`, `TicketScan`, `Event`, `UmkmProfile`, `UmkmProduct`, `UmkmProductCategory`, `ArModel`, `CapacityZone`, `VisitorLog`, `WeatherReport`, `Feedback`, `UserFavorite`, `UserVisit`, `VillageSettings`.

Reusable traits in `app/Models/Concerns/`:

- `HasMapLocation` — `syncMapLocation()` for polymorphic map pins.
- `HasSlug` — generates slugs from translatable fields, including collision-free variants.
- `HasLocalizedAudioNarration` — audio narration helpers.
- `HasTranslatableArrayOutput` — ensures translatable fields serialize as arrays in `toArray()`.

### Caching

- `CACHE_STORE=redis`.
- Public data is cached with tags and Stale-While-Revalidate: `Cache::tags(['...'])->flexible('key', [$freshSec, $staleSec], fn)`.
- `CacheInvalidationObserver` is registered in `AppServiceProvider` for content models; it flushes cache tags on `saved`/`deleted`.
- **Do not cache Eloquent models/collections directly** (`serializable_classes=false` in Laravel 13). Always cache `->toArray()` and include `_array_` in the cache key.
- Always include the locale in cache keys when content is localized.

### Events, Jobs, and Broadcasting

- Events: `CrowdAlertSent`, `VisitorLocationUpdated`, `VisitorLocationRemoved`, `EventReminderSent`.
- Real-time: GPS pings hit `TrackingController`, stored in Redis `active_visitors`, broadcast via Reverb; client config in `resources/js/echo.js`.

### File Uploads

- **Small files:** standard multipart uploads; stored under `storage/app/public/`.
- **Large AR files:** TUS chunked upload to `admin/api/tus/upload`; CSRF is exempted in `bootstrap/app.php`.
- Common storage paths:
  - AR models: `storage/app/public/ar_models/`
  - AR markers: `storage/app/public/ar_markers/`
  - Cultural images: `storage/app/public/cultural_objects/`
  - Audio: streamed via `/audio-stream/{path}` for range-request support.
  - USDZ: served via `/usdz-file/{path}` for correct iOS headers.

### Maps & Routing

- Leaflet maps.
- OpenRouteService is self-hosted locally (`start-ors.sh`) or via Docker Compose.
- `Api\RoutingController` proxies turn-by-turn directions.
- `CapacityZone` stores polygon `geofence` JSON and crowd thresholds.

### Ticket Scanning

- No online booking or payment gateway. Tickets are sold via OTAs (e.g. Traveloka); staff scan the QR at the gate.
- `Staff\TicketScanController` (`index`, `check`, `store`, `stats`) records each scan in `TicketScan`, using a unique `code_hash` to detect duplicate/repeat scans.
- Routes: `staff.ticketing`, `staff.ticketing.check`, `staff.ticketing.store`, `staff.ticketing.stats`.
- **Access:** `StaffMiddleware` admits `admin` and `ticket_officer` with full access, and `admin_viewer` strictly with read-only access (GET/HEAD only). `admin_viewer` cannot mutate ticketing data via POST (`staff.ticketing.check` and `staff.ticketing.store` return 403 Forbidden).

---

## 6. Development Conventions & Style

### PHP Style

- PHP 8.4 syntax: typed properties, enums, native attributes (`#[Fillable]`, `#[Hidden]`), constructor property promotion where appropriate.
- Use PSR-4 autoloading (`App\`, `Database\Factories\`, `Database\Seeders\`, `Tests\`).
- Run `vendor/bin/pint --dirty --format agent` before committing.
- Keep controllers thin; put reusable logic in `Services` or `Concerns`.
- Prefer Laravel form requests in `app/Http/Requests/` for validation.

### Multilingual Rules (Critical)

This project uses **two independent i18n layers**:

1. **UI strings** — `__('Some text')` in Blade/PHP. Keys live in `lang/en.json` and `lang/id.json`.
2. **Content fields** — `spatie/laravel-translatable` stores JSON like `{"en":"...","id":"..."}` in the database.

**Hard rules:**

- Public-facing views (`/`) use `__()` and the i18n-sync script manages them.
- **Dashboard views (`resources/views/admin/*`, `owner/*`, `staff/*`) must use hardcoded English strings.** Do **not** use `__()` there.
- When writing translatable fields programmatically (seeders, factories, imports, tinker), always pass a locale-keyed array:

```php
// CORRECT
$model->name = ['id' => 'Nama Indonesia', 'en' => 'English Name'];

// WRONG — will store under the current app locale and leave the other empty
$model->name = 'Nama Indonesia';
```

- Use `translateValue($model->name)` or `translateValue($model->name, 'en')` to safely read translatable values (handles arrays, JSON strings, and plain strings).
- Use `NormalizesMultilingualInput` in controllers when a translatable field may arrive as either a plain string or an array.
- Cache keys that depend on localized data must include the locale suffix.

### Frontend Conventions

- Mobile-first, outdoor-readable design. See `DESIGN.md` for the full visual system.
- Tailwind v4 theme variables in `resources/css/app.css` define `--color-primary` (#1E5128), `--color-secondary` (#D4AF37), etc.
- Use `Playfair Display` **only** for cultural/editorial headlines; UI text uses `Plus Jakarta Sans` / `Inter`.
- Minimum tap target: `44px × 44px` (the project defines `.tap-target`).
- Use skeleton loading (pulse) instead of spinners.
- Use bottom sheets instead of full-page modals for short details.
- Haptic feedback (`navigator.vibrate(50)`) on core actions.

### Global Helpers

Loaded via `app/helpers.php`:

- `translateValue($value, $locale = null)`
- `slugFromTranslatable(array $translations)`
- `qrSvgDataUri(string $data, int $size = 250)`

---

## 7. Testing Strategy

### PHPUnit (Unit + Feature)

- Configuration: `phpunit.xml`.
- Environment: `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`.
- Suites: `Unit` and `Feature`.
- Feature tests cover HTTP routes, auth, admin CRUD, ticket scanning, caching, localization, UMKM, edutourism, etc.
- Unit tests cover services, model traits, helpers, and AR upload handling.
- External APIs (OpenRouteService, Weather) should be mocked in tests.

### Laravel Dusk (Browser Tests)

- Configuration: `phpunit.dusk.xml`.
- Tests live in `tests/Browser/` and cover end-to-end flows: authentication, public explore, admin capacity zones, admin tour routes, UMKM management, owner products, and smart edutourism.
- `DuskTestCase.php` starts chromedriver automatically unless running in Sail.
- Dusk reads from `.env.dusk.local` (do not commit secrets).

### Running Tests

```bash
composer test
php artisan test --filter=<TestName>
php artisan dusk
```

### CI

`.github/workflows/laravel.yml` runs on pushes/PRs to `main`:

1. Sets up PHP 8.4.
2. Copies `.env.example` to `.env`.
3. Installs Composer and npm dependencies.
4. Builds Vite assets.
5. Generates application key.
6. Creates `database/database.sqlite`.
7. Runs `php artisan test` against SQLite.

Dusk tests are **not** currently run in CI.

---

## 8. Security Considerations

- **CSRF:** Standard web routes require CSRF. The TUS upload endpoints (`admin/api/tus/upload` and `admin/api/tus/upload/*`) are explicitly exempted in `bootstrap/app.php`.
- **Proxy trust:** `bootstrap/app.php` calls `$middleware->trustProxies(at: '*')` to support reverse proxies (Docker/Traefik/Cloudflare).
- **Security headers:** `App\Http\Middleware\SecurityHeaders` is applied to all web routes.
- **HTTPS:** `AppServiceProvider` forces `https` scheme in local development when `HTTP_X_FORWARDED_PROTO=https` is present.
- **Admin log viewer:** access is gated by a `viewLogViewer` Gate requiring `isAdmin()`.
- **Secrets:** store all credentials in `.env` only. The repository contains `.env.example` with placeholder values; never commit real keys.
- **File uploads:** Nginx and PHP are configured for up to `200M` body size in Docker. Validate and sanitize uploaded file names and paths; AR/USDZ/PATT files are stored under `storage/app/public/`.
- **WebPush:** VAPID keys should be generated with `php artisan webpush:vapid` and stored in `.env`.

---

## 9. Deployment & Infrastructure

### Docker Compose (Recommended for Production)

`docker-compose.yml` defines:

| Service | Purpose |
|---------|---------|
| `penglipuran-app` | Laravel + Nginx (port `80` by default) |
| `penglipuran-queue` | Queue worker (`queue:work`) |
| `penglipuran-reverb` | WebSocket server (port `8081`) |
| `penglipuran-db` | MySQL 8.0 (port `3306`) |
| `penglipuran-redis` | Redis 7 (port `6379`) |
| `penglipuran-ors` | OpenRouteService (port `8080` mapped to container `8082`) |
| `penglipuran-libretranslate` | LibreTranslate for admin auto-translate |
| `penglipuran-pma` | phpMyAdmin |

### Deployment Script

```bash
./deploy.sh        # pull branch, download Bali OSM extract, build images, migrate, optimize caches
./deploy.sh --seed # same, plus seed the database
```

What `deploy.sh` does:

1. Pulls latest code from the current Git branch.
2. Downloads the Bali OSM PBF for OpenRouteService if missing.
3. Builds and starts Docker Compose services.
4. Waits for MySQL.
5. Generates `APP_KEY` if empty.
6. Runs migrations (`--force`).
7. Optionally seeds the database.
8. Caches config, routes, and views.

### Local OpenRouteService (Non-Docker)

```bash
./start-ors.sh   # wait ~2 minutes for graph build
./stop-ors.sh
```

### Scheduled Tasks

`routes/console.php` registers:

- `app:update-weather` — every 10 minutes.
- `events:send-reminders` — every minute.
- `app:cleanup-tus` — daily.

In production, run `php artisan schedule:run` every minute via cron or use the Docker `penglipuran-app` container's scheduler.

### Reverse Proxy / HTTPS

The compose file includes Traefik labels by default and comments out host port bindings, assuming an external Traefik reverse proxy. `DEPLOY.md` explains how to enable Hostinger Traefik for automatic Let's Encrypt SSL. For local sharing, `composer share` uses Cloudflare Tunnel.

### Multi-Environment on One VPS

Use distinct `COMPOSE_PROJECT_NAME` values in `.env` for each clone (e.g., `penglipuran-prod` vs `penglipuran-dev`). Docker will isolate containers, networks, and volumes. See `DEPLOY.md` for port and domain separation guidelines.

---

## 10. Important Environment Variables

Key variables from `.env.example`:

| Variable | Purpose |
|----------|---------|
| `APP_URL` | Public URL of the app |
| `APP_PORT` | Host port for the app in Docker (default `80`) |
| `DB_CONNECTION` | `mysql` for dev/prod, `sqlite` for testing |
| `DB_HOST` | `penglipuran-db` in Docker |
| `CACHE_STORE`, `QUEUE_CONNECTION`, `SESSION_DRIVER` | Should be `redis` in Docker |
| `REDIS_HOST` | `penglipuran-redis` in Docker |
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` | Google OAuth |
| `REVERB_*` | WebSocket credentials |
| `ORS_BASE_URL` | OpenRouteService URL (`http://penglipuran-ors:8082` in Docker) |
| `LIBRETRANSLATE_URL` | Auto-translate service (`http://penglipuran-libretranslate:5000` in Docker) |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Default admin credentials for seeders |
| `PENGLIPURAN_LAT`, `PENGLIPURAN_LON`, `PENGLIPURAN_ZOOM` | Default map center |
| `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY` | Web Push notifications |
| `TOUR_GUIDE_WHATSAPP_NUMBER` | Manual guide coordination |

---

## 11. Common Pitfalls

- **Bilingual content appears empty/wrong:** ensure seeders/factories write `['id' => ..., 'en' => ...]` to translatable fields, never a bare string.
- **Vite assets not loading:** `npm run dev` must run alongside `php artisan serve`.
- **Queue jobs not processing:** check `QUEUE_CONNECTION=redis` and that a worker is running.
- **AR models not loading:** run `php artisan storage:link`; serve USDZ through `/usdz-file/{path}`, not direct storage URLs.
- **OpenRouteService failing:** run `./start-ors.sh` and wait for the graph build; verify `ORS_BASE_URL`.
- **Real-time not working:** ensure `penglipuran-reverb` is running and Reverb credentials match `resources/js/echo.js`.
- **Stale cache:** the observer should auto-flush, but you can manually run `Cache::tags(['tag'])->flush()` in tinker.

---

## 12. Quick Reference: Files to Know

| File | Why it matters |
|------|----------------|
| `composer.json` | PHP deps and composer scripts (`setup`, `dev`, `test`, `share`) |
| `package.json` | Node deps and scripts (`build`, `dev`, `i18n:*`) |
| `vite.config.js` | Vite + Tailwind plugin config |
| `phpunit.xml` | PHPUnit testing environment |
| `phpunit.dusk.xml` | Dusk browser-test config |
| `bootstrap/app.php` | Middleware aliases, CSRF exceptions, routing registration |
| `app/Providers/AppServiceProvider.php` | Cache observer registration, Gates, View composers |
| `routes/web.php` | All web routes |
| `routes/api.php` | Stateless API routes (webhook, tracking, AR lookup) |
| `routes/console.php` | Scheduled Artisan commands |
| `app/helpers.php` | Global helper functions |
| `scripts/i18n-sync.mjs` | UI string sync / auto-translate |
| `docker-compose.yml` / `deploy.sh` | Production deployment |
| `CLAUDE.md` | Deep agent guidance on domain model and patterns |

===

<laravel-boost-guidelines>
=== .ai/flock rules ===

## Flock

- When Flock is running on this machine (`flock status`), it serves this app on a `.test` domain (`https://<folder>.test` by default). Use the `site_info` tool of Flock's MCP server, or `flock sites`, for the exact URL. Don't run `php artisan serve`.
- Run `php`, `composer`, `node` and `npm` as usual: Flock's shims pick the versions this project uses.
- When code uses a PHP extension (imagick, intl, ...), require it in composer.json: `composer require ext-<name>:*`, so every machine and server installing the project needs it too.
- If other guidelines describe a different local development server, follow them only when Flock isn't the one serving this app.

=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== laravel-octane/core rules ===

# Laravel Octane

This application uses Laravel Octane, a long-running PHP server. The application bootstraps once and handles many requests within the same process.

- Never store request-specific state in singletons or static properties, because it can leak across requests.
- Use `config('octane.server')` to detect the active driver (`swoole`, `roadrunner`, or `frankenphp`).
- Prefer scoped bindings (`$this->app->scoped()`) over singletons for per-request services.

When working on Octane-specific features (concurrency, shared tables, memory, driver configuration, testing), invoke `octane-development` for detailed rules.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
