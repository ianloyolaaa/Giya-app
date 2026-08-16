<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * The profile counters are derived now, so they are no longer seeded.
 * Maria's stats appear once VisitHistory and Itineraries rows exist.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@giya.app'],
            [
                'name'          => 'Admin Giya',
                'password_hash' => Hash::make('Admin@123'),
                'role'          => 'admin',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'maria.santos@email.com'],
            [
                'name'          => 'Maria Santos',
                'password_hash' => Hash::make('User@123'),
                'role'          => 'user',
                'created_at'    => now()->subMonths(7),
                'updated_at'    => now(),
            ]
        );
    }
}
