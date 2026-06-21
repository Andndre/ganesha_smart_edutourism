<?php

namespace Database\Factories;

use App\Models\CulturalObject;
use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserFavorite>
 */
class UserFavoriteFactory extends Factory
{
    protected $model = UserFavorite::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'favoritable_type' => CulturalObject::class,
            'favoritable_id' => CulturalObject::factory(),
        ];
    }
}
