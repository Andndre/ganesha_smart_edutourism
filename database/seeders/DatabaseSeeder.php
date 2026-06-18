<?php

namespace Database\Seeders;

use App\Models\CapacityZone;
use App\Models\CulturalObject;
use App\Models\Event;
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
        // Seeders that run in all environments
        $this->call([
            AdminSeeder::class,
            TourPackageSeeder::class,
        ]);

        if (app()->environment('local')) {
            if (! User::where('email', 'test@example.com')->exists()) {
                User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ]);
            }

            if (Event::where('name', 'Panglipuran Cultural Festival 2026')->doesntExist()) {
                $this->call([
                    EventSeeder::class,
                ]);
            }

            if (CapacityZone::where('zone_identifier', 'main_street')->doesntExist()) {
                $this->call([
                    CapacityZoneSeeder::class,
                ]);
            }

            if (CulturalObject::where('slug', 'pura-pande-test')->doesntExist()) {
                $this->call([
                    TestSimulationSeeder::class,
                ]);
            }
        }
    }
}
