<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            if (!Schema::hasColumn('horarios', 'funciona')) {
                $table->char('funciona', 1)->default('S')->after('funciona_24h');
            }

            if (!Schema::hasColumn('horarios', 'tem_intervalo_almoco')) {
                $table->char('tem_intervalo_almoco', 1)->default('S')->after('hora_fim_almoco');
            }
        });
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            if (Schema::hasColumn('horarios', 'tem_intervalo_almoco')) {
                $table->dropColumn('tem_intervalo_almoco');
            }

            if (Schema::hasColumn('horarios', 'funciona')) {
                $table->dropColumn('funciona');
            }
        });
    }
};
