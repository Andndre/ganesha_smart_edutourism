<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminViewerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('admin.viewer_email');
        $password = config('admin.viewer_password');

        if (empty($email) || empty($password)) {
            $this->command->warn('Kredensial admin_viewer di .env belum diatur (ADMIN_VIEWER_EMAIL / ADMIN_VIEWER_PASSWORD), skip.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin Viewer',
                'password' => Hash::make($password),
                'role' => 'admin_viewer',
                'email_verified_at' => now(),
                'nationality' => 'Indonesia',
                'preferred_language' => 'id',
            ]
        );
    }
}
