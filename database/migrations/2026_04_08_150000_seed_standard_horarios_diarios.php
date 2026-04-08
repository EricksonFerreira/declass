<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $padroes = [
            ['nome' => 'Dias da Semana'],
            ['nome' => 'Sábados'],
            ['nome' => 'Domingos'],
            ['nome' => 'Feriados'],
        ];

        foreach ($padroes as $item) {
            $existe = DB::table('horarios_diarios')
                ->whereRaw('LOWER(nome) = ?', [strtolower($item['nome'])])
                ->whereNull('empresa_id')
                ->exists();

            if (!$existe) {
                DB::table('horarios_diarios')->insert([
                    'nome'       => $item['nome'],
                    'ativo'      => 'S',
                    'empresa_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('horarios_diarios')
            ->whereIn('nome', ['Dias da Semana', 'Sábados', 'Domingos', 'Feriados'])
            ->whereNull('empresa_id')
            ->delete();
    }
};
