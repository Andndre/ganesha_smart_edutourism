# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Ganesha Smart Edutourism** is a Laravel 13 mobile-first web application for Desa Wisata Penglipuran. The app adopts a "Super App" design philosophy (inspired by Grab/Gojek) with a modular, outdoor-optimized interface for tourists to explore cultural sites, browse UMKM products, book tour packages, and use AR features.

**Tech Stack:** Laravel 13, PHP 8.3, SQLite (dev), Vite, TailwindCSS v4, Alpine.js, Livewire 4, Laravel Reverb (WebSockets)

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
```

## Architecture & Domain Model

### Multi-Role System
The application has **5 distinct user roles** with separate route prefixes and middleware:

1. **Guest** - Public access to explore, UMKM catalog, cultural objects, AR scan
2. **User** (authenticated) - Bookings, profile, feedback, e-tickets
3. **Admin** (`/admin/*`) - Full system management
4. **UMKM Owner** (`/owner/*`) - Product catalog and profile management
5. **Ticket Officer** (`/staff/*`) - QR scanning, walk-in ticketing, check-ins

**Middleware:** `redirect.admin`, `staff`, `umkm_owner` control access.

### Core Domain Models
**Cultural & Tourism:**
- `CulturalObject` - Heritage sites with AR markers, quizzes, stories (rich text via TipTap)
- `CulturalStory` - Narrative content for digital storytelling
- `TourRoute` / `TourRoutePoint` - Predefined walking routes with waypoints
- `TourPackage` - Bookable tour packages with pricing
- `Event` - Scheduled events with registration
- `MapLocation` - Generic map pins (includes facilities)
- `Facility` - Service locations (toilets, parking, info centers)

**UMKM (Local Business):**
- `UmkmProfile` - Business profiles with geolocation
- `UmkmProduct` - Products with categories
- `UmkmProductCategory` - Product categorization with 3D model support
- **Fair Distribution System:** `UmkmRecommendationService` ensures equitable UMKM exposure via `last_recommended_at` and `recommendation_count` fields

**AR & 3D:**
- `ArModel` - 3D models (GLB/GLTF/USDZ) with AR markers (PATT files)
- USDZ files served via special route for iOS AR Quick Look

**Bookings & Visitors:**
- `Reservation` - Tour package bookings with Midtrans payment integration
- `VisitorLog` - Track visitor entry/exit
- `RouteSession` - Track user progress through tour routes
- `CapacityZone` - Geofenced zones with crowd thresholds (triggers alerts)

**System:**
- `WeatherReport` - Cached weather data (updated every 10min during share)
- `Feedback` - User feedback with admin replies

### Service Layer
- `UmkmRecommendationService` - Fair rotation algorithm for UMKM recommendations based on geolocation and visit history

### Events & Notifications
- `CrowdAlertSent` - Broadcast when capacity zone threshold exceeded
- `EventReminderSent` - Sent 1 day before event
- `VisitorLocationUpdated` - Real-time visitor tracking (via Laravel Reverb)
- `ETicketMail` - Email e-tickets to users

## Key Technical Patterns

### AR Integration
- AR camera overlay uses **glassmorphism HUD** (backdrop-blur-md) for overlays
- AR markers are PATT files (from AR.js/A-Frame ecosystem)
- 3D models support GLB (Android) and USDZ (iOS AR Quick Look)
- Route: `/ar-scan` serves camera interface, `/api/ar/model` returns model data
- USDZ files served via `/usdz-file/{path}` with proper headers for iOS

### Routing & Maps
- **OpenRouteService** integration via `Api\RoutingController` for turn-by-turn directions
- Leaflet maps (not Google Maps) used throughout admin and public views
- Map uses `MapLocation` model with polymorphic relationships to `CulturalObject`, `Facility`, `UmkmProfile`
- Geofencing for capacity zones uses polygon coordinates in `geofence` JSON column

### Real-Time Features
- **Laravel Reverb** (WebSockets) for real-time updates
- `echo.js` configures Laravel Echo with Pusher client
- Channels defined in `routes/channels.php`
- Used for: crowd alerts, event notifications, visitor tracking

### Payment Integration
- **Midtrans** for payment processing
- Webhook: `/api/midtrans/webhook` handles payment status updates
- E-tickets emailed after successful payment via `ETicketMail`

### Caching
- **Redis and Tags Requirement**: We use **Redis** (`CACHE_STORE=redis`) for caching. Cache Tags are utilized to manage cache namespaces.
- **Avoid Cache Stampede**: Instead of plain `Cache::remember`, use `Cache::tags(['your-tag'])->flexible('key', [$freshSeconds, $staleSeconds], callback)` to implement the Stale-While-Revalidate pattern. This protects the database during traffic spikes (e.g. 2000 concurrent users).
- **Group Invalidation**: Use `Cache::tags(['your-tag'])->flush()` in observers/models for O(1) cache group clearing instead of forgetting individual keys.
- **Laravel 13 Security Feature**: Due to Laravel 13's `serializable_classes` feature being disabled by default (`false`), you **must not** cache full Eloquent Models or Collections directly. This causes `__PHP_Incomplete_Class` errors upon unserialization.
- **Correct Pattern**: Convert data to an array before caching using `->toArray()`. If you need to include accessors or relations, make sure to use `->append(['my_accessor'])` before `toArray()`.
- **View Consumption**: Ensure that the Views consuming these cached variables treat them as arrays (e.g. `$item['name']` instead of `$item->name`) and use standard array helpers like `!empty($item)` instead of `->isNotEmpty()`.

### Rich Text Editing
- **TipTap editor** (WYSIWYG) for cultural object descriptions
- Loaded via ES Modules from `esm.sh` (no NPM dependency)
- Image uploads via `/admin/cultural-objects/upload-image`
- Editor styling uses brand colors with subtle green highlights for active states

### File Storage Patterns
- AR models (GLB/GLTF/USDZ) stored in `storage/app/public/ar_models/`
- AR markers (PATT) in `storage/app/public/ar_markers/`
- UMKM product images in `storage/app/public/umkm_products/`
- Cultural object images in `storage/app/public/cultural_objects/`
- Event images in `storage/app/public/events/`
- Audio guides served via `/audio-stream/{path}` for range request support (Chrome seeking)

## Design System Constraints

**CRITICAL:** The UI must remain **outdoor-readable** and **mobile-first**. Refer to `DESIGN.md` for full specifications.

### Color Palette (Brand Identity)
- **Penglipuran Green** (`#1E5128`) - Primary CTA buttons, active states, progress bars
- **Bali Gold** (`#D4AF37`) - Accent, premium labels, ratings
- **Clean Off-White** (`#FAF9F6`) - Body background
- **Solid White** (`#FFFFFF`) - Card/modal surfaces
- **Charcoal Dark** (`#191A19`) - Primary text
- **Alert Amber** (`#E65100`) - Crowd warnings, emergency routes

### Typography Rules
- **UI Text:** Plus Jakarta Sans or Inter (high x-height, legible at small sizes)
- **Cultural Content ONLY:** Playfair Display (headlines, storytelling sections)
- **DO NOT** use Playfair for buttons, labels, or functional UI elements

### UI Patterns
- **Bottom Navigation:** Primary navigation (5 tabs, center AR button elevated)
- **Bottom Sheet:** Use for details (like Google Maps) instead of full-page modals
- **Bento Grid:** Homepage features a 4x2 or 3x2 grid of utility icons
- **Tap Targets:** Minimum 44px × 44px for outdoor use
- **Skeleton Loading:** Use pulse animations, NOT spinners
- **Haptic Feedback:** Core actions must trigger Vibration API (JS: `navigator.vibrate(50)`)

### Accessibility
- High contrast text (WCAG AA minimum)
- No edge-to-edge background photos (text must be readable in sunlight)
- Generous whitespace ("anti-fatigue" layout)

## Testing Strategy

### Running Tests
```bash
composer test                           # Run all tests
php artisan test --filter=ARScannerTest # Run specific test
php artisan test --testsuite=Feature   # Feature tests only
php artisan test --testsuite=Unit      # Unit tests only
```

### Test Organization
- `tests/Feature/` - Integration tests for controllers, routes, auth flows
- `tests/Unit/` - Unit tests for services, models, helpers

### Key Test Files
- `ARScannerTest` - AR model detection and marker validation
- `AdminTest` - Admin dashboard and CRUD operations
- `RoutingTest` - OpenRouteService integration
- `TourPackageBookingTest` - End-to-end booking flow with Midtrans
- `UmkmCatalogPublicTest` - UMKM recommendation algorithm
- `WeatherIntegrationTest` - Weather API integration

### Testing Conventions
- Use database transactions for cleanup (RefreshDatabase trait)
- Mock external APIs (Midtrans, OpenRouteService, Weather API)
- Seed test data via factories in `database/factories/`

## Important Conventions

### Code Style
- Follow Laravel conventions (PSR-12)
- Use `vendor/bin/pint --dirty --format agent` before committing
- Keep controllers thin, move business logic to services
- Use Eloquent relationships over manual joins

### Blade Components
- Reusable components in `resources/views/components/`
- Modal component: `<x-modal>` for admin forms
- Navigation components in `resources/views/components/navigation/`
- Notification system: `notification-toast.blade.php`, `notification-panel.blade.php`

### Route Organization
- Public routes: No prefix, uses `redirect.admin` middleware to prevent admin access
- Authenticated routes: Wrapped in `auth` middleware
- Admin routes: `/admin/*` prefix with `auth` + `admin` middleware
- Owner routes: `/owner/*` prefix with `auth` + `umkm_owner` middleware
- Staff routes: `/staff/*` prefix with `auth` + `staff` middleware
- API routes: `/api/*` (no Sanctum auth, uses webhook signatures where needed)

### Migration Patterns
- Use descriptive migration names with domain prefixes (e.g., `create_umkm_tables`)
- Store coordinates as `decimal(10, 7)` for latitude/longitude
- Use JSON columns for flexible data (e.g., `geofence` in capacity zones)
- Add timestamps to all tables

### Localization
- Translations in `lang/en.json` (flat key-value pairs)
- Language switcher route: `/lang/{locale}` (supports `en`, `id`)
- Stored in user preferences: `preferred_language` column

## Development Workflow

### Adding a New Feature
1. Create migration if database changes needed
2. Generate model with relationships
3. Create controller (use resource controllers for CRUD)
4. Define routes in `routes/web.php` (mind the middleware)
5. Create Blade views (use existing components)
6. Write tests in `tests/Feature/`
7. Run `composer test` to verify
8. Format code with `vendor/bin/pint --dirty --format agent`

### Working with AR Models
- GLB/GLTF for Android (Three.js/AR.js)
- USDZ for iOS (AR Quick Look)
- Store PATT marker files alongside 3D models
- Admin can upload via `/admin/ar-manager`
- Public API: `/api/ar/model` returns model URL + marker data

### Working with UMKM Recommendations
- Use `UmkmRecommendationService` for fair distribution
- Service tracks `last_recommended_at` and `recommendation_count`
- Multi-route recommendations via `/umkm/multi-route` (visits multiple UMKMs)

### Real-Time Features
- Define channels in `routes/channels.php`
- Broadcast events via `event()` helper
- Listen on frontend via Laravel Echo (configured in `resources/js/echo.js`)
- Test with `php artisan reverb:start` during development

## External Services

### Cloudflare Tunnel
- Tunnel name: `ganesha-tunnel`
- Used via `composer share` or `npm run host`
- Required for mobile device testing

### OpenRouteService
- Self-hosted instance in `openrouteservice/` directory
- Start with `./start-ors.sh`, stop with `./stop-ors.sh`
- API base URL configured in `.env` as `OPENROUTE_SERVICE_URL`

### Midtrans
- Sandbox/production credentials in `.env`
- Webhook endpoint: `/api/midtrans/webhook`
- Transaction IDs must be unique (use `reservation_id` as reference)

### Weather API
- API key in `.env` as `WEATHER_API_KEY`
- Updated every 10 minutes via `composer share` script
- Cached in `weather_reports` table

## Deployment

See `DEPLOY.md` for comprehensive deployment instructions. Key points:

- Production uses PostgreSQL (not SQLite)
- Run `composer setup` on fresh instances
- Configure `.env` with production credentials
- Build assets with `npm run build`
- Set up cron for `schedule:run`
- Configure supervisor for `queue:work` and `reverb:start`

## Common Issues

### Vite Not Loading Assets
- Ensure `npm run dev` is running alongside `php artisan serve`
- Check `vite.config.js` for correct port configuration

### Queue Jobs Not Processing
- Start queue worker: `php artisan queue:listen --tries=1`
- Check `.env` for correct `QUEUE_CONNECTION` (default: `database`)

### AR Models Not Loading
- Verify file paths in `ar_models` table
- Check storage symlink: `php artisan storage:link`
- USDZ files must be served via `/usdz-file/{path}` route (not direct storage URLs)

### OpenRouteService Connection Failed
- Start ORS: `./start-ors.sh` (Docker required)
- Wait ~2 minutes for initial graph build
- Verify `OPENROUTE_SERVICE_URL` in `.env`

### Real-Time Events Not Working
- Start Reverb: `php artisan reverb:start`
- Check Pusher credentials in `.env`
- Verify Echo configuration in `resources/js/echo.js`
