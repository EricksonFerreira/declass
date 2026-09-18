<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clockify_projetos', function (Blueprint $table) {
            $table->string('codigo', 50)->nullable()->after('id')->index();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('jornada_diaria', 4, 1)->default(8.0)->after('custo_hora');
        });
    }

    public function down(): void
    {
        Schema::table('clockify_projetos', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jornada_diaria');
        });
    }
};
