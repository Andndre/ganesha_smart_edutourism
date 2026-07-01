<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'guest_name' => $this->faker->name(),
            'guest_email' => $this->faker->unique()->safeEmail(),
            'reservation_type' => 'package',
            'scheduled_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'party_size' => $this->faker->numberBetween(1, 10),
            'total_amount' => $this->faker->randomFloat(2, 50000, 1000000),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => $this->faker->randomElement(['midtrans', 'cash']),
            'payment_reference' => $this->faker->uuid(),
            'qr_code' => Str::uuid()->toString(),
            'checked_in_at' => null,
            'checked_in_by' => null,
            'cancelled_at' => null,
            'cancelled_by' => null,
            'cancellation_type' => null,
            'cancellation_note' => null,

            'user_id' => User::factory(),
            'tour_package_id' => TourPackage::factory(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => 'confirmed', 'payment_status' => 'paid']);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'checked_in_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_type' => 'user',
            'cancellation_note' => $this->faker->sentence(),
        ]);
    }
}
