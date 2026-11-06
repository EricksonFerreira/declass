<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensalidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->foreignId('plano_mensalidade_id')->nullable()->constrained('planos_mensalidade')->nullOnDelete();
            $table->char('competencia', 7); // YYYY-MM
            $table->decimal('valor', 10, 2);
            $table->date('vencimento');
            $table->string('status', 20)->default('pendente'); // pendente|paga|cancelada
            $table->date('data_pagamento')->nullable();
            $table->string('forma_pagamento', 40)->nullable();
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['matricula_id', 'competencia'], 'mensalidades_matricula_competencia_unique');
            $table->index(['empresa_id', 'status', 'vencimento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensalidades');
    }
};
