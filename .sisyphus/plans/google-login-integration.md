# Google Login Integration with Laravel Socialite

## TL;DR

> **Quick Summary**: Implement Google OAuth login with auto-linking for existing email accounts. Users can login via password OR Google interchangeably.
> 
> **Deliverables**:
> - Migration: `google_id` column + nullable password
> - OAuth routes: `/auth/google` and `/auth/google/callback`
> - Controller methods: redirect & callback logic with auto-linking
> - View updates: Active Google buttons in login/register
> - Config: `.env` variables for Google credentials
> 
> **Estimated Effort**: Quick (1-2 hours)
> **Parallel Execution**: NO - sequential (migration → code → views)
> **Critical Path**: Migration → Controller → Routes → Views

---

## Context

### Original Request
User ingin mengaktifkan & mengimplementasikan tombol login dengan Google di `resources/views/auth/`. Jika sudah ada email yang sama (sudah register tanpa Google), **auto-link** akun tersebut sehingga user punya 2 opsi login: password atau Google.

### Interview Summary
**Key Decisions**:
- Google OAuth credentials: Belum ada, akan diatur di `.env` nanti
- Email linking behavior: **Auto-link** - jika email sudah exists, langsung koneksi `google_id` ke user existing
- Laravel Socialite: Already installed (v5.28.0)

**Research Findings**:
- Current auth system: `AuthController` handles login/register
- User table: Has `name`, `email`, `password`, `role`, etc.
- Views: `login.blade.php` dan `register.blade.php` already have Google button UI (currently non-functional)

---

## Work Objectives

### Core Objective
Implement Google OAuth login flow using Laravel Socialite with automatic account linking for existing emails.

### Concrete Deliverables
- Database column: `users.google_id` (nullable, unique)
- Database modification: `users.password` becomes nullable
- Environment variables: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`
- Routes: `/auth/google` (redirect), `/auth/google/callback` (callback)
- Controller methods: `redirectToGoogle()`, `handleGoogleCallback()`
- Updated views: Login & register buttons link to `/auth/google`

### Definition of Done
- [ ] Migration applied successfully: `php artisan migrate`
- [ ] Google button in login page redirects to Google OAuth screen
- [ ] Google button in register page redirects to Google OAuth screen
- [ ] After Google auth: new users are created, existing users are auto-linked
- [ ] User can login via password OR Google on subsequent visits
- [ ] Config values in `.env.example` are documented

### Must Have
- `google_id` column in users table (string, nullable, unique)
- `password` column becomes nullable (untuk pure Google users)
- OAuth redirect logic (`Socialite::driver('google')->redirect()`)
- OAuth callback logic with auto-linking by email
- Environment config for Google credentials (CLIENT_ID, SECRET, REDIRECT)
- Active href on Google buttons in login/register views

### Must NOT Have (Guardrails)
- ❌ No user prompt for linking - auto-link silently if email matches
- ❌ No separate "Link Google Account" page - login flow does everything
- ❌ No additional user input fields during OAuth flow
- ❌ No email verification step for Google users (Google already verified)
- ❌ No complex role assignment logic - use default 'tourist' role

---

## Verification Strategy

> **ZERO HUMAN INTERVENTION** - ALL verification is agent-executed.

### Test Decision
- **Infrastructure exists**: YES (PHPUnit configured)
- **Automated tests**: Tests-after (feature test after implementation)
- **Framework**: PHPUnit
- **Agent-Executed QA**: ALWAYS (mandatory via Playwright for browser flow)

### QA Policy
Every task includes agent-executed QA scenarios.
Evidence saved to `.sisyphus/evidence/task-{N}-{scenario-slug}.{ext}`.

- **OAuth Flow**: Use Playwright (playwright skill) - Navigate, click Google button, mock OAuth response
- **Database**: Use Bash (php artisan tinker) - Verify columns exist, check nullable constraints
- **API/Backend**: Use Bash (curl) - Hit redirect endpoint, verify 302 response

---

## Execution Strategy

### Sequential Execution (No Parallelism - Dependencies)

```
Task 1: Database Migration (foundation)
  ↓
Task 2: Service Provider Config (dependency: env vars)
  ↓
Task 3: Controller OAuth Methods (dependency: config)
  ↓
Task 4: Route Registration (dependency: controller)
  ↓
Task 5: View Updates (dependency: routes)
  ↓
Task 6: Environment Config (can be parallel with Task 1-2)
  ↓
