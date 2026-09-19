<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('licitacoes_ativo', 1)->default('N')->after('ativo');
        });

        Schema::create('licitacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('orgao');
            $table->text('objeto');
            $table->string('numero_edital')->nullable();
            $table->string('modalidade')->nullable();
            $table->string('criterio_julgamento')->nullable();
            $table->date('data_abertura')->nullable();
            $table->decimal('valor_estimado', 14, 2)->nullable();
            $table->string('url_edital')->nullable();
            $table->longText('texto_edital')->nullable();
            $table->string('status')->default('cadastrada'); // cadastrada, analisada, descartada, participando, vencida, perdida
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'status']);
            $table->index(['empresa_id', 'data_abertura']);
        });

        Schema::create('licitacao_analises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('licitacao_id')->constrained('licitacoes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modelo')->nullable();
            $table->decimal('nota_tecnica_estimada', 5, 2)->nullable();
            $table->string('viabilidade')->nullable(); // viavel, atencao, inviavel
            $table->text('parecer')->nullable();
            $table->json('criterios')->nullable();
            $table->json('exigencias')->nullable();
            $table->json('gaps')->nullable();
            $table->decimal('preco_sugerido', 14, 2)->nullable();
            $table->unsignedInteger('tokens_entrada')->nullable();
            $table->unsignedInteger('tokens_saida')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licitacao_analises');
        Schema::dropIfExists('licitacoes');
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('licitacoes_ativo');
        });
    }
};
