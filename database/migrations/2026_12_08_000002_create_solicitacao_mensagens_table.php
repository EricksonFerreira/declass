<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Conversa dentro de uma solicitacao: a secretaria pode pedir
 * esclarecimentos/documentos e a familia responde pelo portal,
 * mantendo o historico ate o atendimento final.
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
        Schema::create('solicitacao_mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('solicitacao_id')->constrained('solicitacoes')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('mensagem');
            $this->audit($table);

            $table->index(['solicitacao_id']);
        });

        // Anexo enviado junto de uma resposta na conversa (ex.: documento pedido pela secretaria)
        Schema::table('solicitacao_anexos', function (Blueprint $table) {
            $table->foreignId('mensagem_id')->nullable()->after('solicitacao_id')
                ->constrained('solicitacao_mensagens')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('solicitacao_anexos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mensagem_id');
        });

        Schema::dropIfExists('solicitacao_mensagens');
    }
};
