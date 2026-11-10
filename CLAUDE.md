# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Ganesha Smart Edutourism** is a Laravel 13 mobile-first web application for Desa Wisata Penglipuran. The app adopts a "Super App" design philosophy (inspired by Grab/Gojek) with a modular, outdoor-optimized interface for tourists to explore cultural sites, browse UMKM products, book tour packages, and use AR features.

**Tech Stack:** Laravel 13, PHP 8.4, MySQL (dev & prod), SQLite (testing only), Vite, TailwindCSS v4, Alpine.js, Livewire 4 (navigation only, no components), Laravel Reverb (WebSockets)

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
2. **User** (authenticated) - Profile, feedback, favorites, edutourism
3. **Admin** (`/admin/*`) - Full system management
4. **UMKM Owner** (`/owner/*`) - Product catalog and profile management
5. **Ticket Officer** (`/staff/*`) - Scans OTA-purchased tickets (QR) to record visits and flag duplicate scans

**Middleware:** `redirect.admin`, `staff`, `umkm_owner` control access.

### Core Domain Models

**Cultural & Tourism:**

- `CulturalObject` - Heritage sites with AR markers, quizzes, stories (TipTap rich text). Has custom `attributesToArray()` override — see Multilingual section.
- `CulturalStory` - Narrative content (`story_type`: `history` | `philosophy` | `value`) per cultural object
- `TourRoute` / `TourRoutePoint` - Predefined walking routes with waypoints and `storytelling_content`
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

- `TicketScan` - catatan scan tiket OTA; `code_hash` unik untuk deteksi duplikat
- `VisitorLog` - Track visitor entry/exit
- `RouteSession` - Tracks user progress through edutourism tour routes
- `UserFavorite` - Polymorphic favorites (`favoritable` morph: CulturalObject, Event, UmkmProfile)
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
- `TusService` - Resolves TUS chunked upload temp files and moves them to final storage

### Events & Notifications

- `CrowdAlertSent` - Broadcast when capacity zone threshold exceeded
- `VisitorLocationUpdated` / `VisitorLocationRemoved` - Real-time visitor tracking
- `EventReminderSent` - Sent 1 day before event

## Multilingual Content System

This is a **dual-layer** i18n system:

### Layer 1: UI Strings (`__()` helper)

- `lang/en.json` and `lang/id.json` — flat key-value pairs for UI text
- `lang/en/` and `lang/id/` — PHP files for Laravel's built-in validation/auth messages
- Use `__('key')` in Blade and PHP

### Layer 2: Content Fields (Spatie HasTranslations)

Most content models use `spatie/laravel-translatable`. Translatable fields are stored as JSON in the DB: `{"en": "...", "id": "..."}`.

**Models with `HasTranslations`:** CulturalObject (`name`, `short_description`, `description`), CulturalStory (`title`, `content`), Event (`name`, `description`, `location_name`), Facility (`name`, `description`), TourRoute (`name`, `description`), TourRoutePoint (`storytelling_content`), UmkmProfile (`business_name`, `description`), UmkmProduct (`name`, `description`), UmkmProductCategory (`name`, `description`), ArModel (`name`, `description`), MapLocation (`accessibility_notes`).

**Admin form input pattern** — use locale-keyed inputs, one tab per locale:

