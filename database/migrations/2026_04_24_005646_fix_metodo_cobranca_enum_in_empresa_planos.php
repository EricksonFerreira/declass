<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE empresa_planos MODIFY COLUMN metodo_cobranca ENUM('boleto','pix','automatico','manual') NOT NULL DEFAULT 'manual'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE empresa_planos MODIFY COLUMN metodo_cobranca ENUM('boleto','automatico','manual') NOT NULL DEFAULT 'manual'");
    }
};
