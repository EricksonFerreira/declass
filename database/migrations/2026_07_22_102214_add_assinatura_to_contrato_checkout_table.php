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
        Schema::table('contrato_checkout', function (Blueprint $table) {
            $table->text('assinatura')->nullable()->after('foto_combustivel_path');
            $table->dateTime('data_assinatura')->nullable()->after('assinatura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrato_checkout', function (Blueprint $table) {
            $table->dropColumn(['assinatura', 'data_assinatura']);
        });
    }
};
