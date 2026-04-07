<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'saldo_atual')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('saldo_atual', 12, 2)->default(0)->after('foto_perfil');
    });
        }

        if (!Schema::hasTable('saldo_movimentacoes')) {
            Schema::create('saldo_movimentacoes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->unsignedBigInteger('reserva_id')->nullable();
                $table->string('tipo', 20);
                $table->string('origem', 60)->nullable();
                $table->decimal('valor', 12, 2);
                $table->decimal('saldo_anterior', 12, 2)->default(0);
                $table->decimal('saldo_atual', 12, 2)->default(0);
                $table->text('observacoes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'empresa_id']);
                $table->index(['reserva_id']);
            });
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('saldo_movimentacoes')) {
            Schema::dropIfExists('saldo_movimentacoes');
    }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'saldo_atual')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('saldo_atual');
            });
        }
        Schema::enableForeignKeyConstraints();
    }
};