Task 7: Feature Test (dependency: all above)
```

**Why Sequential**: Each task depends on previous output. No safe parallelization.

---

## TODOs

- [x] 1. Create and Run Migration for google_id Column

  **What to do**:
  - Edit existing migration file: `database/migrations/2026_06_21_012614_add_google_id_to_users_table.php`
  - Add `google_id` column: `$table->string('google_id')->nullable()->unique()->after('email');`
  - Make `password` nullable: `$table->string('password')->nullable()->change();`
  - Implement `down()` method: drop `google_id`, revert password to NOT NULL
  - Run migration: `php artisan migrate`

  **Must NOT do**:
  - Don't add `google_email` or `google_name` columns (use existing `email`/`name`)
  - Don't add `provider` column (only Google, not multi-provider)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`

  **Parallelization**:
  - **Can Run In Parallel**: NO
  - **Parallel Group**: Sequential (Task 1)
  - **Blocks**: Task 2, 3, 4, 5, 7
  - **Blocked By**: None

  **References**:
  - `database/migrations/2026_05_20_000001_update_users_table.php` - Pattern for altering users table
  - Laravel docs: `https://laravel.com/docs/11.x/migrations#column-modifiers` - Nullable and unique modifiers

  **Acceptance Criteria**:
  - [ ] Migration file up() method contains `google_id` column definition
  - [ ] Migration file up() method makes password nullable
  - [ ] Migration runs without error: `php artisan migrate` exits 0
  - [ ] Users table schema shows google_id column: `php artisan db:show users`

  **QA Scenarios**:

  ```
  Scenario: Migration adds google_id column successfully
    Tool: Bash
    Preconditions: Fresh migration state
    Steps:
      1. Run: php artisan migrate --force
      2. Run: php artisan db:show users | grep google_id
      3. Assert output contains "google_id" and "nullable"
    Expected Result: Column exists, is nullable and unique
    Evidence: .sisyphus/evidence/task-1-migration-success.txt

  Scenario: Password column becomes nullable
    Tool: Bash
    Preconditions: Migration applied
    Steps:
      1. Run: php artisan tinker --execute="Schema::getColumnType('users', 'password');"
      2. Check migration output for "password" change
    Expected Result: Password column accepts NULL values
    Evidence: .sisyphus/evidence/task-1-password-nullable.txt
  ```

  **Evidence to Capture**:
  - [ ] Migration output log
  - [ ] Database schema dump for users table

  **Commit**: NO (grouped with Task 3)

---

- [x] 2. Configure Laravel Socialite for Google Provider

  **What to do**:
  - Open `config/services.php`
  - Add Google config array under 'google' key:
    ```php
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],
    ```

  **Must NOT do**:
  - Don't hardcode credentials
  - Don't add unnecessary scopes (Socialite defaults are fine)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`

  **Parallelization**:
  - **Can Run In Parallel**: NO
  - **Parallel Group**: Sequential (Task 2)
  - **Blocks**: Task 3
  - **Blocked By**: Task 1

  **References**:
  - Laravel Socialite docs: `https://laravel.com/docs/11.x/socialite#configuration`
  - `config/services.php` - Existing service configs

  **Acceptance Criteria**:
  - [ ] config/services.php contains 'google' array
  - [ ] Config uses env() for all credentials
  - [ ] GOOGLE_REDIRECT_URI has fallback to APP_URL/auth/google/callback

  **QA Scenarios**:

  ```
  Scenario: Config file has valid Google array
    Tool: Bash
    Preconditions: config/services.php edited
    Steps:
      1. Run: php artisan config:cache
      2. Run: php artisan tinker --execute="echo config('services.google.client_id');"
      3. Assert output is not null (will be null until .env filled, but key exists)
    Expected Result: Config key accessible without error
    Evidence: .sisyphus/evidence/task-2-config-valid.txt
  ```

  **Commit**: NO (grouped with Task 3)

---

