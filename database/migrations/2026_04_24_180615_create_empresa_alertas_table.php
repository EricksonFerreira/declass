<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('empresa_alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('tipo', 60)->comment('ex: veiculos_excedentes, modulo_indisponivel, etc');
            $table->string('nivel', 20)->default('aviso')->comment('aviso, critico, info');
            $table->string('titulo', 200);
            $table->text('mensagem');
            $table->json('metadados')->nullable()->comment('dados extras para contexto');
            $table->boolean('resolvido')->default(false);
            $table->timestamp('resolvido_em')->nullable();
            $table->foreignId('resolvido_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['empresa_id', 'resolvido']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_alertas');
    }
};
