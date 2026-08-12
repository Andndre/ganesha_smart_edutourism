# Testing Best Practices

## Use `LazilyRefreshDatabase` Over `RefreshDatabase`

`RefreshDatabase` migrates once per process and wraps each test in a rolled-back transaction. `LazilyRefreshDatabase` skips even that first migration if the schema is already up to date.

## Use Model Assertions Over Raw Database Assertions

Incorrect: `$this->assertDatabaseHas('users', ['id' => $user->id]);`

Correct: `$this->assertModelExists($user);`

More expressive, type-safe, and fails with clearer messages.

## Use Factory States and Sequences

Named states make tests self-documenting. Sequences eliminate repetitive setup.

Incorrect: `User::factory()->create(['email_verified_at' => null]);`

Correct: `User::factory()->unverified()->create();`

## Use `Exceptions::fake()` to Assert Exception Reporting

Instead of `withoutExceptionHandling()`, use `Exceptions::fake()` to assert the correct exception was reported while the request completes normally.

## Call `Event::fake()` After Factory Setup

Model factories rely on model events (e.g., `creating` to generate UUIDs). Calling `Event::fake()` before factory calls silences those events, producing broken models.

Incorrect: `Event::fake(); $user = User::factory()->create();`

Correct: `$user = User::factory()->create(); Event::fake();`

## Use `recycle()` to Share Relationship Instances Across Factories

Without `recycle()`, nested factories create separate instances of the same conceptual entity.

```php
Ticket::factory()
    ->recycle(Airline::factory()->create())
    ->create();
```

## Laravel Dusk (Browser Testing)

### 1. Never Use `RefreshDatabase` or `LazilyRefreshDatabase` in Dusk Tests
Dusk tests run in a real browser making HTTP requests to your application. Since database transactions do not span across separate HTTP requests, transactions will not be rolled back.
- **Incorrect:**
  ```php
  use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

  class LoginTest extends DuskTestCase
  {
      use LazilyRefreshDatabase;
  }
  ```
- **Correct:** Use `DatabaseMigrations` or `DatabaseTruncation` instead.
  ```php
  use Illuminate\Foundation\Testing\DatabaseMigrations;

  class LoginTest extends DuskTestCase
  {
      use DatabaseMigrations;
  }
  ```

### 2. Configure a Dedicated Test Database and URL
To prevent Dusk from overwriting your local development database, configure a separate testing environment:
- Create a `.env.dusk.local` file (Dusk will load this instead of `.env` when running).
- Set `DB_DATABASE` to your test database (e.g., `ganesha_smart_edutourism_test`).
- Set `APP_URL` to a dedicated local test server port (e.g., `http://127.0.0.1:8001`).

### 3. Use Dusk Selectors (`dusk="..."`) for UI Elements
Targeting CSS classes or IDs makes tests fragile since UI styling changes frequently. Use the custom `dusk` attribute to make tests resilient.
- **Blade Template:**
  ```html
  <button dusk="login-submit-button" class="btn-primary py-2 px-4">Masuk</button>
  ```
- **Dusk Test:**
  ```php
  $browser->click('@login-submit-button');
  ```

### 4. Always Wait for Elements When Testing Dynamic JS / Livewire / Alpine
Since browser testing interacts with real JavaScript, page elements and data updates happen asynchronously.
- **Incorrect:**
  ```php
  $browser->press('Simpan')
      ->assertSee('Data Berhasil Disimpan'); // May fail if the AJAX request hasn't completed
  ```
- **Correct:** Use `waitForText`, `waitFor`, `waitUntilMissing`, etc.
  ```php
  $browser->press('Simpan')
      ->waitForText('Data Berhasil Disimpan')
      ->assertSee('Data Berhasil Disimpan');
  ```
