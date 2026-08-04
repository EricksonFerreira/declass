<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_contratos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('plano_id')->nullable();
            $table->string('numero', 50)->unique();
            $table->string('titulo');
            $table->string('status', 30)->default('rascunho');
            $table->string('razao_social');
            $table->string('cnpj_cliente', 30);
            $table->string('endereco_cliente');
            $table->string('representante_nome');
            $table->decimal('valor', 12, 2)->nullable();
            $table->string('valor_texto', 30)->nullable();
            $table->json('snapshot')->nullable();
            $table->longText('conteudo_html')->nullable();
            $table->string('arquivo_pdf_path')->nullable();
            $table->timestamp('gerado_em')->nullable();
            $table->timestamp('assinado_em')->nullable();
            $table->unsignedBigInteger('gerado_por')->nullable();
            $table->unsignedBigInteger('assinado_por')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->foreign('plano_id')->references('id')->on('planos')->nullOnDelete();
            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_contratos');
    }
};
