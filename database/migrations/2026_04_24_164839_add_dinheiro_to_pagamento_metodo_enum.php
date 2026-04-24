<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE empresa_plano_pagamentos MODIFY COLUMN metodo ENUM('boleto','cartao','pix','dinheiro','transferencia','manual') NOT NULL DEFAULT 'boleto'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE empresa_plano_pagamentos MODIFY COLUMN metodo ENUM('boleto','cartao','pix','transferencia','manual') NOT NULL DEFAULT 'boleto'");
    }
};
