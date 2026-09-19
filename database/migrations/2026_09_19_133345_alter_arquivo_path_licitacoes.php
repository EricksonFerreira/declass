<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licitacoes', function (Blueprint $table) {
            $table->text('arquivo_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('licitacoes', function (Blueprint $table) {
            $table->string('arquivo_path', 500)->nullable()->change();
        });
    }
};
