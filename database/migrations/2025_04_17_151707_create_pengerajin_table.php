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
        Schema::create('pengerajin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('kode_pengerajin')->unique();
            $table->string('nama_pengerajin')->nullable();
            $table->enum('jk_pengerajin', ['P', 'W'])->nullable();
            $table->integer('usia_pengerajin')->nullable();
            $table->string('telp_pengerajin')->nullable();
            $table->string('email_pengerajin')->nullable();
            $table->string('alamat_pengerajin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengerajin');
    }
};
