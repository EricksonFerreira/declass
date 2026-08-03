<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documentacoes_veiculo')) {
            return;
        }

        Schema::table('documentacoes_veiculo', function (Blueprint $table) {
            if (!Schema::hasColumn('documentacoes_veiculo', 'datainc')) {
                $table->dateTime('datainc')->nullable()->after('categoria_financeira_id');
            }

            if (!Schema::hasColumn('documentacoes_veiculo', 'usuinc')) {
                $table->unsignedBigInteger('usuinc')->nullable()->after('datainc');
            }

            if (!Schema::hasColumn('documentacoes_veiculo', 'datatu')) {
                $table->dateTime('datatu')->nullable()->after('usuinc');
            }

            if (!Schema::hasColumn('documentacoes_veiculo', 'usuatu')) {
                $table->unsignedBigInteger('usuatu')->nullable()->after('datatu');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('documentacoes_veiculo')) {
            return;
        }

        Schema::table('documentacoes_veiculo', function (Blueprint $table) {
            foreach (['usuatu', 'datatu', 'usuinc', 'datainc'] as $column) {
                if (Schema::hasColumn('documentacoes_veiculo', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
