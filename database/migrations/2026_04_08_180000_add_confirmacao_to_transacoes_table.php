<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transacoes', function (Blueprint $table) {
            $table->timestamp('confirmado_em')->nullable()->after('status');
            $table->unsignedBigInteger('confirmado_por_user_id')->nullable()->after('confirmado_em');
            $table->foreign('confirmado_por_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transacoes', function (Blueprint $table) {
            $table->dropForeign(['confirmado_por_user_id']);
            $table->dropColumn(['confirmado_em', 'confirmado_por_user_id']);
        });
    }
};
