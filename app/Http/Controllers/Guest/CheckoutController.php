<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // GET /checkout
    public function index()
    {
        // pastikan user login (juga dijaga middleware 'auth' di route)
        $userId = Auth::id();

        $cart = Cart::firstOrCreate(['user_id' => $userId])
            ->load('items.produk.fotoProduk');

        if ($cart->items->count() === 0) {
            return redirect()->route('cart.index');
        }

        $total = $cart->items->sum(fn ($i) => $i->produk->harga * $i->quantity);

        return view('guest.pages.checkout', compact('cart', 'total'));
    }

    // POST /checkout
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'address' => 'required|string',
            'notes'   => 'nullable|string',
        ]);

        $userId = Auth::id();

        $cart = Cart::firstOrCreate(['user_id' => $userId])
            ->load('items.produk');

        if ($cart->items->count() === 0) {
            return redirect()->route('cart.index');
        }

        $total = $cart->items->sum(fn ($i) => $i->produk->harga * $i->quantity);

        // Biar rapi, pakai transaction
        $order = DB::transaction(function () use ($cart, $request, $total, $userId) {

            $order = Order::create([
                'user_id'         => $userId,
                'order_number'    => 'ORD-' . date('Ymd') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
                'customer_name'   => $request->name,
                'customer_phone'  => $request->phone,
                'customer_address'=> $request->address,
                'total_amount'    => $total,
                'notes'           => $request->notes,
                'status'          => 'baru',
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'produk_id'         => $item->produk_id,
                    'quantity'          => $item->quantity,
                    'price_at_purchase' => $item->produk->harga,
                ]);
            }

            // kosongkan cart setelah order dibuat
            $cart->items()->delete();

            return $order;
        });

        return redirect()
            ->route('checkout.success', $order->order_number)
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    // GET /checkout/success/{orderNumber}
    public function success($orderNumber)
    {
        $order = Order::with('items.produk')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('guest.pages.checkout-success', compact('order'));
    }
}
