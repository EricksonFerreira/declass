<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('item_checklist')) { return; }
        Schema::create('item_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('ativo')->limit(2)->default('S');
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('item_checklists');
        Schema::enableForeignKeyConstraints();
    }
};
