<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('aluno_id')->unique()->constrained('alunos')->cascadeOnDelete();
            $table->string('tipo_sanguineo', 5)->nullable();
            $table->text('alergias')->nullable();
            $table->text('medicamentos')->nullable();
            $table->text('condicoes_cronicas')->nullable();
            $table->string('contato_emergencia_nome')->nullable();
            $table->string('contato_emergencia_parentesco', 60)->nullable();
            $table->string('contato_emergencia_telefone', 30)->nullable();
            $table->text('observacoes')->nullable();
            $table->char('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saude_alunos');
    }
};
