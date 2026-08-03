<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lancamentos_financeiros')) {
            return;
        }

        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            if (!Schema::hasColumn('lancamentos_financeiros', 'datainc')) {
                $table->dateTime('datainc')->nullable()->after('grupo_recorrencia_id');
            }

            if (!Schema::hasColumn('lancamentos_financeiros', 'usuinc')) {
                $table->unsignedBigInteger('usuinc')->nullable()->after('datainc');
            }

            if (!Schema::hasColumn('lancamentos_financeiros', 'datatu')) {
                $table->dateTime('datatu')->nullable()->after('usuinc');
            }

            if (!Schema::hasColumn('lancamentos_financeiros', 'usuatu')) {
                $table->unsignedBigInteger('usuatu')->nullable()->after('datatu');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lancamentos_financeiros')) {
            return;
        }

        Schema::table('lancamentos_financeiros', function (Blueprint $table) {
            foreach (['usuatu', 'datatu', 'usuinc', 'datainc'] as $column) {
                if (Schema::hasColumn('lancamentos_financeiros', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
