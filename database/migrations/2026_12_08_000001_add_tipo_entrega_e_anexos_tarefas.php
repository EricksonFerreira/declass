<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modos de entrega da atividade no portal do aluno:
 * 'tipo' diferencia atividade/prova/trabalho; 'tipo_entrega'
 * define o que o aluno envia (leitura, texto ou fotos).
 * Anexos enviados ficam em entrega_tarefa_anexos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->string('tipo', 20)->default('atividade')->after('titulo');
            $table->string('tipo_entrega', 20)->default('nenhuma')->after('nota_maxima');
        });

        Schema::table('entregas_tarefa', function (Blueprint $table) {
            $table->text('resposta')->nullable()->after('status');
        });

        // Foto/arquivo enviado pelo aluno dentro da sua entrega
        Schema::create('entrega_tarefa_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('entrega_id')->constrained('entregas_tarefa')->cascadeOnDelete();
            $table->string('arquivo_path');
            $table->string('nome_original')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'entrega_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrega_tarefa_anexos');

        Schema::table('entregas_tarefa', function (Blueprint $table) {
            $table->dropColumn('resposta');
        });

        Schema::table('tarefas', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'tipo_entrega']);
        });
    }
};
