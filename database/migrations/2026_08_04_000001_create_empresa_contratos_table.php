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
            $table->string('numero', 50)->unique();
            $table->string('titulo');
            $table->string('status', 30)->default('rascunho');
            $table->longText('conteudo_html')->nullable();
            $table->string('arquivo_pdf_path')->nullable();
            $table->timestamp('gerado_em')->nullable();
            $table->timestamp('assinado_em')->nullable();
            $table->unsignedBigInteger('gerado_por')->nullable();
            $table->unsignedBigInteger('assinado_por')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_contratos');
    }
};
