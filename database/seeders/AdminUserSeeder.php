<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Cek dulu biar gak double
        User::firstOrCreate(
            ['email' => 'admin@csi.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin1'), // ganti dengan password kuat
            ]
        );
    }
}
