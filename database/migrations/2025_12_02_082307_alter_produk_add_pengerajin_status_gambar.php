<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            // tambah pengerajin_id kalau belum ada di DB kamu
            $table->unsignedBigInteger('pengerajin_id')->nullable()->after('kategori_produk_id');

            // kalau status belum ada, tambahin juga
            if (!Schema::hasColumn('produk', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('slug');
            }

            // kalau gambar belum ada, tambahin juga
            if (!Schema::hasColumn('produk', 'gambar')) {
                $table->string('gambar')->nullable()->after('status');
            }

            // foreign key (optional tapi bagus)
            $table->foreign('pengerajin_id')->references('id')->on('pengerajin')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropForeign(['pengerajin_id']);
            $table->dropColumn('pengerajin_id');

            if (Schema::hasColumn('produk', 'status'))
                $table->dropColumn('status');
            if (Schema::hasColumn('produk', 'gambar'))
                $table->dropColumn('gambar');
        });

    }
};
