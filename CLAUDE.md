# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Ganesha Smart Edutourism** is a Laravel 13 mobile-first web application for Desa Wisata Penglipuran. The app adopts a "Super App" design philosophy (inspired by Grab/Gojek) with a modular, outdoor-optimized interface for tourists to explore cultural sites, browse UMKM products, book tour packages, and use AR features.

**Tech Stack:** Laravel 13, PHP 8.3, MySQL (dev & prod), SQLite (testing only), Vite, TailwindCSS v4, Alpine.js, Livewire 4 (navigation only, no components), Laravel Reverb (WebSockets)

## Essential Commands

### Setup & Development
```bash
composer setup              # Full setup: install deps, .env, key, migrate, build
composer dev                # Start all services (server, queue, logs, vite)
composer test               # Run PHPUnit test suite
composer share              # Share via Cloudflare tunnel + weather updates + Reverb
npm run dev                 # Vite dev server only
npm run build               # Production build
```

### Code Quality
```bash
vendor/bin/pint --dirty --format agent  # Format changed files (run before commits)
php artisan pail                        # Tail application logs
php artisan queue:listen --tries=1      # Run queue worker
```

### Key Artisan Commands
```bash
php artisan app:update-weather          # Fetch weather data (runs every 10min via share)
php artisan app:send-event-reminders    # Send event reminder emails
php artisan app:cleanup-tus             # Clean up stale TUS upload temp files (runs daily)
```

## Architecture & Domain Model

### Multi-Role System
The application has **5 distinct user roles** with separate route prefixes and middleware:

1. **Guest** - Public access to explore, UMKM catalog, cultural objects, AR scan
2. **User** (authenticated) - Bookings, profile, feedback, e-tickets, favorites, edutourism
3. **Admin** (`/admin/*`) - Full system management
4. **UMKM Owner** (`/owner/*`) - Product catalog and profile management
5. **Ticket Officer** (`/staff/*`) - QR scanning, walk-in ticketing, check-ins

**Middleware:** `redirect.admin`, `staff`, `umkm_owner` control access.

### Core Domain Models

**Cultural & Tourism:**
- `CulturalObject` - Heritage sites with AR markers, quizzes, stories (TipTap rich text). Has custom `attributesToArray()` override — see Multilingual section.
- `CulturalStory` - Narrative content (`story_type`: `history` | `philosophy` | `value`) per cultural object
- `TourRoute` / `TourRoutePoint` - Predefined walking routes with waypoints and `storytelling_content`
- `TourPackage` - Bookable tour packages. `inclusions`/`exclusions` use per-locale accessor/mutator supporting both old flat-list and new `{en, id}` format
- `Event` - Scheduled events with registration
- `MapLocation` - Generic map pins with polymorphic `locationable` (CulturalObject, Facility, UmkmProfile)
- `Facility` - Service locations (toilets, parking, info centers)

**UMKM (Local Business):**
- `UmkmProfile` - Business profiles with geolocation
- `UmkmProduct` - Products with categories
- `UmkmProductCategory` - Product categorization
- **Fair Distribution System:** `UmkmRecommendationService` ensures equitable UMKM exposure via `last_recommended_at` and `recommendation_count`

**AR & 3D:**
- `ArModel` - 3D models (GLB/GLTF/USDZ) with AR markers (PATT files), linked to `MapLocation`
- USDZ files served via special route for iOS AR Quick Look

**User Activity:**
- `Reservation` - Tour package bookings with Midtrans payment integration
- `VisitorLog` - Track visitor entry/exit
- `RouteSession` - Tracks user progress through edutourism tour routes
- `UserFavorite` - Polymorphic favorites (`favoritable` morph: CulturalObject, Event, TourPackage, UmkmProfile)
- `UserVisit` - Polymorphic visit history (`visitable` morph: CulturalObject)
- `CapacityZone` - Geofenced zones with crowd thresholds (triggers WebSocket alerts)

**System:**
- `WeatherReport` - Cached weather data (updated every 10min during share)
- `Feedback` - User feedback with admin replies

### Model Concerns (Traits)
All in `app/Models/Concerns/`:

- **`HasMapLocation`** — provides `syncMapLocation(array $attrs, bool $isUpdate)` which calls `updateOrCreate` on `mapLocation()`. Used by CulturalObject, Facility, UmkmProfile.
- **`HasSlug`** — slug generation from translatable fields. Methods: `generateSlug()`, `generateUniqueSlug()` (random suffix), `generateCollisionFreeSlug()` (collision-checked increment).

### Service Layer
- `UmkmRecommendationService` - Fair rotation algorithm based on geolocation and visit history
- `MidtransService` - Wraps Midtrans API status checks; returns parsed `transaction_status` and `payment_type`
- `TusService` - Resolves TUS chunked upload temp files and moves them to final storage

### Events & Notifications
- `CrowdAlertSent` - Broadcast when capacity zone threshold exceeded
- `VisitorLocationUpdated` / `VisitorLocationRemoved` - Real-time visitor tracking
- `EventReminderSent` - Sent 1 day before event
- `ETicketMail` - Email e-tickets to users

