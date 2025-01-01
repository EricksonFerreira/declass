<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignKeysOnGrupoFrotaCaracteristicaFrota extends Migration
{
    public function up()
    {
        Schema::table('grupo_frota_caracteristica_frota', function (Blueprint $table) {
            // Remover chaves estrangeiras existentes
            $table->dropForeign(['grupo_frota_id']);
            $table->dropForeign(['caracteristica_frota_id']);

            // Adicionar novas chaves estrangeiras com ON DELETE CASCADE
            $table->foreign('grupo_frota_id')
                  ->references('id')->on('grupos_frota')
                  ->onDelete('cascade');

            $table->foreign('caracteristica_frota_id')
                  ->references('id')->on('caracteristicas_frota')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('grupo_frota_caracteristica_frota', function (Blueprint $table) {
            // Remover chaves estrangeiras com ON DELETE CASCADE
            $table->dropForeign(['grupo_frota_id']);
            $table->dropForeign(['caracteristica_frota_id']);

            // Adicionar chaves estrangeiras originais
            $table->foreign('grupo_frota_id')
                  ->references('id')->on('grupos_frota')
                  ->onDelete('restrict');

            $table->foreign('caracteristica_frota_id')
                  ->references('id')->on('caracteristicas_frota')
                  ->onDelete('restrict');
        });
    }
}
