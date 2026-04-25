<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->unsignedSmallInteger('limite_usuarios')->nullable()->after('limite_veiculos');
        });

        DB::table('planos')->where('slug', 'gestao')->update(['limite_usuarios' => 1]);
        DB::table('planos')->where('slug', 'essencial')->update(['limite_usuarios' => 3]);
        DB::table('planos')->where('slug', 'crescimento')->update(['limite_usuarios' => 8]);
        // Escala: null = ilimitado
    }

    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->dropColumn('limite_usuarios');
        });
    }
};