## Multilingual Content System

This is a **dual-layer** i18n system:

### Layer 1: UI Strings (`__()` helper)
- `lang/en.json` and `lang/id.json` — flat key-value pairs for UI text
- `lang/en/` and `lang/id/` — PHP files for Laravel's built-in validation/auth messages
- Use `__('key')` in Blade and PHP

### Layer 2: Content Fields (Spatie HasTranslations)
Most content models use `spatie/laravel-translatable`. Translatable fields are stored as JSON in the DB: `{"en": "...", "id": "..."}`.

**Models with `HasTranslations`:** CulturalObject (`name`, `short_description`, `description`), CulturalStory (`title`, `content`), Event (`name`, `description`, `location_name`), Facility (`name`, `description`), TourRoute (`name`, `description`), TourRoutePoint (`storytelling_content`), TourPackage (`name`, `description`), UmkmProfile (`business_name`, `description`), UmkmProduct (`name`, `description`), UmkmProductCategory (`name`, `description`), ArModel (`name`, `description`), MapLocation (`accessibility_notes`).

**Admin form input pattern** — use locale-keyed inputs, one tab per locale:
```html
<input type="text" name="name[en]" value="...">
<input type="text" name="name[id]" value="...">
```
Validated as `'name' => ['required', 'array'], 'name.en' => ['required', 'string']`.

**`NormalizesMultilingualInput` trait** (`app/Http/Concerns/`) — used in controllers to convert a plain string input to `{en, id}` array when a field may arrive as either format:
```php
$this->normalizeLocaleField($request, 'accessibility_notes');
$this->normalizeLocaleFields($request, ['name', 'description']);
$this->normalizeLocaleArrayField($request, 'quiz_question'); // for arrays of translatable items
```

**`translateValue()` helper** (`app/helpers.php`) — safe extraction from a translatable value (array, JSON string, or plain string):
```php
translateValue($model->name)          // uses current locale with fallback
translateValue($model->name, 'en')    // explicit locale
```

**`CulturalObject::attributesToArray()` override** — Spatie's `getAttributeValue()` is not called by Laravel's default `attributesToArray()`, so translatable fields would serialize as raw JSON strings in `toArray()` output. `CulturalObject` overrides this to apply `getAttributeValue()` per translatable field. **If you add a new model that is serialized to JSON (e.g., for `@json()` in Blade), check whether it needs this same override.**

### Locale Switching
`SetUserLocale` middleware (applied globally) sets app locale in this priority order:
1. `?locale=en` query param → stores in session and `user.preferred_language`
2. `auth()->user()->preferred_language`
3. `session('locale')`
4. `config('app.locale')` (default: `id`)

Named route `lang.switch`: `GET /lang/{locale}` (via `PageController::switchLang`).

**Cache keys must include locale** — always suffix locale-keyed caches: `"cache_key_{$locale}"`.

## Key Technical Patterns

### Caching
- **Redis + Tags** (`CACHE_STORE=redis`). All public-facing data uses cache tags.
- **Pattern:** `Cache::tags(['tag'])->flexible('key', [$freshSec, $staleSec], fn)` — Stale-While-Revalidate to prevent cache stampede.
- **Auto-invalidation:** `CacheInvalidationObserver` (`app/Observers/`) is registered in `AppServiceProvider` for all content models. It calls `Cache::tags([...])->flush()` on `saved` and `deleted` events. No manual cache clearing needed in controllers.
- **Serialization rule:** Never cache Eloquent Models/Collections directly (Laravel 13 `serializable_classes=false`). Always call `->toArray()` first. Cache key convention includes `_array_` to signal this: `"explore_map_locations_array_{$locale}"`.
- **View consumption:** Cached arrays use `$item['key']` not `$item->key`.

### File Uploads
- **Large files** (AR models GLB/USDZ): Chunked upload via **TUS protocol** (`/api/tus/upload`). Handled by `TusController` → `TusService`. Temp files in `storage/app/tus/`. Use `TusService::moveToFinal()` after upload completes.
- **Small files** (images): Standard Laravel multipart via `$request->file()`.
- AR models (GLB/GLTF/USDZ): `storage/app/public/ar_models/`
- AR markers (PATT): `storage/app/public/ar_markers/`
- Cultural object images: `storage/app/public/cultural_objects/`
- Audio guides: served via `/audio-stream/{path}` for range request support (Chrome seeking)

### AR Integration
- AR camera overlay uses **glassmorphism HUD** (backdrop-blur-md)
- AR markers are PATT files (AR.js/A-Frame ecosystem)
- 3D models: GLB (Android) and USDZ (iOS AR Quick Look)
- Route: `/ar-scan` → camera interface; `/ar/scan/{arMarkerId}` → redirect; `/ar/viewer/{arMarkerId}` → viewer
- USDZ served via `/usdz-file/{path}` (not direct storage URL) for correct iOS headers

