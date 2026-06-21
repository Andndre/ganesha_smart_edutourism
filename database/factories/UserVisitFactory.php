<?php

namespace Database\Factories;

use App\Models\CulturalObject;
use App\Models\RouteSession;
use App\Models\User;
use App\Models\UserVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserVisit>
 */
class UserVisitFactory extends Factory
{
    protected $model = UserVisit::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'visitable_type' => CulturalObject::class,
            'visitable_id' => CulturalObject::factory(),
            'route_session_id' => RouteSession::factory(),
            'visited_at' => now(),
        ];
    }
}
