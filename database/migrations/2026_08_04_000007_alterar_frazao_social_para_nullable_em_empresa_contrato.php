<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterarRazaoSocialParaNullableEmEmpresaContratos extends Migration
{
    public function up()
    {
        Schema::table('empresa_contratos', function (Blueprint $table) {
            $table->string('razao_social')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('empresa_contratos', function (Blueprint $table) {
            $table->string('razao_social')->nullable(false)->change();
        });
    }
}