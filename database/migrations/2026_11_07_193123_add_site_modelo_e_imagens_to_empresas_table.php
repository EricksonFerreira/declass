<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('site_modelo', 30)->default('classico');
            $table->string('site_banner')->nullable()->after('site_modelo');
            $table->string('site_imagem_sobre')->nullable()->after('site_banner');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['site_modelo', 'site_banner', 'site_imagem_sobre']);
        });
    }
};
