<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ordem_servico_itens', function (Blueprint $table) {
            $table->unsignedBigInteger('pendencia_manutencao_preventiva_id')->nullable();
            $table->foreign('pendencia_manutencao_preventiva_id', 'fk_osi_pendencia')->references('id')->on('pendencias_manutencao_preventiva')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('ordem_servico_itens', function (Blueprint $table) {
            $table->dropForeign('fk_osi_pendencia');
            $table->dropColumn('pendencia_manutencao_preventiva_id');
        });
    }
};
