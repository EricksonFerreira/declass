<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('itens_manutencao_preventiva', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });

        Schema::create('planos_manutencao_preventiva', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->integer('grupo_frota_id');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('grupo_frota_id')->references('id')->on('grupos_frota')->onDelete('cascade');
        });

        Schema::create('plano_manutencao_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plano_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('frequencia_meses')->nullable();
            $table->integer('frequencia_km')->nullable();
            $table->timestamps();

            $table->foreign('plano_id')->references('id')->on('planos_manutencao_preventiva')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('itens_manutencao_preventiva')->onDelete('cascade');
        });

        Schema::create('pendencias_manutencao_preventiva', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->integer('veiculo_id');
            $table->unsignedBigInteger('item_id');
            $table->date('data_prevista')->nullable();
            $table->integer('km_previsto')->nullable();
            $table->string('status')->default('pendente'); // pendente, atrasado
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('veiculo_id')->references('id')->on('veiculos')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('itens_manutencao_preventiva')->onDelete('cascade');
        });

        Schema::create('execucoes_manutencao_preventiva', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->integer('veiculo_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('ordem_servico_id')->nullable();
            $table->date('data_execucao');
            $table->integer('km_execucao');
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('veiculo_id')->references('id')->on('veiculos')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('itens_manutencao_preventiva')->onDelete('cascade');
            $table->foreign('ordem_servico_id')->references('id')->on('ordens_servico')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('execucoes_manutencao_preventiva');
        Schema::dropIfExists('pendencias_manutencao_preventiva');
        Schema::dropIfExists('plano_manutencao_itens');
        Schema::dropIfExists('planos_manutencao_preventiva');
        Schema::dropIfExists('itens_manutencao_preventiva');
    }
};
