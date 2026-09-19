<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_ia_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->unique()->constrained('empresas')->cascadeOnDelete();
            // Provedor padrão: deepseek | openai (extensível no IaService::PROVEDORES)
            $table->string('provedor_padrao', 20)->default('deepseek');
            $table->unsignedInteger('tokens_limite')->default(4000);
            // LGPD: anonimiza PII antes de enviar à IA
            $table->char('anonimizar', 1)->default('N');
            $table->string('tamanho_resumo', 12)->default('medio'); // curto | medio | detalhado
            $table->text('instrucoes_extras')->nullable();
            // Chaves por provedor — cast 'encrypted' no model
            $table->text('deepseek_key')->nullable();
            $table->string('deepseek_model', 60)->nullable();
            $table->text('openai_key')->nullable();
            $table->string('openai_model', 60)->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->unsignedBigInteger('usualt')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->timestamp('dataalt')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_ia_configs');
    }
};
