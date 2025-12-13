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
        Schema::create('usaha_pengerajin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usaha_id')->constrained('usaha')->onDelete('cascade');
            $table->foreignId('pengerajin_id')->constrained('pengerajin')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usaha_pengerajin');
    }
};
