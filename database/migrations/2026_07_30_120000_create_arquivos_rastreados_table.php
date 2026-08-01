<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arquivos_rastreados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modulo', 80)->index();
            $table->string('entidade', 120)->nullable()->index();
            $table->unsignedBigInteger('entidade_id')->nullable()->index();
            $table->string('campo', 120)->nullable()->index();
            $table->string('contexto', 120)->nullable()->index();
            $table->string('disk', 50)->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('hash_sha1', 40)->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['modulo', 'entidade', 'entidade_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arquivos_rastreados');
    }
};
