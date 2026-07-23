<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('os_subcategoria_campos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_subcategoria_id')->constrained('os_subcategorias')->cascadeOnDelete();
            $table->string('nome');
            $table->string('tipo'); // text, number, date, select, checkbox
            $table->boolean('is_required')->default(false);
            $table->json('opcoes')->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('ordem_servico_campo_valores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->cascadeOnDelete();
            $table->foreignId('os_subcategoria_campo_id')->constrained('os_subcategoria_campos')->cascadeOnDelete();
            $table->text('valor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordem_servico_campo_valores');
        Schema::dropIfExists('os_subcategoria_campos');
    }
};
