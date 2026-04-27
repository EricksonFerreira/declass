<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('horarios_diarios')) {
            return;
        }

        $nomes = [
            0 => 'Semana',
            1 => 'Sábado',
            2 => 'Domingo',
            3 => 'Fériado',
        ];

        $registros = DB::table('horarios_diarios')
            ->where('empresa_id', 1)
            ->orderBy('id')
            ->limit(4)
            ->get(['id']);

        foreach ($registros as $index => $registro) {
            if (!array_key_exists($index, $nomes)) {
                continue;
            }

            DB::table('horarios_diarios')
                ->where('id', $registro->id)
                ->update([
                    'nome' => $nomes[$index],
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('horarios_diarios')) {
            return;
        }

        $nomes = [
            0 => 'Dias da Semana',
            1 => 'Sábados',
            2 => 'Domingos',
            3 => 'Feriados',
        ];

        $registros = DB::table('horarios_diarios')
            ->where('empresa_id', 1)
            ->orderBy('id')
            ->limit(4)
            ->get(['id']);

        foreach ($registros as $index => $registro) {
            if (!array_key_exists($index, $nomes)) {
                continue;
            }

            DB::table('horarios_diarios')
                ->where('id', $registro->id)
                ->update([
                    'nome' => $nomes[$index],
                    'updated_at' => now(),
                ]);
        }
    }
};
