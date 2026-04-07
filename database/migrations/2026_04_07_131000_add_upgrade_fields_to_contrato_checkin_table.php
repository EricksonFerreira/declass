<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('contrato_checkin')) {
            return;
        }

        Schema::table('contrato_checkin', function (Blueprint $table) {
            if (!Schema::hasColumn('contrato_checkin', 'grupo_frota_original_id')) {
                $table->unsignedBigInteger('grupo_frota_original_id')->nullable()->after('veiculo_id')
                    ->comment('Grupo/categoria originalmente reservado');
            }
            if (!Schema::hasColumn('contrato_checkin', 'veiculo_previsto_id')) {
                $table->unsignedBigInteger('veiculo_previsto_id')->nullable()->after('grupo_frota_original_id')
                    ->comment('Veiculo que seria entregue originalmente, se houver');
            }
            if (!Schema::hasColumn('contrato_checkin', 'houve_upgrade_categoria')) {
                $table->boolean('houve_upgrade_categoria')->default(false)->after('veiculo_previsto_id');
            }
            if (!Schema::hasColumn('contrato_checkin', 'motivo_upgrade')) {
                $table->string('motivo_upgrade', 500)->nullable()->after('houve_upgrade_categoria');
            }
            if (!Schema::hasColumn('contrato_checkin', 'usuario_upgrade_id')) {
                $table->unsignedBigInteger('usuario_upgrade_id')->nullable()->after('motivo_upgrade');
            }
            if (!Schema::hasColumn('contrato_checkin', 'data_hora_upgrade')) {
                $table->datetime('data_hora_upgrade')->nullable()->after('usuario_upgrade_id');
            }
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('contrato_checkin')) {
            return;
        }

        Schema::table('contrato_checkin', function (Blueprint $table) {
            $cols = [
                'grupo_frota_original_id',
                'veiculo_previsto_id',
                'houve_upgrade_categoria',
                'motivo_upgrade',
                'usuario_upgrade_id',
                'data_hora_upgrade',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('contrato_checkin', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::enableForeignKeyConstraints();
    }
};
