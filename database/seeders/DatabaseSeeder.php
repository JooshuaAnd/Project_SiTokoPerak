<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            UserSeeder::class,
            ReviewSeeder::class,
            ProdukSeeder::class,
            OrderSeeder::class,
            // KategoriProdukSeeder::class,
            // JenisUsahaSeeder::class,
            // ProdukSeeder::class,
            // KategoriProdukSeeder::class,
            // JenisUsahaSeeder::class,
            // ProdukSeeder::class,
            // KategoriProdukSeeder::class,
        ]);
    }
}
