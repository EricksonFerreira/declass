<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table): void {
            if (! Schema::hasColumn('reservas', 'checkin_realizado_em')) {
                $table->timestamp('checkin_realizado_em')->nullable()->after('data_cancelamento');
            }
            if (! Schema::hasColumn('reservas', 'checkin_assinado_em')) {
                $table->timestamp('checkin_assinado_em')->nullable()->after('checkin_realizado_em');
            }
            if (! Schema::hasColumn('reservas', 'calcao_registrado_em')) {
                $table->timestamp('calcao_registrado_em')->nullable()->after('checkin_assinado_em');
            }
            if (! Schema::hasColumn('reservas', 'checkout_realizado_em')) {
                $table->timestamp('checkout_realizado_em')->nullable()->after('calcao_registrado_em');
            }
            if (! Schema::hasColumn('reservas', 'checkout_assinado_em')) {
                $table->timestamp('checkout_assinado_em')->nullable()->after('checkout_realizado_em');
            }
            if (! Schema::hasColumn('reservas', 'revisao_checkout_concluida_em')) {
                $table->timestamp('revisao_checkout_concluida_em')->nullable()->after('revisao_checkout_concluida');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table): void {
            foreach ([
                'checkin_realizado_em',
                'checkin_assinado_em',
                'calcao_registrado_em',
                'checkout_realizado_em',
                'checkout_assinado_em',
                'revisao_checkout_concluida_em',
            ] as $column) {
                if (Schema::hasColumn('reservas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
