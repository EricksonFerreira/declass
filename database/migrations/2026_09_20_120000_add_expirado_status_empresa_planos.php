<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE empresa_planos MODIFY COLUMN status ENUM('ativo','inadimplente','cancelado','trial','aguardando_pagamento','expirado') NOT NULL DEFAULT 'trial'");
    }

    public function down(): void
    {
        DB::statement("UPDATE empresa_planos SET status = 'cancelado' WHERE status = 'expirado'");
        DB::statement("ALTER TABLE empresa_planos MODIFY COLUMN status ENUM('ativo','inadimplente','cancelado','trial','aguardando_pagamento') NOT NULL DEFAULT 'trial'");
    }
};
