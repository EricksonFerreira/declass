<?php

namespace Database\Seeders;

use App\Models\OsCategoria;
use Illuminate\Database\Seeder;

class OsCategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $limpeza = OsCategoria::firstOrCreate(['nome' => 'Limpeza']);
        $limpeza->subcategorias()->firstOrCreate([
            'nome' => 'Limpeza Simples',
        ], [
            'show_sinistro' => false,
            'show_reserva' => true,
            'show_avarias' => false,
            'show_checklist_prevencao' => false,
        ]);
        $limpeza->subcategorias()->firstOrCreate([
            'nome' => 'Limpeza Detalhada',
        ], [
            'show_sinistro' => false,
            'show_reserva' => true,
            'show_avarias' => false,
            'show_checklist_prevencao' => false,
        ]);

        $prevencao = OsCategoria::firstOrCreate(['nome' => 'Prevenção']);
        $prevencao->subcategorias()->firstOrCreate([
            'nome' => 'Revisão Padrão',
        ], [
            'show_sinistro' => false,
            'show_reserva' => false,
            'show_avarias' => false,
            'show_checklist_prevencao' => true,
        ]);
        
        $corretiva = OsCategoria::firstOrCreate(['nome' => 'Corretiva']);
        $corretiva->subcategorias()->firstOrCreate([
            'nome' => 'Mecânica',
        ], [
            'show_sinistro' => true,
            'show_reserva' => false,
            'show_avarias' => true,
            'show_checklist_prevencao' => false,
        ]);
        $corretiva->subcategorias()->firstOrCreate([
            'nome' => 'Funilaria e Pintura',
        ], [
            'show_sinistro' => true,
            'show_reserva' => false,
            'show_avarias' => true,
            'show_checklist_prevencao' => false,
        ]);
    }
}
