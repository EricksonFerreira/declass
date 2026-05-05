<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $foreigns = [
                'grupo_frota_id'        => ['grupos_frota', 'id'],
                'marca_id'              => ['marcas', 'id'],
                'modelo_id'             => ['modelos', 'id'],
                'segmento_id'           => ['segmentos', 'id'],
                'status_veiculo_id'     => ['status_veiculos', 'id'],
                'local_disponibilidade' => ['estacoes', 'id'],
            ];

            foreach ($foreigns as $column => [$refTable, $refCol]) {
                try {
                    $table->foreign($column)->references($refCol)->on($refTable)->nullOnDelete();
                } catch (\Throwable $e) {
                    // skip
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $columns = ['grupo_frota_id', 'marca_id', 'modelo_id',
                'segmento_id', 'status_veiculo_id', 'local_disponibilidade'];

            foreach ($columns as $column) {
                try {
                    $table->dropForeign([$column]);
                } catch (\Throwable $e) {
                    // skip
                }
            }
        });
    }
};
