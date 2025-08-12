<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Shop',
            'email' => 'shop@example.com',
            'password' => Hash::make('password'),
            'role' => 'shop',
        ]);

        User::create([
            'name' => 'Publisher',
            'email' => 'publisher@example.com',
            'password' => Hash::make('password'),
            'role' => 'publisher',
        ]);
    }
}
