<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas') || !Schema::hasColumn('empresas', 'stripe_secret')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE empresas MODIFY stripe_secret TEXT NULL');
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('empresas') || !Schema::hasColumn('empresas', 'stripe_secret')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE empresas MODIFY stripe_secret text NULL');
        }
        Schema::enableForeignKeyConstraints();
    }
};
