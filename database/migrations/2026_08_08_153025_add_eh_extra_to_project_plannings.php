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
        Schema::table('clockify_projeto_planejamentos', function (Blueprint $table) {
            $table->boolean('eh_extra')->default(false)->after('horas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clockify_projeto_planejamentos', function (Blueprint $table) {
            $table->dropColumn('eh_extra');
        });
    }
};
