<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            "reviews" => "required|array",
            "reviews.*.produk_id" => "required|exists:produk,id",
            "reviews.*.rating" => "required|integer|min:1|max:5",
            "reviews.*.komentar" => "nullable|string",
        ]);

        foreach ($request->reviews as $reviewData) {
            // Check if user has already reviewed this product from this order maybe?
            // For now, let's just create the review.
            Review::updateOrCreate(
                [
                    "user_id" => Auth::id(),
                    "produk_id" => $reviewData["produk_id"],
                ],
                [
                    "rating" => $reviewData["rating"],
                    "komentar" => $reviewData["komentar"],
                ],
            );
        }

        return back()->with("success", "Ulasan Anda telah berhasil dikirim.");
    }
}
