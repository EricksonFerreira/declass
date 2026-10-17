<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clockify_tags') && ! Schema::hasColumn('clockify_tags', 'clockify_projeto_id')) {
            Schema::table('clockify_tags', function (Blueprint $table) {
                $table->unsignedBigInteger('clockify_projeto_id')->nullable()->after('empresa_id')->index();
                $table->foreign('clockify_projeto_id', 'fk_tags_projeto')
                    ->references('id')->on('clockify_projetos')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('empresas') && ! Schema::hasColumn('empresas', 'tags_por_projeto')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->boolean('tags_por_projeto')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clockify_tags') && Schema::hasColumn('clockify_tags', 'clockify_projeto_id')) {
            Schema::table('clockify_tags', function (Blueprint $table) {
                $table->dropForeign('fk_tags_projeto');
                $table->dropIndex(['clockify_projeto_id']);
                $table->dropColumn('clockify_projeto_id');
            });
        }

        if (Schema::hasTable('empresas') && Schema::hasColumn('empresas', 'tags_por_projeto')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->dropColumn('tags_por_projeto');
            });
        }
    }
};
