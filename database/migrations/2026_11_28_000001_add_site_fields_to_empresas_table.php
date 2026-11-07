<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos do site publico da empresa ("rede de escolas"):
 * branding (tagline/descricao/whatsapp/instagram), ligar/desligar
 * o site e cada modulo exibido nele.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('site_titulo')->nullable()->after('cor_terciaria');
            $table->text('site_descricao')->nullable()->after('site_titulo');
            $table->string('site_whatsapp', 30)->nullable()->after('site_descricao');
            $table->string('site_instagram')->nullable()->after('site_whatsapp');
            $table->char('site_ativo', 1)->default('N')->after('site_instagram');
            $table->char('site_unidades', 1)->default('S')->after('site_ativo');
            $table->char('site_matricula', 1)->default('S')->after('site_unidades');
            $table->char('site_contato', 1)->default('S')->after('site_matricula');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'site_titulo', 'site_descricao', 'site_whatsapp', 'site_instagram',
                'site_ativo', 'site_unidades', 'site_matricula', 'site_contato',
            ]);
        });
    }
};
