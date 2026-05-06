<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('termo_consentimento', function (Blueprint $table) {
            if (!Schema::hasColumn('termo_consentimento', 'ordem')) {
                $table->integer('ordem')->default(0)->after('id');
            }
            if (!Schema::hasColumn('termo_consentimento', 'titulo')) {
                $table->string('titulo')->nullable()->after('ordem');
            }
            if (!Schema::hasColumn('termo_consentimento', 'tipo')) {
                $table->string('tipo', 20)->default('checkin')->after('texto');
                $table->index('tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('termo_consentimento', function (Blueprint $table) {
            $table->dropIndex(['tipo']);
            $table->dropColumn(['ordem', 'titulo', 'tipo']);
        });
    }
};
