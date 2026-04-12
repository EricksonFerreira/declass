<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('dias_uteis')
            ->whereBetween('dia', [1, 7])
            ->update([
                'dia' => DB::raw('dia - 1'),
            ]);
    }

    public function down(): void
    {
        DB::table('dias_uteis')
            ->whereBetween('dia', [0, 6])
            ->update([
                'dia' => DB::raw('dia + 1'),
            ]);
    }
};
