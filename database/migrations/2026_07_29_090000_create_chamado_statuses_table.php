<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamado_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome');
            $table->string('slug');
            $table->string('cor', 20)->default('secondary');
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->boolean('padrao_entrada')->default(false);
            $table->boolean('padrao_atendimento')->default(false);
            $table->boolean('padrao_fechamento')->default(false);
            $table->timestamps();
            $table->index(['empresa_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamado_statuses');
    }
};
