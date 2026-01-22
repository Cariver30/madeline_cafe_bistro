<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            ['email' => 'info@bbtdpr.com', 'name' => 'Admin Café'],
            ['email' => 'info@bbtspr.com', 'name' => 'Admin Café'],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make('123456'),
                    'role' => 'admin',
                    'active' => true,
                ]
            );
        }
    }
}
