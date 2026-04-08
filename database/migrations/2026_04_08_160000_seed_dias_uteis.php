<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dias = [
            ['dia' => 1, 'nome' => 'Segunda-feira'],
            ['dia' => 2, 'nome' => 'Terça-feira'],
            ['dia' => 3, 'nome' => 'Quarta-feira'],
            ['dia' => 4, 'nome' => 'Quinta-feira'],
            ['dia' => 5, 'nome' => 'Sexta-feira'],
            ['dia' => 6, 'nome' => 'Sábado'],
            ['dia' => 7, 'nome' => 'Domingo'],
        ];

        foreach ($dias as $item) {
            $existe = DB::table('dias_uteis')->where('dia', $item['dia'])->exists();
            if (!$existe) {
                DB::table('dias_uteis')->insert([
                    'dia'        => $item['dia'],
                    'nome'       => $item['nome'],
                    'ativo'      => 'S',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void {}
};
