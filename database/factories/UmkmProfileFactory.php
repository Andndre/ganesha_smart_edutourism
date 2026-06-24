<?php

namespace Database\Factories;

use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UmkmProfile>
 */
class UmkmProfileFactory extends Factory
{
    protected $model = UmkmProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $businessNameEn = fake()->unique()->company().' Art';
        $businessNameId = 'Warung '.$businessNameEn;

        return [
            'user_id' => User::factory()->state(['role' => 'umkm_owner']),
            'owner_name' => fake()->name(),
            'business_name' => ['en' => $businessNameEn, 'id' => $businessNameId],
            'slug' => Str::slug($businessNameEn),
            'description' => [
                'en' => fake()->paragraph(),
                'id' => fake()->paragraph(),
            ],
            'rating' => fake()->randomFloat(1, 4, 5),
            'is_active' => true,
            'recommendation_count' => fake()->numberBetween(0, 100),
        ];
    }
}
