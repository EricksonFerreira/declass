<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('tipo');
            $table->string('numero')->nullable();
            $table->string('orgao_emissor')->nullable();
            $table->text('descricao')->nullable();
            $table->decimal('valor', 14, 2)->nullable(); // ex.: valor do atestado (CAT)
            $table->date('data_emissao')->nullable();
            $table->date('data_validade')->nullable();
            $table->string('arquivo_path', 500)->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'tipo']);
            $table->index(['empresa_id', 'data_validade']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_documentos');
    }
};
