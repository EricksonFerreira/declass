<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('permission.table_names.permissions', 'permissions');

        if (!Schema::hasTable($table)) {
            return;
        }

        if (!Schema::hasColumn($table, 'grupo')) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('grupo', 80)->nullable()->after('descricao')->index();
            });
        }

        // Auto-populate grupo from the permission name prefix (reserva.index → reserva)
        DB::statement("
            UPDATE {$table}
            SET grupo = CASE
                WHEN name LIKE '%.%' THEN SUBSTRING_INDEX(name, '.', 1)
                ELSE name
            END
            WHERE grupo IS NULL OR grupo = ''
        ");
    }

    public function down(): void
    {
        $table = config('permission.table_names.permissions', 'permissions');

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'grupo')) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('grupo');
            });
        }
    }
};
