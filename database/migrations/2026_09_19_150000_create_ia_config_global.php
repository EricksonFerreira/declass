<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Linha única (id=1): configuração de IA da plataforma — padrão
        // para empresas sem config própria e destino do "Configurado".
        Schema::create('ia_config_global', function (Blueprint $table) {
            $table->id();
            $table->string('provedor', 20)->default('deepseek');
            $table->text('api_key')->nullable(); // encrypted no model
            $table->string('modelo', 60)->nullable();
            $table->char('assistente_ativo', 1)->default('N');
            // Transcrição de áudio (provedor separado, ex.: Groq/OpenAI Whisper)
            $table->string('transcricao_provedor', 20)->nullable(); // null = usa o principal
            $table->text('transcricao_key')->nullable(); // encrypted
            $table->string('transcricao_modelo', 60)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_config_global');
    }
};
