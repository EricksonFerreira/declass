<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categorias_financeiras')) {
            Schema::create('categorias_financeiras', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('cascade');
                $table->string('nome');
                $table->enum('tipo', ['receita', 'despesa']);
                $table->string('descricao')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_financeiras');
    }
};
