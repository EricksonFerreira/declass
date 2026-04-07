<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (!Schema::hasTable('sinistros')) {
            Schema::create('sinistros', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
                $table->unsignedBigInteger('veiculo_id')->nullable()->index();
                $table->unsignedBigInteger('reserva_id')->nullable()->index();
                $table->unsignedBigInteger('cliente_id')->nullable()->index();
                $table->unsignedBigInteger('condutor_id')->nullable();
                $table->unsignedBigInteger('responsavel_id')->nullable();

                // Dados do ocorrido
                $table->string('tipo_sinistro', 50)->default('colisao'); // colisao, furto_roubo, incendio, vandalismo, alagamento, outros
                $table->datetime('data_hora_ocorrido')->nullable();
                $table->string('local_ocorrido', 255)->nullable();
                $table->text('descricao')->nullable();
                $table->string('placa_momento', 20)->nullable();

                // Boletim de Ocorrência
                $table->string('numero_bo', 100)->nullable();
                $table->string('orgao_bo', 100)->nullable(); // Delegacia, PRF, PM, etc.

                // Terceiros
                $table->boolean('terceiros_envolvidos')->default(false);
                $table->string('terceiro_nome', 150)->nullable();
                $table->string('terceiro_telefone', 30)->nullable();
                $table->string('terceiro_placa', 20)->nullable();
                $table->boolean('vitimas')->default(false);

                // Seguro
                $table->string('seguradora', 150)->nullable();
                $table->string('numero_apolice', 100)->nullable();
                $table->string('numero_sinistro_seguradora', 100)->nullable();
                $table->decimal('franquia', 10, 2)->nullable();

                // Valores
                $table->decimal('valor_prejuizo_estimado', 10, 2)->nullable();
                $table->decimal('valor_aprovado_seguradora', 10, 2)->nullable();
                $table->decimal('valor_responsabilidade_cliente', 10, 2)->nullable();

                // Status
                $table->string('status', 50)->default('aberto'); // aberto, em_analise, aguardando_documentos, aprovado, indenizado, encerrado, cancelado

                $table->text('observacoes')->nullable();

                // Anexos
                $table->string('anexo_bo_path', 500)->nullable();
                $table->string('anexo_foto1_path', 500)->nullable();
                $table->string('anexo_foto2_path', 500)->nullable();
                $table->string('anexo_foto3_path', 500)->nullable();
                $table->string('anexo_laudo_path', 500)->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('sinistro_historico')) {
            Schema::create('sinistro_historico', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sinistro_id')->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('campo_alterado', 100)->nullable();
                $table->text('valor_anterior')->nullable();
                $table->text('valor_novo')->nullable();
                $table->text('descricao')->nullable();
                $table->timestamps();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sinistro_historico');
        Schema::dropIfExists('sinistros');
        Schema::enableForeignKeyConstraints();
    }
};
