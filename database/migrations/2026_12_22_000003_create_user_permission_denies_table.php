<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Concessoes diretas ja sao cobertas por model_has_permissions
        // (spatie); esta tabela guarda as NEGACOES diretas — remover
        // de um usuario especifico algo que o perfil dele concede.
        Schema::create('user_permission_denies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('permission_id')->index();
            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permission_denies');
    }
};
