<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            if (!Schema::hasIndex('reservas', 'reservas_empresa_status_data_index')) {
                $table->index(['empresa_id', 'status_reserva_id', 'data_inicio', 'data_fim'], 'reservas_empresa_status_data_index');
            }
            if (!Schema::hasIndex('reservas', 'reservas_veiculo_status_data_index')) {
                $table->index(['veiculo_id', 'status_reserva_id', 'data_inicio', 'data_fim'], 'reservas_veiculo_status_data_index');
            }
            if (!Schema::hasIndex('reservas', 'reservas_status_data_index')) {
                $table->index(['status_reserva_id', 'data_inicio', 'data_fim'], 'reservas_status_data_index');
            }
            if (!Schema::hasIndex('reservas', 'reservas_created_at_index')) {
                $table->index('created_at', 'reservas_created_at_index');
            }
            if (!Schema::hasIndex('reservas', 'reservas_empresa_created_at_index')) {
                $table->index(['empresa_id', 'created_at'], 'reservas_empresa_created_at_index');
            }
            if (!Schema::hasIndex('reservas', 'reservas_pagamento_efetuado_index')) {
                $table->index('pagamento_efetuado', 'reservas_pagamento_efetuado_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex([
                'reservas_empresa_status_data_index',
                'reservas_veiculo_status_data_index',
                'reservas_status_data_index',
                'reservas_created_at_index',
                'reservas_empresa_created_at_index',
                'reservas_pagamento_efetuado_index',
            ]);
        });
    }
};
