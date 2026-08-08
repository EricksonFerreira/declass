<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clockify_calendarios', function (Blueprint $table) {
            $table->unsignedBigInteger('clockify_projeto_cronograma_id')
                  ->nullable()
                  ->after('clockify_projeto_id');

            $table->foreign('clockify_projeto_cronograma_id')
                  ->references('id')
                  ->on('clockify_projeto_cronogramas')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clockify_calendarios', function (Blueprint $table) {
            $table->dropForeign(['clockify_projeto_cronograma_id']);
            $table->dropColumn('clockify_projeto_cronograma_id');
        });
    }
};
