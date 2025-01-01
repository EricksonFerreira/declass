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
        Schema::create('configuracao_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_checklist_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_checklist_id')->constrained()->onDelete('cascade');
            $table->string('ativo')->limit(2)->default('S');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracao_checklists');
    }
};
