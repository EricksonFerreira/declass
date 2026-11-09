<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aviso_turma', function (Blueprint $table) {
            $table->foreignId('aviso_id')->constrained('avisos')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->primary(['aviso_id', 'turma_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aviso_turma');
    }
};
