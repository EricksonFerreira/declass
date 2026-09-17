<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funcoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->string('nome');
            $table->decimal('custo_hora', 10, 2)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'nome']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('funcao_id')->nullable()->after('cargo')->index();
            $table->decimal('custo_hora', 10, 2)->nullable()->after('funcao_id');
            $table->foreign('funcao_id')->references('id')->on('funcoes')->onDelete('set null');
        });

        Schema::table('clockify_projetos', function (Blueprint $table) {
            $table->string('status', 20)->default('em_andamento')->after('ativo')->index();
        });

        Schema::create('clockify_projeto_estimativas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('clockify_projeto_id');
            $table->unsignedBigInteger('funcao_id');
            $table->decimal('horas_estimadas', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['clockify_projeto_id', 'funcao_id'], 'projeto_estimativas_projeto_funcao_unique');
            $table->foreign('clockify_projeto_id', 'projeto_estimativas_projeto_fk')->references('id')->on('clockify_projetos')->onDelete('cascade');
            $table->foreign('funcao_id', 'projeto_estimativas_funcao_fk')->references('id')->on('funcoes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clockify_projeto_estimativas');

        Schema::table('clockify_projetos', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['funcao_id']);
            $table->dropColumn(['funcao_id', 'custo_hora']);
        });

        Schema::dropIfExists('funcoes');
    }
};