- [x] 3. Implement OAuth Controller Methods in AuthController

  **What to do**:
  - Open `app/Http/Controllers/AuthController.php`
  - Add two new methods:
    ```php
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // Auto-link: Update existing user with google_id
                $user->update(['google_id' => $googleUser->getId()]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                    'password' => null,
                ]);
            }
            
            Auth::login($user);
            return redirect()->intended('/');
            
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Gagal login dengan Google. Silakan coba lagi.']);
        }
    }
    ```
  - Add import: `use Laravel\Socialite\Facades\Socialite;` at top

  **Must NOT do**:
  - Don't ask user for permission to link (auto-link silently)
  - Don't create duplicate accounts for same email
  - Don't assign custom roles (use default 'tourist')

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `['laravel-best-practices']`

  **Parallelization**:
  - **Can Run In Parallel**: NO
  - **Parallel Group**: Sequential (Task 3)
  - **Blocks**: Task 4, 7
  - **Blocked By**: Task 1, 2

  **References**:
  - `app/Http/Controllers/AuthController.php:28-48` - Existing login() method pattern
  - `app/Http/Controllers/AuthController.php:61-79` - Existing register() method for User::create pattern
  - Laravel Socialite docs: `https://laravel.com/docs/11.x/socialite#retrieving-user-details`
  - **Auto-linking logic**: Check `User::where('email', ...)->first()` before creating

  **Acceptance Criteria**:
  - [ ] redirectToGoogle() method exists and returns Socialite redirect
  - [ ] handleGoogleCallback() method exists
  - [ ] Callback checks if email exists before creating user
  - [ ] If email exists, updates google_id on existing user
  - [ ] If email doesn't exist, creates new user with google_id
  - [ ] Exception handling returns user-friendly error message

  **QA Scenarios**:

  ```
  Scenario: OAuth redirect returns 302 to Google
    Tool: Bash (curl)
    Preconditions: Routes registered, controller method exists
    Steps:
      1. Run: curl -I http://localhost:8000/auth/google
      2. Assert response code is 302
      3. Assert Location header contains "accounts.google.com"
    Expected Result: Redirect to Google OAuth consent screen
    Evidence: .sisyphus/evidence/task-3-oauth-redirect.txt

  Scenario: Callback auto-links existing email
    Tool: Bash (php artisan tinker)
    Preconditions: Database has user with email test@example.com, password set
    Steps:
      1. Create test user: User::create(['email' => 'test@example.com', 'password' => Hash::make('pass'), 'name' => 'Test'])
      2. Mock callback: Simulate handleGoogleCallback with same email, different google_id
      3. Check user record: User::where('email', 'test@example.com')->first()->google_id
    Expected Result: User has both password AND google_id populated
    Evidence: .sisyphus/evidence/task-3-auto-link.txt
  ```

  **Commit**: YES
  - Message: `feat(auth): implement Google OAuth login with auto-linking`
  - Files: `database/migrations/2026_06_21_012614_add_google_id_to_users_table.php`, `config/services.php`, `app/Http/Controllers/AuthController.php`
  - Pre-commit: `vendor/bin/pint --dirty`

---

- [x] 4. Register OAuth Routes

  **What to do**:
  - Open `routes/web.php`
  - Add routes inside `Route::middleware('guest')->group(...)` block (after line 39, before closing `});`):
    ```php
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
    ```

  **Must NOT do**:
  - Don't put routes outside guest middleware (they must be guest-only)
  - Don't add separate routes for register vs login (one route serves both)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`

  **Parallelization**:
  - **Can Run In Parallel**: NO
  - **Parallel Group**: Sequential (Task 4)
  - **Blocks**: Task 5
  - **Blocked By**: Task 3

  **References**:
  - `routes/web.php:34-44` - Existing auth routes in guest middleware
  - Route naming convention: `auth.google` and `auth.google.callback`

  **Acceptance Criteria**:
  - [ ] Route list shows auth.google and auth.google.callback: `php artisan route:list | grep google`
  - [ ] Both routes use guest middleware
  - [ ] Routes point to correct controller methods

  **QA Scenarios**:

  ```
  Scenario: Routes are registered correctly
    Tool: Bash
    Preconditions: Routes added to web.php
    Steps:
      1. Run: php artisan route:list --name=google
      2. Assert output contains "auth.google" GET route
      3. Assert output contains "auth.google.callback" GET route
    Expected Result: Both routes visible in route list
    Evidence: .sisyphus/evidence/task-4-routes-registered.txt
  ```

  **Commit**: NO (grouped with Task 5)

---

