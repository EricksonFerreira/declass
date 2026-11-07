<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Horarios nomeados por escola+turno: um turno pode ter varias
 * grades de horario (ex.: "Matutino Regular", "Matutino Sabado").
 * Slots passam a pertencer a um horario e a turma se vincula
 * a um horario especifico. Backfill cria um horario por
 * (escola, turno) existente e vincula turmas pelo turno.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->string('turno', 20);
            $table->string('nome', 120);
            $table->string('ativo', 1)->default('S');
            $table->unsignedBigInteger('usuinc')->nullable();
            $table->timestamp('datainc')->nullable();
            $table->unsignedBigInteger('usuatu')->nullable();
            $table->timestamp('datatu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'escola_id', 'turno']);
        });

        Schema::table('horario_slots', function (Blueprint $table) {
            // O unique atual cobre a FK de escola_id: cria um indice
            // simples antes de derruba-lo, senao o MySQL recusa (1553).
            $table->index('escola_id', 'horario_slots_escola_index');
            $table->dropUnique('horario_slots_unique');
            $table->foreignId('horario_id')->nullable()->after('escola_id')
                ->constrained('horarios')->cascadeOnDelete();
        });

        Schema::table('turmas', function (Blueprint $table) {
            $table->foreignId('horario_id')->nullable()->after('turno')
                ->constrained('horarios')->nullOnDelete();
        });

        // Backfill: um horario por (escola, turno) que ja tem slots.
        $grupos = DB::table('horario_slots')
            ->select('empresa_id', 'escola_id', 'turno')
            ->groupBy('empresa_id', 'escola_id', 'turno')
            ->get();

        foreach ($grupos as $g) {
            $horarioId = DB::table('horarios')->insertGetId([
                'empresa_id' => $g->empresa_id,
                'escola_id' => $g->escola_id,
                'turno' => $g->turno,
                'nome' => 'Matriz — '.ucfirst($g->turno),
                'ativo' => 'S',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('horario_slots')
                ->where('escola_id', $g->escola_id)
                ->where('turno', $g->turno)
                ->update(['horario_id' => $horarioId]);

            DB::table('turmas')
                ->where('escola_id', $g->escola_id)
                ->where('turno', $g->turno)
                ->whereNull('horario_id')
                ->update(['horario_id' => $horarioId]);
        }

        Schema::table('horario_slots', function (Blueprint $table) {
            $table->unique(['horario_id', 'dia_semana', 'ordem'], 'horario_slots_unique');
        });
    }

    public function down(): void
    {
        Schema::table('horario_slots', function (Blueprint $table) {
            $table->dropUnique('horario_slots_unique');
            $table->dropConstrainedForeignId('horario_id');
            $table->unique(['escola_id', 'turno', 'dia_semana', 'ordem'], 'horario_slots_unique');
            $table->dropIndex('horario_slots_escola_index');
        });
        Schema::table('turmas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('horario_id');
        });
        Schema::dropIfExists('horarios');
    }
};
