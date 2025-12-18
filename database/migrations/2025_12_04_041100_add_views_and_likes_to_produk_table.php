<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // === PRODUK VIEWS (1 user/guest 1x view) ===
        Schema::create('produk_views', function (Blueprint $table) {
            $table->id();

            $table->foreignId('produk_id')
                ->constrained('produk')
                ->onDelete('cascade');

            // login user
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // guest identifier (pakai session()->getId() / token)
            $table->string('guest_id', 255)->nullable()->index();

            $table->timestamps();

            // Unique untuk user login
            $table->unique(['produk_id', 'user_id']);

            // Unique untuk guest
            $table->unique(['produk_id', 'guest_id']);
        });

        // === PRODUK LIKES (1 user/guest 1x like) ===
        Schema::create('produk_likes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('produk_id')
                ->constrained('produk')
                ->onDelete('cascade');

            // login user
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // guest identifier
            $table->string('guest_id', 255)->nullable()->index();

            $table->timestamps();

            // Unique untuk user login
            $table->unique(['produk_id', 'user_id']);

            // Unique untuk guest
            $table->unique(['produk_id', 'guest_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('produk_views');
        Schema::dropIfExists('produk_likes');
    }
};
