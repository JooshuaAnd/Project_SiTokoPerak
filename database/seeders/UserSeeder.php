<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password'), // WAJIB di-hash
        //     'role' => 'admin',
        // ]);

        // User::create([
        //     'name' => 'Guest User',
        //     'email' => 'guest@example.com',
        //     'password' => Hash::make('password'), // WAJIB di-hash juga
        //     'role' => 'guest',
        // ]);

        User::create([
            'username' => 'admin',
            'email' => 'admin123@example.com',
            'password' => Hash::make('12345'), // WAJIB di-hash
            'role' => 'admin',
        ]);
        User::create(attributes: [
            'username' => 'saya',
            'email' => 'saya@example.com',
            'password' => Hash::make('12345'), // WAJIB di-hash
            'role' => 'pengerajin',
        ]);
    }
}
