<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah definisi kolom 'role' yang sudah ada
        Schema::table('users', function (Blueprint $table) {
            // Kita harus mendefinisikan ulang semua opsi enum yang ada
            // dan menambahkan yang baru ('pengrajin').
            // Default tetap 'guest'.
            $table->enum('role', ['admin', 'guest', 'pengerajin'])->default('guest')->change();
        });

        // Alternatif (Opsional): Update role pengguna existing menjadi 'pengrajin' jika diperlukan
        // DB::table('users')->where('role', 'guest')->update(['role' => 'pengrajin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Saat rollback, kembalikan kolom 'role' ke definisi semula (tanpa 'pengrajin')
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'guest'])->default('guest')->change();
        });
    }
};

