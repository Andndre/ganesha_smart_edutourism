<?php

namespace Tests\Feature\Admin;

use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminTableTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('accountSearches')]
    public function test_account_search_matches_each_field_without_including_other_roles(string $role, string $route, string $viewKey, string $field, string $value, string $search, string $otherValue): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $match = User::factory()->create(['role' => $role, $field => $value]);
        User::factory()->create(['role' => $role]);
        User::factory()->create(['role' => 'tourist', $field => $otherValue]);

        $this->actingAs($admin)->get(route($route, ['search' => $search]))
            ->assertOk()
            ->assertViewHas($viewKey, fn ($accounts) => $accounts->modelKeys() === [$match->id]);
    }

    #[DataProvider('accountLists')]
    public function test_account_pagination_keeps_the_search_and_returns_the_remaining_match(string $role, string $route, string $viewKey): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $accounts = User::factory()->count(16)->create(['role' => $role, 'name' => 'Sasih Pagination']);
        User::factory()->create(['role' => $role, 'name' => 'Unrelated Account']);

        $this->actingAs($admin)->get(route($route, ['search' => 'Sasih']))
            ->assertOk()
            ->assertViewHas($viewKey, fn ($results) => $results->total() === 16 && $results->count() === 15)
            ->assertSee('search=Sasih&amp;page=2', false);

        $this->get(route($route, ['search' => 'Sasih', 'page' => 2]))
            ->assertOk()
            ->assertViewHas($viewKey, fn ($results) => $results->modelKeys() === [$accounts->last()->id])
            ->assertSee('16–16');
    }

    #[DataProvider('shopSearches')]
    public function test_owner_search_includes_the_linked_shop_in_both_languages(string $search): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'umkm_owner']);
        UmkmProfile::factory()->create([
            'user_id' => $owner->id,
            'business_name' => ['id' => 'Tenun Sasih', 'en' => 'Moon Weaving'],
        ]);
        User::factory()->create(['role' => 'umkm_owner']);

        $this->actingAs($admin)->get(route('admin.umkm.owners', ['search' => $search]))
            ->assertOk()
            ->assertViewHas('owners', fn ($owners) => $owners->modelKeys() === [$owner->id]);
    }

    public function test_removed_global_product_table_is_not_available_with_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/umkm?search=Sasih&category=1')->assertNotFound();
    }

    public function test_owner_and_unmapped_shop_tables_have_independent_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owners = User::factory()->count(16)->create(['role' => 'umkm_owner', 'name' => 'Wayan']);
        $profiles = UmkmProfile::factory()->count(16)->create(['user_id' => null]);

        $this->actingAs($admin)->get(route('admin.umkm.owners', ['page' => 2, 'unmapped_page' => 2]))
            ->assertOk()
            ->assertViewHas('owners', fn ($results) => $results->modelKeys() === [$owners->last()->id])
            ->assertViewHas('unmappedProfiles', fn ($results) => $results->modelKeys() === [$profiles->last()->id]);

        $this->get(route('admin.umkm.owners', ['unmapped_page' => 2]))
            ->assertOk()
            ->assertViewHas('owners', fn ($results) => $results->count() === 15)
            ->assertViewHas('unmappedProfiles', fn ($results) => $results->modelKeys() === [$profiles->last()->id]);
    }

    #[DataProvider('accountLists')]
    public function test_unmatched_account_search_shows_a_filtered_empty_state(string $role, string $route, string $viewKey): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => $role, 'name' => 'Wayan']);

        $this->actingAs($admin)->get(route($route, ['search' => 'No matching account']))
            ->assertOk()
            ->assertViewHas($viewKey, fn ($accounts) => $accounts->total() === 0)
            ->assertSee('Tidak ada hasil yang cocok.')
            ->assertSee('Reset');
    }

    public static function accountLists(): array
    {
        return [
            'owners' => ['umkm_owner', 'admin.umkm.owners', 'owners'],
            'officers' => ['ticket_officer', 'admin.ticket-officers', 'officers'],
        ];
    }

    public static function accountSearches(): array
    {
        $cases = [];
        foreach (self::accountLists() as $list => $accountList) {
            foreach ([
                'name' => ['Wayan sasih-table', 'sasih-table', 'Tourist sasih-table'],
                'email' => ['sasih-table@example.test', 'sasih-table', 'sasih-table-tourist@example.test'],
                'phone' => ['087711123344', '771112', '087711123344'],
            ] as $field => $values) {
                $cases[$list.' '.$field] = [...$accountList, $field, ...$values];
            }
        }

        return $cases;
    }

    public static function shopSearches(): array
    {
        return ['Indonesian' => ['Tenun Sasih'], 'English' => ['Moon Weaving']];
    }
}
