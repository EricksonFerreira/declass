<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materiais')->cascadeOnDelete();
            $table->string('tipo', 10); // entrada|saida|ajuste
            $table->decimal('quantidade', 12, 2);
            $table->decimal('saldo_anterior', 12, 2);
            $table->decimal('saldo_novo', 12, 2);
            $table->text('motivo')->nullable();
            $table->date('data');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();

            $table->index(['material_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_estoque');
    }
};
