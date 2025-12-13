<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua review, urutkan dari yang terbaru, dan eager-load relasi user & produk
        $reviews = Review::latest()->with('user', 'produk')->paginate(20);

        // Kembalikan ke view admin untuk daftar review
        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        // Hapus media terkait jika ada (opsional, tergantung konfigurasi storage)
        // Storage::disk('public')->delete($review->media->pluck('file_path')->toArray());

        // Hapus review dari database
        $review->delete();

        // Redirect kembali dengan pesan sukses
        return back()->with('success', 'Ulasan telah berhasil dihapus.');
    }
}
