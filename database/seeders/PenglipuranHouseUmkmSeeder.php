<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\UmkmProductCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Optional presentation data. Run explicitly with:
 * php artisan db:seed --class=PenglipuranHouseUmkmSeeder --force
 *
 * Uses existing product categories; at least four must exist before seeding.
 * Pins are provisional. Move them in Map Manager after checking the actual houses.
 */
class PenglipuranHouseUmkmSeeder extends Seeder
{
    private const HOUSE_COUNT = 72;

    public function run(): void
    {
        $categories = UmkmProductCategory::orderBy('id')->get();
        if ($categories->count() < 4) {
            throw new \RuntimeException('Seeder membutuhkan minimal 4 kategori produk yang sudah ada di database. Tidak ada data UMKM yang ditambahkan.');
        }

        for ($number = 1; $number <= self::HOUSE_COUNT; $number++) {
            DB::transaction(function () use ($number, $categories): void {
                $slug = sprintf('demo-rumah-no-%02d', $number);
                $email = sprintf('rumah-no-%02d@demo.invalid', $number);

                // An existing house may already have a real owner or manually edited data.
                if (UmkmProfile::where('slug', $slug)->exists()) {
                    return;
                }

                $owner = User::where('email', $email)->first();
                if ($owner && ($owner->role !== UserRole::UmkmOwner || $owner->umkmProfile()->exists())) {
                    throw new \RuntimeException("Akun {$email} sudah digunakan oleh data lain.");
                }

                $owner ??= User::create([
                    'name' => $this->randomOwnerName(),
                    'email' => $email,
                    'password' => '12345678',
                    'role' => UserRole::UmkmOwner,
                    'email_verified_at' => now(),
                    'preferred_language' => 'id',
                ]);

                $profile = UmkmProfile::create([
                    'user_id' => $owner->id,
                    'owner_name' => $owner->name,
                    'business_name' => [
                        'id' => "Rumah No. {$number}",
                        'en' => "House No. {$number}",
                    ],
                    'slug' => $slug,
                    'is_active' => true,
                ]);

                $row = intdiv($number - 1, 6);
                $column = ($number - 1) % 6;
                $profile->syncMapLocation([
                    'category' => 'umkm',
                    'latitude' => round((float) config('services.penglipuran.latitude') + ($row - 5.5) * 0.000055, 8),
                    'longitude' => round((float) config('services.penglipuran.longitude') + ($column - 2.5) * 0.00008, 8),
                    'is_accessible' => true,
                ]);

                $count = min($categories->count(), 4 + (($number - 1) % 3));
                for ($offset = 0; $offset < $count; $offset++) {
                    $category = $categories[(($number - 1) + $offset) % $categories->count()];

                    $profile->products()->create([
                        'umkm_product_category_id' => $category->id,
                        'name' => $category->getTranslations('name'),
                        'slug' => "{$slug}-{$category->slug}",
                        'stock' => null,
                        'is_active' => true,
                    ]);
                }
            });
        }

        $this->command?->info('72 UMKM rumah siap. Titik peta masih perkiraan dan perlu diposisikan manual.');
    }

    private function randomOwnerName(): string
    {
        $firstNames = ['Putu', 'Made', 'Nyoman', 'Ketut', 'Wayan', 'Komang', 'Kadek', 'Gede'];
        $lastNames = ['Arta', 'Suardana', 'Wirawan', 'Pradnyana', 'Suryani', 'Darmayasa', 'Sukma', 'Adnyana', 'Purnama'];

        return $firstNames[random_int(0, count($firstNames) - 1)].' '.$lastNames[random_int(0, count($lastNames) - 1)];
    }
}
