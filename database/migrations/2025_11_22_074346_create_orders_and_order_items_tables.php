<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number')->unique(); // ORD-20251122-0001
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->bigInteger('total_amount');
            $table->enum('status', ['baru', 'dibayar', 'diproses', 'dikirim', 'selesai', 'dibatalkan'])
                ->default('baru');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            // di project kamu tabelnya "produk", bukan "produks"
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->unsignedBigInteger('usaha_produk_id')->nullable();
            $table->foreign('usaha_produk_id')
                ->references('id')->on('usaha_produk')
                ->onDelete('set null');
            $table->integer('quantity');
            $table->bigInteger('price_at_purchase'); // harga saat dibeli
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
