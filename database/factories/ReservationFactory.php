<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'guest_name' => $this->faker->name(),
            'guest_email' => $this->faker->unique()->safeEmail(),
            'guest_phone' => $this->faker->phoneNumber(),
            'reservation_type' => $this->faker->word(),
            'scheduled_date' => Carbon::now(),
            'party_size' => $this->faker->randomNumber(),
            'total_amount' => $this->faker->word(),
            'status' => $this->faker->word(),
            'payment_status' => $this->faker->word(),
            'payment_method' => $this->faker->word(),
            'payment_reference' => $this->faker->word(),
            'qr_code' => $this->faker->word(),
            'checked_in_at' => $this->faker->word(),
            'checked_in_by' => $this->faker->randomNumber(),
            'cancelled_at' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'cancelled_by' => $this->faker->randomNumber(),
            'cancellation_type' => $this->faker->word(),
            'cancellation_note' => $this->faker->word(),

            'user_id' => User::factory(),
            'tour_package_id' => TourPackage::factory(),
        ];
    }
}
