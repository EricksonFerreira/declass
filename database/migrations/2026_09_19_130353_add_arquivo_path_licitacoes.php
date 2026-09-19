<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licitacoes', function (Blueprint $table) {
            $table->string('arquivo_path', 500)->nullable()->after('url_edital');
        });
    }

    public function down(): void
    {
        Schema::table('licitacoes', function (Blueprint $table) {
            $table->dropColumn('arquivo_path');
        });
    }
};
