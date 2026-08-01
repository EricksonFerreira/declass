<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('criado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('chamado_categoria_id')->constrained('chamado_categorias')->restrictOnDelete();
            $table->foreignId('chamado_subcategoria_id')->nullable()->constrained('chamado_subcategorias')->nullOnDelete();
            $table->foreignId('chamado_status_id')->constrained('chamado_statuses')->restrictOnDelete();
            $table->longText('descricao');
            $table->text('solucao')->nullable();
            $table->dateTime('prazo_atendimento_em')->nullable();
            $table->dateTime('aberto_em')->nullable();
            $table->dateTime('em_atendimento_em')->nullable();
            $table->dateTime('concluido_em')->nullable();
            $table->dateTime('cancelado_em')->nullable();
            $table->foreignId('responsavel_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancelamento_justificativa')->nullable();
            $table->timestamps();
            $table->index(['empresa_id', 'chamado_status_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamados');
    }
};
