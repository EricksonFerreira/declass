<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tipos de requerimento configurados pela secretaria (declaracao,
 * transferencia, 2a via...): cada tipo define se exige anexo e quais
 * campos extras a familia preenche no portal. As respostas ficam em
 * solicitacoes.dados e os arquivos em solicitacao_anexos.
 */
return new class extends Migration
{
    private function audit(Blueprint $table): void
    {
        $table->unsignedBigInteger('usuinc')->nullable();
        $table->timestamp('datainc')->nullable();
        $table->unsignedBigInteger('usuatu')->nullable();
        $table->timestamp('datatu')->nullable();
        $table->timestamps();
        $table->softDeletes();
    }

    public function up(): void
    {
        Schema::create('solicitacao_tipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->nullable()->constrained('escolas')->cascadeOnDelete();
            $table->string('nome', 120);
            $table->text('descricao')->nullable();
            $table->boolean('exige_anexo')->default(false);
            $table->json('campos')->nullable();
            $table->boolean('ativo')->default(true);
            $this->audit($table);

            $table->index(['empresa_id', 'ativo']);
        });

        Schema::table('solicitacoes', function (Blueprint $table) {
            $table->foreignId('tipo_solicitacao_id')->nullable()->after('tipo')
                ->constrained('solicitacao_tipos')->nullOnDelete();
            $table->json('dados')->nullable()->after('descricao');
        });

        Schema::create('solicitacao_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('solicitacao_id')->constrained('solicitacoes')->cascadeOnDelete();
            $table->string('arquivo_path');
            $table->string('nome_original')->nullable();
            $this->audit($table);

            $table->index(['solicitacao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacao_anexos');

        Schema::table('solicitacoes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_solicitacao_id');
            $table->dropColumn('dados');
        });

        Schema::dropIfExists('solicitacao_tipos');
    }
};
