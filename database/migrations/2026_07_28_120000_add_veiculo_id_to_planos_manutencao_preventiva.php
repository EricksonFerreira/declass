<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('planos_manutencao_preventiva')) {
            return;
        }

        Schema::table('planos_manutencao_preventiva', function (Blueprint $table) {
            if (!Schema::hasColumn('planos_manutencao_preventiva', 'veiculo_id')) {
                $table->unsignedInteger('veiculo_id')->nullable()->after('empresa_id');
                $table->foreign('veiculo_id')->references('id')->on('veiculos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('planos_manutencao_preventiva')) {
            return;
        }

        Schema::table('planos_manutencao_preventiva', function (Blueprint $table) {
            if (Schema::hasColumn('planos_manutencao_preventiva', 'veiculo_id')) {
                $table->dropForeign(['veiculo_id']);
                $table->dropColumn('veiculo_id');
            }
        });
    }
};
