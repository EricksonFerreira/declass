<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clockify_tags') || Schema::hasColumn('clockify_tags', 'cor')) {
            return;
        }

        Schema::table('clockify_tags', function (Blueprint $table) {
            $table->string('cor', 7)->nullable()->after('nome');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('clockify_tags') || ! Schema::hasColumn('clockify_tags', 'cor')) {
            return;
        }

        Schema::table('clockify_tags', function (Blueprint $table) {
            $table->dropColumn('cor');
        });
    }
};
