<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamado_subcategorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreignId('chamado_categoria_id')->constrained('chamado_categorias')->cascadeOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->unsignedInteger('prazo_horas')->default(24);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->index(['empresa_id', 'chamado_categoria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamado_subcategorias');
    }
};
