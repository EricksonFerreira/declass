<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update "Recebimento de Calção" to "Caução recebida" and set code "C.01"
        DB::table('categorias_financeiras')
            ->where('nome', 'Recebimento de Calção')
            ->update([
                'nome' => 'Caução recebida',
                'codigo' => 'C.01'
            ]);

        // 2. Update "Devolução de Calção" to "Devolução de caução" and set code "C.02"
        DB::table('categorias_financeiras')
            ->where('nome', 'Devolução de Calção')
            ->update([
                'nome' => 'Devolução de caução',
                'codigo' => 'C.02'
            ]);
    }

    public function down(): void
    {
        DB::table('categorias_financeiras')
            ->where('nome', 'Caução recebida')
            ->update([
                'nome' => 'Recebimento de Calção'
            ]);

        DB::table('categorias_financeiras')
            ->where('nome', 'Devolução de caução')
            ->update([
                'nome' => 'Devolução de Calção'
            ]);
    }
};
