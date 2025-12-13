<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected function getDbCart()
    {
        return Cart::firstOrCreate(['user_id' => Auth::id()]);
    }

    public function index()
    {
        if (Auth::check()) {
            $cartDB = $this->getDbCart()->load('items.produk.fotoProduk');
            return view('guest.pages.cart', compact('cartDB'));
        }

        // guest: session cart
        $sessionCart = session('cart', []);

        return view('guest.pages.cart', [
            'sessionCart' => $sessionCart
        ]);
    }


    public function add(Request $request, $slug)
    {
        $produk = Produk::where('slug', $slug)->firstOrFail();

        // USER LOGIN → simpan ke DB
        if (Auth::check()) {

            $cart = $this->getDbCart();

            // CARI ITEM DULU
            $item = $cart->items()->where('produk_id', $produk->id)->first();

            if ($item) {
                // UPDATE MANUAL TANPA RAW
                $item->quantity += 1;
                $item->save();
            } else {
                // INSERT
                $cart->items()->create([
                    'produk_id' => $produk->id,
                    'quantity' => 1
                ]);
            }

            return back()->with('success', 'Berhasil ditambahkan ke keranjang!');
        }

        // GUEST → simpan ke session
        $cart = session('cart', []);

        if (isset($cart[$produk->id])) {
            $cart[$produk->id]['quantity']++;
        } else {
            $cart[$produk->id] = [
                'id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'harga' => $produk->harga,
                'foto' => optional($produk->fotoProduk->first())->file_foto_produk,
                'quantity' => 1,
            ];
        }

        session(['cart' => $cart]);

        return back()->with('success', 'Berhasil ditambahkan ke keranjang!');
    }
    

    public function update(Request $request)
    {
        // USER LOGIN → update DB
        if (Auth::check()) {
            $cart = $this->getDbCart();

            foreach ($request->quantity as $itemId => $qty) {
                $qty = max(1, (int) $qty);
                $cart->items()->where('id', $itemId)->update(['quantity' => $qty]);
            }

            return back()->with('success', 'Keranjang diperbarui!');
        }

        // GUEST → update session
        $cart = session('cart', []);

        foreach ($request->quantity as $id => $qty) {
            $qty = max(1, (int) $qty);
            if (isset($cart[$id])) {
                $cart[$id]['quantity'] = $qty;
            }
        }

        session(['cart' => $cart]);

        return back()->with('success', 'Keranjang diperbarui!');
    }

    public function remove($id)
    {
        // USER LOGIN → hapus dari DB
        if (Auth::check()) {
            $this->getDbCart()->items()->where('id', $id)->delete();
            return back()->with('success', 'Item dihapus.');
        }

        // GUEST → hapus dari session
        $cart = session('cart', []);
        unset($cart[$id]);
        session(['cart' => $cart]);

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    public function clear()
    {
        // USER LOGIN → hapus semua DB item
        if (Auth::check()) {
            $this->getDbCart()->items()->delete();
            return back()->with('success', 'Keranjang dikosongkan.');
        }

        // GUEST → clear session
        session()->forget('cart');
        return back()->with('success', 'Keranjang dikosongkan.');
    }

    // DIPANGGIL SETELAH LOGIN BERHASIL
    public function mergeSessionCart()
    {
        if (!Auth::check())
            return;

        $sessionCart = session('cart', []);
        if (empty($sessionCart))
            return;

        $cart = $this->getDbCart();

        foreach ($sessionCart as $item) {
            $cart->items()->updateOrCreate(
                ['produk_id' => $item['id']],
                ['quantity' => DB::raw('quantity + ' . (int) $item['quantity'])]
            );
        }

        // Setelah merge, kosongkan session cart
        session()->forget('cart');
    }
}
