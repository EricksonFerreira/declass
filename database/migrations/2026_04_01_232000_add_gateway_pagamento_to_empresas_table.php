<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        if (!Schema::hasColumn('empresas', 'gateway_pagamento')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->string('gateway_pagamento', 40)->default('stripe')->after('dominio');
            });
        }

        DB::table('empresas')
            ->whereNull('gateway_pagamento')
            ->update(['gateway_pagamento' => 'stripe']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('empresas') || !Schema::hasColumn('empresas', 'gateway_pagamento')) {
            return;
        }

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('gateway_pagamento');
        });
    }
};
