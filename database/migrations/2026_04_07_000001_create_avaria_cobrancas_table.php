<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (!Schema::hasTable('avaria_cobrancas')) {
            Schema::create('avaria_cobrancas', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->unsignedBigInteger('reserva_id')->index();
                $table->unsignedBigInteger('veiculo_id')->nullable()->index();
                $table->unsignedBigInteger('cliente_id')->nullable()->index();
                $table->unsignedBigInteger('calcao_id')->nullable()->index();

                $table->string('metodo', 30)->default('manual'); // calcao_capture | stripe_charge | manual
                $table->string('status', 30)->default('pendente'); // pendente | processando | cobrado | falhou | cancelado

                $table->string('moeda', 10)->default('BRL');
                $table->decimal('valor', 10, 2)->default(0);

                $table->text('descricao')->nullable();
                $table->json('dano_veiculo_ids')->nullable();

                $table->string('gateway_payment_intent_id')->nullable();
                $table->json('gateway_response')->nullable();

                $table->unsignedBigInteger('usuario_responsavel_id')->nullable()->index();
                $table->dateTime('cobrado_em')->nullable();

                $table->timestamps();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('avaria_cobrancas');
        Schema::enableForeignKeyConstraints();
    }
};

