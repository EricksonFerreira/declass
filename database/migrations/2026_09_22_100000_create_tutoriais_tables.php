<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutorial_categorias', function (Blueprint $table): void {
            $table->id();
            $table->string('nome')->unique();
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('tutoriais', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tutorial_categoria_id')->nullable()
                ->constrained('tutorial_categorias')->nullOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('video_path')->nullable();
            $table->string('video_url')->nullable();
            $table->string('capa_path')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->string('visibilidade')->default('todos'); // todos | superadmin
            $table->boolean('principal')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['ativo', 'tutorial_categoria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutoriais');
        Schema::dropIfExists('tutorial_categorias');
    }
};
