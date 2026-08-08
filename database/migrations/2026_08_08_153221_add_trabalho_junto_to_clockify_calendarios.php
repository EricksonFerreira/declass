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
        Schema::table('clockify_calendarios', function (Blueprint $table) {
            $table->boolean('trabalho_junto')->default(false)->after('observacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clockify_calendarios', function (Blueprint $table) {
            $table->dropColumn('trabalho_junto');
        });
    }
};
