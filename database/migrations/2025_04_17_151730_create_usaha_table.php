<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usaha', function (Blueprint $table) {
            $table->id();
            $table->string('kode_usaha')->unique();
            $table->string('nama_usaha')->nullable();
            $table->string('telp_usaha')->nullable();
            $table->string('email_usaha')->nullable();
            $table->string('deskripsi_usaha')->nullable();
            $table->string('foto_usaha')->nullable();
            $table->string('link_gmap_usaha')->nullable();
            $table->enum('status_usaha', ['aktif', 'nonaktif', 'tutup', 'pending', 'dibekukan'])->default('aktif');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usaha');
    }
};
