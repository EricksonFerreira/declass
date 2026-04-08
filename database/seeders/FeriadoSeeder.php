<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeriadoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['data' => '2026-01-01', 'nome' => 'Confraternização Universal', 'global' => 'S', 'ativo' => 'S'],
            ['data' => '2026-04-21', 'nome' => 'Tiradentes', 'global' => 'S', 'ativo' => 'S'],
            ['data' => '2026-05-01', 'nome' => 'Dia do Trabalhador', 'global' => 'S', 'ativo' => 'S'],
        ];

        foreach ($items as $it) {
            DB::table('feriados')->updateOrInsert([
                'empresa_id' => null,
                'estacao_id' => null,
                'data' => $it['data'],
            ], [
                'nome' => $it['nome'],
                'global' => $it['global'],
                'ativo' => $it['ativo'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
