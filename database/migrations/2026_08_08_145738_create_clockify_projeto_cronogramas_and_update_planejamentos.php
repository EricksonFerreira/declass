<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create parent schedules table (Cronogramas)
        Schema::create('clockify_projeto_cronogramas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clockify_projeto_id');
            $table->string('nome');
            $table->string('status')->default('valido'); // valido, invalido, rascunho
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->decimal('horas_orcadas', 8, 2)->nullable();
            $table->decimal('valor_hora', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('clockify_projeto_id')->references('id')->on('clockify_projetos')->onDelete('cascade');
        });

        // Clear existing plannings to avoid integrity conflicts
        Schema::disableForeignKeyConstraints();
        DB::table('clockify_projeto_planejamentos')->truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Adjust daily planning table
        Schema::table('clockify_projeto_planejamentos', function (Blueprint $table) {
            $table->dropForeign('clockify_projeto_planejamentos_clockify_projeto_id_foreign');
            $table->dropUnique('clockify_projeto_planejamentos_clockify_projeto_id_data_unique');
            $table->dropColumn('clockify_projeto_id');
            
            $table->unsignedBigInteger('clockify_projeto_cronograma_id')->after('id');
            $table->foreign('clockify_projeto_cronograma_id', 'fk_planejamentos_cronograma')
                ->references('id')
                ->on('clockify_projeto_cronogramas')
                ->onDelete('cascade');
            $table->unique(['clockify_projeto_cronograma_id', 'data'], 'uniq_cronograma_data');
        });
    }

    public function down(): void
    {
        Schema::table('clockify_projeto_planejamentos', function (Blueprint $table) {
            $table->dropForeign('fk_planejamentos_cronograma');
            $table->dropUnique('uniq_cronograma_data');
            $table->dropColumn('clockify_projeto_cronograma_id');

            $table->unsignedBigInteger('clockify_projeto_id')->after('id');
            $table->foreign('clockify_projeto_id')->references('id')->on('clockify_projetos')->onDelete('cascade');
            $table->unique(['clockify_projeto_id', 'data']);
        });

        Schema::dropIfExists('clockify_projeto_cronogramas');
    }
};
