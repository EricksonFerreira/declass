<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('documentacoes_veiculo')) {
            return;
        }

        Schema::create('documentacoes_veiculo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedInteger('veiculo_id');
            $table->enum('tipo', ['ipva', 'licenciamento', 'seguro']);
            $table->text('descricao')->nullable();
            $table->integer('parcelas_total')->default(1);
            $table->decimal('valor_parcela', 15, 2)->default(0);
            $table->string('status')->default('ativo');
            $table->foreignId('categoria_financeira_id')->nullable()->constrained('categorias_financeiras')->nullOnDelete();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('veiculo_id')->references('id')->on('veiculos')->onDelete('cascade');
        });

        Schema::create('documentacao_veiculo_parcelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documentacao_veiculo_id');
            $table->integer('numero_parcela');
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->decimal('valor', 15, 2)->default(0);
            $table->string('status')->default('pendente');
            $table->timestamps();

            $table->foreign('documentacao_veiculo_id')->references('id')->on('documentacoes_veiculo')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentacao_veiculo_parcelas');
        Schema::dropIfExists('documentacoes_veiculo');
    }
};
