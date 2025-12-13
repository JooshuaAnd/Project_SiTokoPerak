<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Produk;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guestUsers = User::where('role', 'guest')->get();
        $products = Produk::whereIn('id', [5, 6, 7])->get();

        foreach ($products as $product) {
            foreach ($guestUsers->random(5) as $user) {
                Review::create([
                    'produk_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => rand(3, 5),
                    'komentar' => fake()->paragraph,
                ]);
            }
        }
    }
}
