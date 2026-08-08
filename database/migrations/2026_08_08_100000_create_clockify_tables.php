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

        // 1. Teams (Times)
        Schema::create('clockify_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->string('nome');
            $table->text('descricao')->nullable();
            
            // Audit columns
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
        });

        // 1.1 Team members pivot
        Schema::create('clockify_time_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clockify_time_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('clockify_time_id')->references('id')->on('clockify_times')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. Clients (Clientes)
        Schema::create('clockify_clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->text('endereco')->nullable();
            $table->boolean('ativo')->default(true);

            // Audit columns
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
        });

        // 3. Projects (Projetos)
        Schema::create('clockify_projetos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('clockify_cliente_id')->nullable()->index();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('cor')->nullable(); // Hex color
            $table->boolean('publico')->default(true);
            $table->boolean('ativo')->default(true);

            // Audit columns
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();

            $table->foreign('clockify_cliente_id')->references('id')->on('clockify_clientes')->onDelete('set null');
        });

        // 3.1 Project members (for private projects)
        Schema::create('clockify_projeto_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clockify_projeto_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('clockify_projeto_id')->references('id')->on('clockify_projetos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 4. Tags
        Schema::create('clockify_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->string('nome');
            $table->boolean('ativo')->default(true);

            // Audit columns
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
        });

        // 5. Time Trackers (Registros de tempo)
        Schema::create('clockify_time_trackers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('clockify_projeto_id')->nullable()->index();
            $table->text('descricao')->nullable();
            $table->dateTime('inicio');
            $table->dateTime('fim')->nullable(); // Null means active tracking
            $table->boolean('faturavel')->default(false);

            // Audit columns
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('clockify_projeto_id')->references('id')->on('clockify_projetos')->onDelete('set null');
        });

        // 5.1 Tracker tags pivot
        Schema::create('clockify_tracker_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clockify_time_tracker_id');
            $table->unsignedBigInteger('clockify_tag_id');
            $table->timestamps();

            $table->foreign('clockify_time_tracker_id', 'fk_tracker_tag_tracker')->references('id')->on('clockify_time_trackers')->onDelete('cascade');
            $table->foreign('clockify_tag_id', 'fk_tracker_tag_tag')->references('id')->on('clockify_tags')->onDelete('cascade');
        });

        // 6. Time Off (Folgas/Férias)
        Schema::create('clockify_time_offs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('tipo'); // ferias, medico, pessoal, outro
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->string('status')->default('pendente'); // pendente, aprovado, rejeitado
            $table->text('observacao')->nullable();

            // Audit columns
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 7. Calendars / Allocations (Calendário)
        Schema::create('clockify_calendarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('clockify_projeto_id')->index();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->decimal('horas_por_dia', 4, 2)->nullable();
            $table->text('observacao')->nullable();

            // Audit columns
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('clockify_projeto_id')->references('id')->on('clockify_projetos')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('clockify_calendarios');
        Schema::dropIfExists('clockify_time_offs');
        Schema::dropIfExists('clockify_tracker_tag');
        Schema::dropIfExists('clockify_time_trackers');
        Schema::dropIfExists('clockify_tags');
        Schema::dropIfExists('clockify_projeto_user');
        Schema::dropIfExists('clockify_projetos');
        Schema::dropIfExists('clockify_clientes');
        Schema::dropIfExists('clockify_time_user');
        Schema::dropIfExists('clockify_times');
        Schema::enableForeignKeyConstraints();
    }
};
