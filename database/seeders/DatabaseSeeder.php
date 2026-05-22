<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin Seeder runs in all environments
        $this->call([
            AdminSeeder::class,
        ]);

        if (app()->environment('local')) {
            if (! User::where('email', 'test@example.com')->exists()) {
                User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ]);
            }

            // Create sample data for Penglipuran Smart Edutourism
            $this->call([
                CulturalObjectSeeder::class,
                CulturalStorySeeder::class,
                UmkmProfileSeeder::class,
                ProductSeeder::class,
                TourPackageSeeder::class,
                LocalDevSeeder::class,
            ]);
        }
    }
}
