<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livros', function (Blueprint $table) {
            // Estante/prateleira do livro na biblioteca (ex.: "Estante A3").
            $table->string('localizacao', 60)->nullable()->after('descricao');
            // Caminho da foto de capa no disk public.
            $table->string('capa_path')->nullable()->after('localizacao');
        });
    }

    public function down(): void
    {
        Schema::table('livros', function (Blueprint $table) {
            $table->dropColumn(['localizacao', 'capa_path']);
        });
    }
};