- [x] 5. Update Login and Register Views with Active Google Buttons

  **What to do**:
  - Open `resources/views/auth/login.blade.php`
  - Find Google button (line 77): Change `<button type="button"` to `<a href="{{ route('auth.google') }}"`
  - Close tag properly: Change closing `</button>` to `</a>`
  - Open `resources/views/auth/register.blade.php`
  - Find Google button (line 123): Change `<button type="button"` to `<a href="{{ route('auth.google') }}"`
  - Close tag properly: Change closing `</button>` to `</a>`

  **Must NOT do**:
  - Don't change button styling (keep existing Tailwind classes)
  - Don't add separate routes for login vs register (both use same OAuth flow)
  - Don't modify SVG icons

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`

  **Parallelization**:
  - **Can Run In Parallel**: NO
  - **Parallel Group**: Sequential (Task 5)
  - **Blocks**: None (final implementation task)
  - **Blocked By**: Task 4

  **References**:
  - `resources/views/auth/login.blade.php:77-90` - Current non-functional button
  - `resources/views/auth/register.blade.php:123-136` - Current non-functional button
  - Keep exact classes: `tap-target flex w-full items-center justify-center gap-3 rounded-xl border border-gray-200 bg-white py-3.5 transition-all hover:bg-gray-50 active:scale-[0.98]`

  **Acceptance Criteria**:
  - [ ] Login page Google button is `<a>` tag with `href="{{ route('auth.google') }}"`
  - [ ] Register page Google button is `<a>` tag with `href="{{ route('auth.google') }}"`
  - [ ] Buttons retain all Tailwind classes (no styling regression)
  - [ ] SVG icons remain unchanged

  **QA Scenarios**:

  ```
  Scenario: Login page Google button redirects to OAuth
    Tool: Playwright (playwright skill)
    Preconditions: php artisan serve running
    Steps:
      1. Navigate to http://localhost:8000/login
      2. Find element with selector 'a[href*="auth/google"]'
      3. Click the Google button
      4. Assert URL changes to Google OAuth consent page (or local redirect)
    Expected Result: User redirected to /auth/google route
    Evidence: .sisyphus/evidence/task-5-login-button-works.png

  Scenario: Register page Google button redirects to OAuth
    Tool: Playwright (playwright skill)
    Preconditions: php artisan serve running
    Steps:
      1. Navigate to http://localhost:8000/register
      2. Find element with selector 'a[href*="auth/google"]'
      3. Click the Google button
      4. Assert URL changes to /auth/google route
    Expected Result: User redirected to OAuth flow
    Evidence: .sisyphus/evidence/task-5-register-button-works.png
  ```

  **Commit**: YES
  - Message: `feat(views): activate Google login buttons in auth pages`
  - Files: `routes/web.php`, `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`
  - Pre-commit: `vendor/bin/pint --dirty`

---

- [x] 6. Add Google OAuth Environment Variables to .env and .env.example

  **What to do**:
  - Open `.env.example`
  - Add after existing auth/mail config (around line 30-40):
    ```
    # Google OAuth
    GOOGLE_CLIENT_ID=
    GOOGLE_CLIENT_SECRET=
    GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
    ```
  - Open `.env`
  - Add same variables (leave client_id and secret empty for now):
    ```
    GOOGLE_CLIENT_ID=
    GOOGLE_CLIENT_SECRET=
    GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
    ```

  **Must NOT do**:
  - Don't fill in actual credentials (user will add later)
  - Don't add extra OAuth providers (only Google)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 1-2)
  - **Parallel Group**: Wave 1 (with Tasks 1, 2)
  - **Blocks**: None (documentation task)
  - **Blocked By**: None

  **References**:
  - `.env.example` - Existing variable format
  - Default redirect: `${APP_URL}/auth/google/callback`

  **Acceptance Criteria**:
  - [ ] .env.example contains GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI
  - [ ] .env contains same variables (values empty)
  - [ ] GOOGLE_REDIRECT_URI has fallback using APP_URL variable

  **QA Scenarios**:

  ```
  Scenario: Environment variables are documented
    Tool: Bash
    Preconditions: .env and .env.example edited
    Steps:
      1. Run: grep GOOGLE_CLIENT_ID .env.example
      2. Run: grep GOOGLE_CLIENT_SECRET .env.example
      3. Run: grep GOOGLE_REDIRECT_URI .env.example
      4. Assert all three variables present
    Expected Result: All Google OAuth variables documented
    Evidence: .sisyphus/evidence/task-6-env-vars.txt
  ```

  **Commit**: YES
  - Message: `chore(config): add Google OAuth environment variables`
  - Files: `.env.example`, `.env`
  - Pre-commit: None (config files)

---

- [x] 7. Write Feature Test for Google OAuth Flow

  **What to do**:
  - Create test file: `tests/Feature/GoogleOAuthTest.php`
  - Test scenarios:
    1. OAuth redirect returns 302 to Google
    2. Callback creates new user if email doesn't exist
    3. Callback auto-links google_id if email already exists
    4. User can login via Google after linking
  - Mock Socialite user with: `Socialite::shouldReceive('driver')->andReturn($mockDriver)`

  **Must NOT do**:
  - Don't make real API calls to Google (mock everything)
  - Don't test password login (that's existing functionality)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `['laravel-best-practices']`

  **Parallelization**:
  - **Can Run In Parallel**: NO
  - **Parallel Group**: Sequential (Task 7)
  - **Blocks**: None (verification task)
  - **Blocked By**: Task 3, 4, 5

  **References**:
  - `tests/Feature/AdminTest.php` - Existing feature test pattern
  - Laravel Socialite testing: Mock Socialite facade with shouldReceive
  - Laravel docs: `https://laravel.com/docs/11.x/mocking#mocking-facades`

  **Acceptance Criteria**:
  - [ ] Test file exists: `tests/Feature/GoogleOAuthTest.php`
  - [ ] All test methods pass: `php artisan test --filter=GoogleOAuthTest`
  - [ ] Tests cover: redirect, new user creation, auto-linking

  **QA Scenarios**:

  ```
  Scenario: Feature test suite passes
    Tool: Bash
    Preconditions: All implementation tasks complete
    Steps:
      1. Run: php artisan test --filter=GoogleOAuthTest
      2. Assert exit code 0
      3. Assert output shows "Tests: 3 passed"
    Expected Result: All Google OAuth tests pass
    Evidence: .sisyphus/evidence/task-7-tests-pass.txt
  ```

  **Commit**: YES
  - Message: `test(auth): add feature tests for Google OAuth flow`
  - Files: `tests/Feature/GoogleOAuthTest.php`
  - Pre-commit: `vendor/bin/pint --dirty`

