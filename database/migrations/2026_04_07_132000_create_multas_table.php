<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('multas')) {
            return;
        }

        Schema::create('multas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->unsignedBigInteger('reserva_id')->nullable()->index();
            $table->unsignedBigInteger('veiculo_id')->nullable()->index();
            $table->unsignedBigInteger('cliente_id')->nullable()->index()
                ->comment('user_id do locatario');
            $table->unsignedBigInteger('condutor_id')->nullable()
                ->comment('user_id do condutor responsavel, se diferente do locatario');

            // Identificacao da infracao
            $table->string('placa_momento', 20)->nullable()
                ->comment('Placa no momento da infracao');
            $table->string('numero_auto', 100)->nullable()
                ->comment('Numero/auto da infracao');
            $table->string('orgao_autuador', 150)->nullable();
            $table->datetime('data_hora_infracao')->nullable();
            $table->string('local_infracao', 500)->nullable();
            $table->text('descricao')->nullable();

            // Valores
            $table->decimal('valor_original', 10, 2)->default(0);
            $table->decimal('valor_desconto', 10, 2)->nullable();
            $table->decimal('valor_final', 10, 2)->default(0);
            $table->unsignedTinyInteger('pontos')->nullable();
            $table->date('data_vencimento')->nullable();

            // Status
            $table->string('status_multa', 50)->default('pendente')
                ->comment('pendente|em_analise|aguardando_identificacao_condutor|recorrida|deferida|indeferida|paga|cancelada');
            $table->string('status_desconto', 50)->default('nao_aplicado')
                ->comment('nao_aplicado|pendente|descontado|nao_descontar|estornado');

            // Responsavel e documentos
            $table->unsignedBigInteger('responsavel_lancamento_id')->nullable();
            $table->text('observacao')->nullable();
            $table->string('anexo_comprovante_path', 500)->nullable();
            $table->string('anexo_pdf_path', 500)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('multa_historico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('multa_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('campo_alterado', 100)->nullable();
            $table->string('valor_anterior', 500)->nullable();
            $table->string('valor_novo', 500)->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('multa_historico');
        Schema::dropIfExists('multas');
        Schema::enableForeignKeyConstraints();
    }
};
