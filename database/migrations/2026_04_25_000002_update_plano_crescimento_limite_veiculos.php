<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('planos')
            ->where('slug', 'crescimento')
            ->update(['limite_veiculos' => 40]);
    }

    public function down(): void
    {
        DB::table('planos')
            ->where('slug', 'crescimento')
            ->update(['limite_veiculos' => 80]);
    }
};
