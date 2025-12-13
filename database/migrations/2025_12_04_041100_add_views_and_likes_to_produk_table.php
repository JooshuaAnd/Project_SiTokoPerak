<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // === PRODUK VIEWS (1 session 1x view) ===
        Schema::create('produk_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->string('session_id'); // identitas user/guest
            $table->timestamps();

            $table->unique(['produk_id', 'session_id']);
        });

        // === PRODUK LIKES (1 session 1x like) ===
        Schema::create('produk_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->string('session_id'); // identitas user/guest
            $table->timestamps();

            $table->unique(['produk_id', 'session_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('produk_views');
        Schema::dropIfExists('produk_likes');
    }
};
