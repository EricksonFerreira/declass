<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant da plataforma SaaS escolar (cliente independente).
 * Equivale ao nivel "Tenant" da arquitetura: uma empresa cliente
 * que pode ter uma organizacao/rede e varias escolas/unidades.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->string('dominio')->nullable()->unique();
            $table->string('cnpj', 18)->nullable();
            $table->string('email')->nullable();
            $table->string('telefone', 30)->nullable();
            $table->text('endereco')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('cor_primaria', 7)->nullable();
            $table->string('cor_secundaria', 7)->nullable();
            $table->string('cor_terciaria', 7)->nullable();
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
