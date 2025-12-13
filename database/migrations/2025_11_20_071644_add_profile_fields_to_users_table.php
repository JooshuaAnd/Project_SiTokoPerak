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
        Schema::table("users", function (Blueprint $table) {
            if (!Schema::hasColumn("users", "name")) {
                $table->string("name")->nullable()->after("username");
            }
            if (!Schema::hasColumn("users", "phone")) {
                $table->string("phone")->nullable()->after("email");
            }
            if (!Schema::hasColumn("users", "address")) {
                $table->text("address")->nullable()->after("phone");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            if (Schema::hasColumn("users", "name")) {
                $table->dropColumn("name");
            }
            if (Schema::hasColumn("users", "phone")) {
                $table->dropColumn("phone");
            }
            if (Schema::hasColumn("users", "address")) {
                $table->dropColumn("address");
            }
        });
    }
};
