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
                'tipo_frota_id'         => 'tipo_frota',
                'grupo_frota_id'        => 'grupos_frota',
                'marca_id'              => 'marcas',
                'modelo_id'             => 'modelos',
                'segmento_id'           => 'segmentos',
                'status_veiculo_id'     => 'status_veiculos',
                'local_disponibilidade' => 'estacoes',
            ];

            foreach ($foreigns as $column => $references) {
                try {
                    $table->foreign($column)->references('id')->on($references)->nullOnDelete();
                } catch (\Throwable $e) {
                    // Foreign key already exists or column missing — skip
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('veiculos', function (Blueprint $table) {
            $columns = [
                'tipo_frota_id', 'grupo_frota_id', 'marca_id', 'modelo_id',
                'segmento_id', 'status_veiculo_id', 'local_disponibilidade',
            ];

            foreach ($columns as $column) {
                try {
                    $table->dropForeign([$column]);
                } catch (\Throwable $e) {
                    // Foreign key doesn't exist — skip
                }
            }
        });
    }
};
