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
        Schema::dropIfExists("review_media"); // Hapus dulu jika sudah ada
        Schema::create("review_media", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("review_id")
                ->constrained("reviews")
                ->onDelete("cascade");
            $table->string("file_path");
            $table->enum("file_type", ["image", "video"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("review_media");
    }
};
