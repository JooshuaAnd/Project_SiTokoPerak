<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $tableName = 'order_items';
        $column = 'usaha_produk_id';

        // kalau kolomnya memang sudah tidak ada, stop.
        if (!Schema::hasColumn($tableName, $column)) {
            return;
        }

        $dbName = DB::getDatabaseName();

        // 1) DROP FOREIGN KEY kalau ada (nama constraint bisa beda-beda)
        $fk = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->select('CONSTRAINT_NAME')
            ->where('TABLE_SCHEMA', $dbName)
            ->where('TABLE_NAME', $tableName)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->first();

        if ($fk && !empty($fk->CONSTRAINT_NAME) && $fk->CONSTRAINT_NAME !== 'PRIMARY') {
            DB::statement("ALTER TABLE `$tableName` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // 2) DROP INDEX yang memakai kolom itu (nama index juga bisa beda)
        $indexes = DB::table('information_schema.STATISTICS')
            ->select('INDEX_NAME')
            ->where('TABLE_SCHEMA', $dbName)
            ->where('TABLE_NAME', $tableName)
            ->where('COLUMN_NAME', $column)
            ->where('INDEX_NAME', '!=', 'PRIMARY')
            ->distinct()
            ->pluck('INDEX_NAME');

        foreach ($indexes as $idx) {
            DB::statement("ALTER TABLE `$tableName` DROP INDEX `$idx`");
        }

        // 3) DROP COLUMN
        Schema::table($tableName, function (Blueprint $table) use ($tableName, $column) {
            if (Schema::hasColumn($tableName, $column)) {
                $table->dropColumn($column);
            }
        });
    }

    public function down(): void
    {
        $tableName = 'order_items';
        $column = 'usaha_produk_id';

        if (Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->unsignedBigInteger($column)->nullable()->after('produk_id');
            $table->index($column); // biar aman kalau sewaktu-waktu rollback
        });
    }
};