```html
<input type="text" name="name[en]" value="..." />
<input type="text" name="name[id]" value="..." />
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

**`CulturalObject::attributesToArray()` override** — Spatie's `getAttributeValue()` is not called by Laravel's default `attributesToArray()`, so translatable fields would serialize as raw JSON strings in `toArray()` output. `CulturalObject` overrides this to call `getTranslations($key)` instead, returning `['en' => ..., 'id' => ...]` for each field. Consumers like `CulturalController::resolveTrans()` check `\is_array()` before resolving to a single locale. **If you add a new model that is serialized to JSON (e.g., for `@json()` in Blade), check whether it needs this same override.** (`ArModel` has the same override for this reason.)

**⚠️ Programmatic writes (seeders, factories, imports, tinker) MUST pass a locale-keyed array** — `['id' => '...', 'en' => '...']`, never a bare string. Assigning a plain string to a translatable field makes Spatie file it under the **current app locale** (which is `en` during `db:seed`/CLI), silently leaving the other locale empty and putting the value in the wrong slot. This is exactly what corrupted the cultural-object EN/ID tabs: Indonesian text ended up stored as `{"en":"<indonesian>"}` with no `id` key, so the admin form's English tab showed Indonesian. The admin **forms** are safe because they already submit `name[en]`/`name[id]` arrays — the bug only enters through code that writes bare strings. When in doubt, store the locale you actually have (e.g. `['id' => $indonesianText]`) rather than letting the locale default decide.

### Dashboard Translation Policy (Layer 1 Constraint)

**Do not use UI translation (`__()` helper) in dashboard views** (`resources/views/admin/*`, `resources/views/owner/*`, `resources/views/staff/*`). Dashboard is internal-only and must use hardcoded strings — in practice this codebase writes them in **Indonesian** (e.g. sidebar labels in `resources/views/layouts/dashboard.blade.php`: "Kapasitas Wisatawan", "Fasilitas Umum", "Manajemen Staff"), not English. Keep new dashboard strings consistent with this (hardcoded Indonesian), to avoid:

- Incomplete translations fragmenting the UI (e.g., some buttons in ID, labels in EN)
- Maintenance burden of syncing N dashboard strings across locales
- Stale translations post-refactor (orphaned keys clutter lang files)

**Translation script (`i18n-sync`) scans only `resources/`, `app/`, `routes/` — it skips dashboard folders.** If you need to add a new dashboard string, write it bare (no `__()`). For user-facing public content (`/` routes), use `__()` and let i18n-sync manage it.

To clean up orphaned keys from lang files post-refactor:

```bash
npm run i18n:cleanup-orphans    # removes keys not found in source code
```

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

- **Hijau Bambu / Primary (60%)** (`#293518`, `--color-primary`) - Primary CTA, active states, progress bars, admin sidebar
- **Hijau Olive / Secondary (30%)** (`#989A4B`, `--color-secondary`) - Secondary accents, ceremony badges, supporting elements
- **Bilah Bambu / Accent (10%)** (`#E3C994`, `--color-accent`) - Warm highlights, badges, decorative accents (always pair with dark `#191A19` or `#293518` text, never white)
- **Clean Off-White** (`#FAF9F6`, `--color-surface`) - Body background
- **Solid White** (`#FFFFFF`, `--color-card`) - Card/modal surfaces
- **Abu Batu / Charcoal Dark** (`#191A19`, `--color-charcoal`) - Primary text
- **Alert Amber** (`#E65100`, `--color-warning`) - Crowd warnings, emergency routes

### Typography

- **Headline & Sub-Headline (`font-display`):** Libertinus Sans (Bold 700 for Headline, Regular 400 for Sub-Headline; bundled via `@fontsource/libertinus-sans`)
- **Body Text, Caption & UI (`font-sans`):** Inter (Regular 400 for Body, Light 300 / Regular 400 for Caption)

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
- Uses SQLite in-memory for test DB; mock external APIs (OpenRouteService, Weather API)

## External Services

| Service           | Config                    | Notes                                                         |
| ----------------- | ------------------------- | ------------------------------------------------------------- |
| Cloudflare Tunnel | `ganesha-tunnel`          | `composer share` — required for mobile testing                |
| OpenRouteService  | `OPENROUTE_SERVICE_URL`   | Self-hosted in `openrouteservice/`, `./start-ors.sh` (Docker) |
| Weather API       | `WEATHER_API_KEY`         | Updated every 10min via share script                          |
| Google OAuth      | `GOOGLE_CLIENT_ID/SECRET` | Socialite driver                                              |

## Deployment

See `DEPLOY.md`. Key: production uses MySQL, supervisor for queue + Reverb, cron for `schedule:run`.

## Common Issues

**Vite not loading:** ensure `npm run dev` runs alongside `php artisan serve`.

**Queue not processing:** `php artisan queue:listen --tries=1`; check `QUEUE_CONNECTION`.

**AR models not loading:** check storage symlink (`php artisan storage:link`); USDZ must use `/usdz-file/{path}`.

**OpenRouteService failed:** `./start-ors.sh` (wait ~2min for graph build), check `OPENROUTE_SERVICE_URL`.

**Real-time not working:** `php artisan reverb:start`; verify Pusher credentials and `resources/js/echo.js`.

**Cache returning stale data:** `Cache::tags(['tag'])->flush()` in tinker; or save/delete any model watched by `CacheInvalidationObserver`.

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

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- When graphify-out/graph.json exists, run `graphify query "<question>"` before your first grep, search or source-file read for a codebase question, and put this rule in every subagent prompt that explores code. Read raw files only for lines the graph or an earlier read already located. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
- A committed graphify-out/graph.json is rewritten whole on every build, so a large diff to it in a pull request is expected. Do not flag it as churn or suggest skipping the graph rebuild or its hooks.
