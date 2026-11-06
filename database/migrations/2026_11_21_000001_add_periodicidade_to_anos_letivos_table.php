<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Periodicidade do ano letivo (bimestral, trimestral, semestral,
 * anual ou personalizado): define quantos periodos letivos sao
 * gerados automaticamente na criacao do ano.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anos_letivos', function (Blueprint $table) {
            $table->string('periodicidade', 20)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('anos_letivos', function (Blueprint $table) {
            $table->dropColumn('periodicidade');
        });
    }
};
