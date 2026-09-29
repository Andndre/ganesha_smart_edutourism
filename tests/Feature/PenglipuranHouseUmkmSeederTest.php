<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Database\Seeders\PenglipuranHouseUmkmSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PenglipuranHouseUmkmSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_72_bilingual_houses_with_four_to_six_available_categories_without_changing_existing_data(): void
    {
        $existingOwner = User::factory()->create(['role' => UserRole::UmkmOwner]);
        $existingProfile = UmkmProfile::create([
            'user_id' => $existingOwner->id,
            'owner_name' => $existingOwner->name,
            'business_name' => ['id' => 'Warung Asli', 'en' => 'Existing Shop'],
            'slug' => 'warung-asli',
            'is_active' => true,
        ]);
        $existingCategory = UmkmProductCategory::create([
            'name' => ['id' => 'Loloh Cemcem Lama', 'en' => 'Existing Loloh Cemcem'],
            'slug' => 'loloh-cemcem',
            'price' => 18000,
        ]);
        $this->createExistingCategories(8);

        $this->seed(PenglipuranHouseUmkmSeeder::class);

        $houses = UmkmProfile::where('slug', 'like', 'demo-rumah-no-%')->with(['user', 'mapLocation', 'products'])->get();
        $this->assertCount(72, $houses);
        $this->assertModelExists($existingProfile);
        $this->assertSame('Warung Asli', $existingProfile->fresh()->getTranslation('business_name', 'id'));
        $this->assertSame('Loloh Cemcem Lama', $existingCategory->fresh()->getTranslation('name', 'id'));
        $this->assertSame('18000.00', $existingCategory->fresh()->price);
        $this->assertSame(9, UmkmProductCategory::count());
        $this->assertSame(72, $houses->pluck('mapLocation.id')->unique()->count());
        $categoryIds = UmkmProductCategory::pluck('id');

        foreach ($houses as $house) {
            $this->assertSame(UserRole::UmkmOwner, $house->user->role);
            $this->assertTrue(Hash::check('12345678', $house->user->password));
            $this->assertNotNull($house->mapLocation);
            $this->assertSame('umkm', $house->mapLocation->category);
            $this->assertGreaterThanOrEqual(4, $house->products->count());
            $this->assertLessThanOrEqual(6, $house->products->count());
            $this->assertSame($house->products->count(), $house->products->pluck('umkm_product_category_id')->unique()->count());
            $this->assertTrue($house->products->pluck('umkm_product_category_id')->every(fn (int $id) => $categoryIds->contains($id)));
            $this->assertTrue($house->products->every(fn (UmkmProduct $product) => $product->stock === null && $product->is_active));
        }

        $first = $houses->first();
        $this->assertSame('Rumah No. 1', $first->getTranslation('business_name', 'id'));
        $this->assertSame('House No. 1', $first->getTranslation('business_name', 'en'));
        $this->assertSame('Rumah No. 1', $first->mapLocation->name);
    }

    public function test_rerunning_does_not_reset_password_products_or_manually_moved_pin(): void
    {
        $this->createExistingCategories(6);
        $this->seed(PenglipuranHouseUmkmSeeder::class);

        $house = UmkmProfile::where('slug', 'demo-rumah-no-01')->firstOrFail();
        $house->mapLocation()->update(['latitude' => -8.42123456, 'longitude' => 115.35912345]);
        $owner = $house->user;
        $owner->password = 'changed-password';
        $owner->save();
        $product = $house->products()->firstOrFail();
        $product->update(['stock' => 12]);

        $counts = [User::count(), UmkmProfile::count(), UmkmProduct::count()];
        $this->seed(PenglipuranHouseUmkmSeeder::class);

        $this->assertSame($counts, [User::count(), UmkmProfile::count(), UmkmProduct::count()]);
        $this->assertSame(-8.42123456, $house->mapLocation()->firstOrFail()->latitude);
        $this->assertSame(115.35912345, $house->mapLocation()->firstOrFail()->longitude);
        $this->assertTrue(Hash::check('changed-password', $house->user()->firstOrFail()->password));
        $this->assertSame(12, $product->fresh()->stock);
    }

    public function test_it_stops_before_creating_houses_when_fewer_than_four_categories_exist(): void
    {
        $this->createExistingCategories(3);

        try {
            $this->seed(PenglipuranHouseUmkmSeeder::class);
            $this->fail('Seeder seharusnya menolak database dengan kurang dari empat kategori.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('minimal 4 kategori', $exception->getMessage());
        }

        $this->assertSame(0, UmkmProfile::count());
        $this->assertSame(0, UmkmProduct::count());
        $this->assertSame(3, UmkmProductCategory::count());
    }

    private function createExistingCategories(int $count): void
    {
        for ($number = 1; $number <= $count; $number++) {
            UmkmProductCategory::create([
                'name' => ['id' => "Kategori {$number}", 'en' => "Category {$number}"],
                'slug' => "kategori-{$number}",
            ]);
        }
    }
}
