<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tour_telas')) return;

        Schema::create('tour_telas', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique(); // ex: "estacao-index", "estacao-form"
            $table->string('nome_exibicao');   // ex: "Lista de Estações"
            $table->timestamps();
        });

        // Registra as telas iniciais
        \Illuminate\Support\Facades\DB::table('tour_telas')->insert([
            ['chave' => 'estacao-index', 'nome_exibicao' => 'Lista de Estações',      'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'estacao-form',  'nome_exibicao' => 'Cadastro de Estação',    'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_telas');
    }
};
