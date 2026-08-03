<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documentacao_veiculo_parcelas')) {
            return;
        }

        Schema::table('documentacao_veiculo_parcelas', function (Blueprint $table) {
            if (!Schema::hasColumn('documentacao_veiculo_parcelas', 'datainc')) {
                $table->dateTime('datainc')->nullable()->after('status');
            }

            if (!Schema::hasColumn('documentacao_veiculo_parcelas', 'usuinc')) {
                $table->unsignedBigInteger('usuinc')->nullable()->after('datainc');
            }

            if (!Schema::hasColumn('documentacao_veiculo_parcelas', 'datatu')) {
                $table->dateTime('datatu')->nullable()->after('usuinc');
            }

            if (!Schema::hasColumn('documentacao_veiculo_parcelas', 'usuatu')) {
                $table->unsignedBigInteger('usuatu')->nullable()->after('datatu');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('documentacao_veiculo_parcelas')) {
            return;
        }

        Schema::table('documentacao_veiculo_parcelas', function (Blueprint $table) {
            foreach (['usuatu', 'datatu', 'usuinc', 'datainc'] as $column) {
                if (Schema::hasColumn('documentacao_veiculo_parcelas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