---

## Final Verification Wave

> 4 review agents run in PARALLEL after ALL implementation. Wait for user's explicit approval.

- [x] F1. **Plan Compliance Audit** — `oracle`
  
  Read plan end-to-end. Verify: `google_id` column exists in DB, `password` is nullable, OAuth routes registered, controller methods exist, views updated, .env variables documented. Check evidence files in `.sisyphus/evidence/`.
  
  Output: `Must Have [6/6] | Must NOT Have [5/5] | Tasks [7/7] | VERDICT: APPROVE/REJECT`

- [x] F2. **Code Quality Review** — `unspecified-high`
  
  Run `vendor/bin/pint --dirty`, verify migration rollback works, check controller exception handling, test OAuth redirect (curl), verify route middleware (guest only).
  
  Output: `Pint [PASS/FAIL] | Migration [PASS/FAIL] | Routes [PASS/FAIL] | VERDICT`

- [x] F3. **Real Manual QA** — `unspecified-high` (+ `playwright` skill)
  
  Start Laravel server. Open login page, click Google button (will fail at Google since no credentials, but route must work). Check register page same way. Test auto-linking with Tinker: create user with email, simulate callback with same email, verify google_id populated.
  
  Output: `Scenarios [3/3 pass] | Integration [PASS/FAIL] | VERDICT`

- [x] F4. **Scope Fidelity Check** — `deep`
  
  Verify: no extra OAuth providers added, no user prompts for linking, password login still works, email validation not duplicated. Check each task didn't add unrequested features.
  
  Output: `Tasks [7/7 compliant] | Scope Creep [CLEAN/issues] | VERDICT`

---

## Commit Strategy

- **1**: `feat(auth): implement Google OAuth login with auto-linking` - Migration, config, controller
- **2**: `feat(views): activate Google login buttons in auth pages` - Routes, views
- **3**: `chore(config): add Google OAuth environment variables` - .env files
- **4**: `test(auth): add feature tests for Google OAuth flow` - Test file

---

## Success Criteria

### Verification Commands
```bash
php artisan migrate           # Expected: Migration successful
php artisan route:list --name=google   # Expected: Shows auth.google routes
php artisan test --filter=GoogleOAuthTest   # Expected: All tests pass
curl -I http://localhost:8000/auth/google   # Expected: 302 redirect
```

### Final Checklist
- [ ] `google_id` column exists in users table
- [ ] `password` column is nullable
- [ ] Google OAuth routes registered
- [ ] Controller methods handle redirect & callback
- [ ] Login page Google button works
- [ ] Register page Google button works
- [ ] `.env.example` documents Google variables
- [ ] Feature tests pass
