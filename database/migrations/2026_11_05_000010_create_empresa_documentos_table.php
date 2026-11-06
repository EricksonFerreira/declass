<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documentos institucionais do tenant (spec §5):
 * contrato social, alvara, certidoes, dados da mantenedora etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('tipo', 60);
            $table->string('titulo')->nullable();
            $table->string('numero')->nullable();
            $table->string('orgao_emissor')->nullable();
            $table->text('descricao')->nullable();
            $table->decimal('valor', 12, 2)->nullable();
            $table->date('data_emissao')->nullable();
            $table->date('data_validade')->nullable();
            $table->string('arquivo_path')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_documentos');
    }
};