### Authentication
- Standard email/password via `AuthController`
- **Google OAuth** via Laravel Socialite — `/auth/google` → `/auth/google/callback`. Auto-links to existing account by email; sets `google_id` and optionally imports avatar.

### Smart Edutourism (Guided Tour Routes)
Routes at `/edutourism/*`. Users follow `TourRoute` → `TourRoutePoint` sequences tracked in `RouteSession`. Points have `storytelling_content` (translatable) and optional quizzes via `CulturalObjectQuiz`. Progress is gate-fenced: must arrive at current point before unlocking the next.

### Real-Time Features
- **Laravel Reverb** (WebSockets) — crowd alerts, visitor tracking heatmap
- `TrackingController` receives GPS pings, stores in Redis `active_visitors` cache, broadcasts `VisitorLocationUpdated`
- Channels in `routes/channels.php`; Echo configured in `resources/js/echo.js`

### Payment Integration
- **Midtrans** for payment processing via `MidtransService`
- Webhook: `/api/midtrans/webhook` handles payment status updates
- E-tickets emailed after successful payment via `ETicketMail`

### Routing & Maps
- **OpenRouteService** (self-hosted) via `Api\RoutingController` for turn-by-turn directions
- Leaflet maps throughout (not Google Maps)
- Geofencing for `CapacityZone` uses polygon coordinates in `geofence` JSON column

### Rich Text Editing
- **TipTap** (WYSIWYG) for cultural object descriptions — loaded via ES Modules from `esm.sh` (no NPM dependency)
- Image uploads via `/admin/cultural-objects/upload-image`

## Design System Constraints

**CRITICAL:** UI must remain **outdoor-readable** and **mobile-first**. See `DESIGN.md` for full specs.

### Color Palette
- **Penglipuran Green** (`#1E5128`) - Primary CTA, active states, progress bars
- **Bali Gold** (`#D4AF37`) - Accent, premium labels, ratings
- **Clean Off-White** (`#FAF9F6`) - Body background
- **Solid White** (`#FFFFFF`) - Card/modal surfaces
- **Charcoal Dark** (`#191A19`) - Primary text
- **Alert Amber** (`#E65100`) - Crowd warnings, emergency routes

### Typography
- **UI Text:** Plus Jakarta Sans or Inter
- **Cultural Content ONLY:** Playfair Display (headlines, storytelling sections) — never use for buttons/labels

### UI Patterns
- **Bottom Navigation:** 5 tabs, center AR button elevated
- **Bottom Sheet:** For details (like Google Maps) instead of full-page modals
- **Bento Grid:** Homepage 4×2 or 3×2 utility icons
- **Tap Targets:** Minimum 44×44px
- **Skeleton Loading:** Pulse animations, NOT spinners
- **Haptic Feedback:** `navigator.vibrate(50)` on core actions

## Route Organization
- Public: No prefix, `redirect.admin` middleware
- Authenticated: `auth` middleware
- Admin: `/admin/*` — `auth` + `admin`
- Owner: `/owner/*` — `auth` + `umkm_owner`
- Staff: `/staff/*` — `auth` + `staff`
- API (no Sanctum): `/api/*`

## Testing

```bash
composer test                           # All tests
php artisan test --filter=ARScannerTest # Single test
php artisan test --testsuite=Feature    # Feature only
php artisan test --testsuite=Unit       # Unit only
```

- `tests/Feature/` — controller/route/auth integration tests
- `tests/Unit/` — services, models, helpers
- Uses SQLite in-memory for test DB; mock external APIs (Midtrans, OpenRouteService, Weather API)

## External Services

| Service | Config | Notes |
|---|---|---|
| Cloudflare Tunnel | `ganesha-tunnel` | `composer share` — required for mobile testing |
| OpenRouteService | `OPENROUTE_SERVICE_URL` | Self-hosted in `openrouteservice/`, `./start-ors.sh` (Docker) |
| Midtrans | `.env` sandbox/prod keys | Webhook: `/api/midtrans/webhook` |
| Weather API | `WEATHER_API_KEY` | Updated every 10min via share script |
| Google OAuth | `GOOGLE_CLIENT_ID/SECRET` | Socialite driver |

## Deployment

See `DEPLOY.md`. Key: production uses MySQL, supervisor for queue + Reverb, cron for `schedule:run`.

## Common Issues

**Vite not loading:** ensure `npm run dev` runs alongside `php artisan serve`.

**Queue not processing:** `php artisan queue:listen --tries=1`; check `QUEUE_CONNECTION`.

**AR models not loading:** check storage symlink (`php artisan storage:link`); USDZ must use `/usdz-file/{path}`.

**OpenRouteService failed:** `./start-ors.sh` (wait ~2min for graph build), check `OPENROUTE_SERVICE_URL`.

**Real-time not working:** `php artisan reverb:start`; verify Pusher credentials and `resources/js/echo.js`.

**Cache returning stale data:** `Cache::tags(['tag'])->flush()` in tinker; or save/delete any model watched by `CacheInvalidationObserver`.
