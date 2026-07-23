<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('os_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('os_subcategorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_categoria_id')->constrained('os_categorias')->cascadeOnDelete();
            $table->string('nome');
            $table->boolean('show_sinistro')->default(false);
            $table->boolean('show_reserva')->default(false);
            $table->boolean('show_avarias')->default(false);
            $table->boolean('show_checklist_prevencao')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->foreignId('os_categoria_id')->nullable()->constrained('os_categorias')->nullOnDelete();
            $table->foreignId('os_subcategoria_id')->nullable()->constrained('os_subcategorias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropForeign(['os_subcategoria_id']);
            $table->dropColumn('os_subcategoria_id');
            $table->dropForeign(['os_categoria_id']);
            $table->dropColumn('os_categoria_id');
        });

        Schema::dropIfExists('os_subcategorias');
        Schema::dropIfExists('os_categorias');
    }
};
