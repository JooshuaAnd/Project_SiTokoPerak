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

        Schema::table('carts', function (Blueprint $table) {

            // 1. DROP FOREIGN KEY user_id
            $table->dropForeign(['user_id']);

            // 2. DROP UNIQUE INDEX user_id
            $table->dropUnique('carts_user_id_unique');

            // 3. CREATE INDEX NORMAL
            $table->index('user_id');

            // 4. ADD FOREIGN KEY AGAIN (WITHOUT UNIQUE)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // 5. DROP UNIQUE ON session_id
            $table->dropUnique('carts_session_id_unique');

            // 6. ADD NORMAL INDEX
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Rollback logic
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['session_id']);

            $table->unique('user_id');
            $table->unique('session_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
