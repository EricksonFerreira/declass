<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('marcas')) {
            Schema::table('marcas', function (Blueprint $table) {
                if (!Schema::hasColumn('marcas', 'vehicle_type')) {
                    $table->string('vehicle_type', 20)->default('cars')->after('codigo_fipe');
                    $table->index('vehicle_type');
                }
            });

            DB::table('marcas')
                ->whereNull('vehicle_type')
                ->orWhere('vehicle_type', '')
                ->update(['vehicle_type' => 'cars']);
        }

        if (Schema::hasTable('modelos')) {
            Schema::table('modelos', function (Blueprint $table) {
                if (!Schema::hasColumn('modelos', 'vehicle_type')) {
                    $table->string('vehicle_type', 20)->default('cars')->after('codigo_fipe');
                    $table->index('vehicle_type');
                }
            });

            DB::table('modelos')
                ->whereNull('vehicle_type')
                ->orWhere('vehicle_type', '')
                ->update(['vehicle_type' => 'cars']);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('modelos') && Schema::hasColumn('modelos', 'vehicle_type')) {
            Schema::table('modelos', function (Blueprint $table) {
                $table->dropIndex(['vehicle_type']);
                $table->dropColumn('vehicle_type');
            });
        }

        if (Schema::hasTable('marcas') && Schema::hasColumn('marcas', 'vehicle_type')) {
            Schema::table('marcas', function (Blueprint $table) {
                $table->dropIndex(['vehicle_type']);
                $table->dropColumn('vehicle_type');
            });
        }

        Schema::enableForeignKeyConstraints();
    }
};
