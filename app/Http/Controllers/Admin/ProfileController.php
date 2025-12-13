<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        return view('admin.profile.profile');
    }

    public function edit()
    {
        return view('admin.profile.edit-profile');
    }

    public function update(Request $request)
    {
        // Logic to update the profile
        return redirect()->route('admin.profile-index')
            ->with('success', 'Profile berhasil diperbarui.');
    }
}
