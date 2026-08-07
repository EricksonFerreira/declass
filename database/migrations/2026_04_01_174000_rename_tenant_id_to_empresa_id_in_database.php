<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        $tables = $this->tablesWithColumn('tenant_id');

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            if (!Schema::hasColumn($tableName, 'tenant_id') || Schema::hasColumn($tableName, 'empresa_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('tenant_id', 'empresa_id');
            });
        }
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        $tables = $this->tablesWithColumn('empresa_id');

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            if (!Schema::hasColumn($tableName, 'empresa_id') || Schema::hasColumn($tableName, 'tenant_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('empresa_id', 'tenant_id');
            });
        }
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * @return array<int, string>
     */
    private function tablesWithColumn(string $columnName): array
    {
        $databaseName = DB::getDatabaseName();

        $rows = DB::select(
            'SELECT TABLE_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND COLUMN_NAME = ?',
            [$databaseName, $columnName]
        );

        return array_map(static fn ($row) => $row->TABLE_NAME, $rows);
    }
};
