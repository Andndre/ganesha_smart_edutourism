<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (empty($email) || empty($password)) {
            $this->command->warn('Kredensial admin di .env belum diatur.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
                'phone' => '08123456789',
                'nationality' => 'Indonesia',
                'preferred_language' => 'id',
            ]
        );
    }
}
