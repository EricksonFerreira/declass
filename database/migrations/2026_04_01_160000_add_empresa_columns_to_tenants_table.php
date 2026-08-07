<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas')) {
            Schema::create('empresas', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->string('slug')->unique();
                $table->string('ativo', 1)->default('S');
                $table->timestamps();
    });
        }

        if (!Schema::hasTable('empresas')) { Schema::enableForeignKeyConstraints(); return; }
        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'cnpj')) {
                $table->string('cnpj', 20)->nullable()->after('nome');
            }
            if (!Schema::hasColumn('empresas', 'email')) {
                $table->string('email')->nullable()->after('cnpj');
            }
            if (!Schema::hasColumn('empresas', 'telefone')) {
                $table->string('telefone', 25)->nullable()->after('email');
            }
            if (!Schema::hasColumn('empresas', 'dominio')) {
                $table->string('dominio')->nullable()->after('telefone');
            }
        });
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            if (Schema::hasColumn('empresas', 'dominio')) {
                $table->dropColumn('dominio');
            }
            if (Schema::hasColumn('empresas', 'telefone')) {
                $table->dropColumn('telefone');
            }
            if (Schema::hasColumn('empresas', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('empresas', 'cnpj')) {
                $table->dropColumn('cnpj');
            }
        });
        Schema::enableForeignKeyConstraints();
        Schema::enableForeignKeyConstraints();
    }
};
