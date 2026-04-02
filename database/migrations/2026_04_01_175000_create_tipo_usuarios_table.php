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
        if (!Schema::hasTable('tipo_usuarios')) {
            Schema::create('tipo_usuarios', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name')->default('web');
                $table->string('ativo', 1)->default('S');
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_usuarios');
    }
};
