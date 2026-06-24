<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('staff.ticket_officer_email', 'ticketing@example.com');
        $password = config('staff.ticket_officer_password', 'password');

        if (empty($email) || empty($password)) {
            $this->command->warn('Kredensial petugas tiket di .env belum diatur.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Petugas Tiket',
                'password' => Hash::make($password),
                'role' => 'ticket_officer',
                'email_verified_at' => now(),
                'phone' => '08123456788',
                'nationality' => 'Indonesia',
                'preferred_language' => 'id',
            ]
        );
    }
}
