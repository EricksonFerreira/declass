<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var string[]
     */
    private array $tables = [
        'termo_consentimento',
        'permissions',
        'roles',
        'users',
        'empresas',
    ];

    public function up(): void
    {
        $defaultEmpresaId = null;

        if (Schema::hasTable('empresas')) {
            $defaultEmpresaId = DB::table('empresas')->value('id');
        }

        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            if (!Schema::hasColumn($tableName, 'empresa_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('empresa_id')->nullable();
                    $table->index('empresa_id');
                });
            }

            if ($tableName === 'empresas') {
                DB::table('empresas')
                    ->whereNull('empresa_id')
                    ->update(['empresa_id' => DB::raw('id')]);
                continue;
            }

            if ($defaultEmpresaId !== null) {
                DB::table($tableName)->whereNull('empresa_id')->update(['empresa_id' => $defaultEmpresaId]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'empresa_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['empresa_id']);
                $table->dropColumn('empresa_id');
            });
        }
    }
};
