<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reservas')) {
            return;
        }

        Schema::table('reservas', function (Blueprint $table): void {
            if (!Schema::hasColumn('reservas', 'criado_por_user_id')) {
                $table->unsignedBigInteger('criado_por_user_id')->nullable()->index()->after('user_id');
            }

            if (!Schema::hasColumn('reservas', 'origem_cadastro')) {
                $table->string('origem_cadastro', 20)->default('colaborador')->index()->after('criado_por_user_id');
            }
        });

        if (Schema::hasColumn('reservas', 'criado_por_user_id')) {
            try {
                Schema::table('reservas', function (Blueprint $table): void {
                    $table->foreign('criado_por_user_id')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Throwable) {
                // Ignora caso a FK já exista em ambiente legado.
            }
        }

        if (!Schema::hasTable('reserva_logs')) {
            return;
        }

        $logs = DB::table('reserva_logs')
            ->select(['reserva_id', 'user_id', 'acao', 'detalhes'])
            ->whereNotNull('reserva_id')
            ->orderBy('id')
            ->get();

        $updates = [];
        foreach ($logs as $log) {
            $reservaId = (int) $log->reserva_id;
            if ($reservaId <= 0 || isset($updates[$reservaId])) {
                continue;
            }

            $detalhes = (string) ($log->detalhes ?? '');
            $acao = (string) ($log->acao ?? '');

            if (str_contains($detalhes, 'site-apresentacao')) {
                $updates[$reservaId] = [
                    'origem_cadastro' => 'site',
                    'criado_por_user_id' => null,
                ];
                continue;
            }

            if ($acao === 'Reserva criada') {
                $updates[$reservaId] = [
                    'origem_cadastro' => 'colaborador',
                    'criado_por_user_id' => !empty($log->user_id) ? (int) $log->user_id : null,
                ];
            }
        }

        foreach ($updates as $reservaId => $payload) {
            DB::table('reservas')->where('id', $reservaId)->update($payload);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('reservas')) {
            return;
        }

        Schema::table('reservas', function (Blueprint $table): void {
            if (Schema::hasColumn('reservas', 'criado_por_user_id')) {
                try {
                    $table->dropForeign(['criado_por_user_id']);
                } catch (\Throwable) {
                    // sem ação
                }
                $table->dropColumn('criado_por_user_id');
            }

            if (Schema::hasColumn('reservas', 'origem_cadastro')) {
                $table->dropColumn('origem_cadastro');
            }
        });
    }
};
