<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('empresas')) {
            Schema::table('empresas', function (Blueprint $table) {
                if (!Schema::hasColumn('empresas', 'certificado_path')) {
                    $table->string('certificado_path')->nullable()->after('regime_emissao');
                }
                if (!Schema::hasColumn('empresas', 'certificado_senha')) {
                    $table->text('certificado_senha')->nullable()->after('certificado_path');
                }
                if (!Schema::hasColumn('empresas', 'fiscal_token')) {
                    $table->string('fiscal_token')->nullable()->after('certificado_senha');
                }
                if (!Schema::hasColumn('empresas', 'fiscal_client_id')) {
                    $table->string('fiscal_client_id')->nullable()->after('fiscal_token');
                }
            });
        }

        if (Schema::hasTable('opcionals')) {
            Schema::table('opcionals', function (Blueprint $table) {
                if (!Schema::hasColumn('opcionals', 'codigo_servico_municipal')) {
                    $table->string('codigo_servico_municipal', 20)->nullable()->after('nome');
                }
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('empresas')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->dropColumn(['certificado_path', 'certificado_senha', 'fiscal_token', 'fiscal_client_id']);
            });
        }

        if (Schema::hasTable('opcionals')) {
            Schema::table('opcionals', function (Blueprint $table) {
                $table->dropColumn('codigo_servico_municipal');
            });
        }

        Schema::enableForeignKeyConstraints();
    }
};
