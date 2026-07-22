<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->unsignedBigInteger('sinistro_id')->nullable()->after('reserva_id');
            
            $table->foreign('sinistro_id')->references('id')->on('sinistros')->nullOnDelete();
            $table->index('sinistro_id');
        });
    }

    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropForeign(['sinistro_id']);
            $table->dropColumn('sinistro_id');
        });
    }
};
