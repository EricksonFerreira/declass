<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            $table->string('destino', 20)->default('familias')->after('tipo');
            $table->index(['empresa_id', 'destino']);
        });
    }

    public function down(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            $table->dropIndex(['empresa_id', 'destino']);
            $table->dropColumn('destino');
        });
    }
};
