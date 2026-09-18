<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clockify_times', function (Blueprint $table) {
            if (! Schema::hasColumn('clockify_times', 'lider_id')) {
                $table->unsignedBigInteger('lider_id')->nullable()->after('descricao');
                $table->foreign('lider_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::table('clockify_projetos', function (Blueprint $table) {
            if (! Schema::hasColumn('clockify_projetos', 'responsavel_id')) {
                $table->unsignedBigInteger('responsavel_id')->nullable()->after('clockify_cliente_id');
                $table->foreign('responsavel_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clockify_times', function (Blueprint $table) {
            if (Schema::hasColumn('clockify_times', 'lider_id')) {
                $table->dropForeign(['lider_id']);
                $table->dropColumn('lider_id');
            }
        });

        Schema::table('clockify_projetos', function (Blueprint $table) {
            if (Schema::hasColumn('clockify_projetos', 'responsavel_id')) {
                $table->dropForeign(['responsavel_id']);
                $table->dropColumn('responsavel_id');
            }
        });
    }
};
