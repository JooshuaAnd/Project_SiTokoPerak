<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Produk;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereBetween('id', [2, 10])->get();
        $products = Produk::whereIn('id', [5, 6, 7])->get();

        for ($i = 0; $i < 5; $i++) {
            $user = $users->random();
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'customer_name' => $user->name,
                'customer_phone' => $user->phone ?? '08123456789',
                'customer_address' => $user->address ?? 'Alamat tidak diketahui',
                'total_amount' => 0,
                'status' => 'selesai',
            ]);

            $totalAmount = 0;
            $itemCount = rand(1, 3);

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 2);
                $price = $product->harga;

                OrderItem::create([
                    'order_id' => $order->id,
                    'produk_id' => $product->id,
                    'quantity' => $quantity,
                    'price_at_purchase' => $price,
                ]);

                $totalAmount += $quantity * $price;
            }

            $order->update(['total_amount' => $totalAmount]);
        }
    }
}
