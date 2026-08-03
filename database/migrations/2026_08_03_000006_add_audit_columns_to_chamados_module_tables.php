<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fullAuditTables = [
        'chamados',
        'chamado_categorias',
        'chamado_subcategorias',
        'chamado_statuses',
        'chamado_anexos',
    ];

    private array $insertOnlyTables = [
        'chamado_logs',
    ];

    public function up(): void
    {
        foreach ($this->fullAuditTables as $tableName) {
            $this->addAuditColumns($tableName, true);
        }

        foreach ($this->insertOnlyTables as $tableName) {
            $this->addAuditColumns($tableName, false);
        }
    }

    public function down(): void
    {
        foreach (array_merge($this->fullAuditTables, $this->insertOnlyTables) as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            foreach (['usuatu', 'datatu', 'usuinc', 'datainc'] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    DB::statement("ALTER TABLE `{$tableName}` DROP COLUMN `{$column}`");
                }
            }
        }
    }

    private function addAuditColumns(string $tableName, bool $includeUpdateAudit): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, 'datainc')) {
            DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `datainc` DATETIME NULL");
        }

        if (!Schema::hasColumn($tableName, 'usuinc')) {
            DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `usuinc` BIGINT UNSIGNED NULL");
        }

        if ($includeUpdateAudit) {
            if (!Schema::hasColumn($tableName, 'datatu')) {
                DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `datatu` DATETIME NULL");
            }

            if (!Schema::hasColumn($tableName, 'usuatu')) {
                DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `usuatu` BIGINT UNSIGNED NULL");
            }
        }
    }
};
