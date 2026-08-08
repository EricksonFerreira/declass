<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clockify_projetos', function (Blueprint $table) {
            $table->decimal('valor_faturado', 12, 2)->nullable()->default(0.00)->after('valor_hora');
        });

        Schema::table('clockify_projeto_cronogramas', function (Blueprint $table) {
            $table->decimal('valor_faturado', 12, 2)->nullable()->default(0.00)->after('valor_hora');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clockify_projetos', function (Blueprint $table) {
            $table->dropColumn('valor_faturado');
        });

        Schema::table('clockify_projeto_cronogramas', function (Blueprint $table) {
            $table->dropColumn('valor_faturado');
        });
    }
};
