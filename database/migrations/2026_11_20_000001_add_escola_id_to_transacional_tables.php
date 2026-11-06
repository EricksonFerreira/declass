<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Desnormaliza escola_id nas tabelas transacionais que ainda nao tinham a
 * coluna, para que o escopo global de escola (seletor do navbar) filtre
 * todos os dados da unidade. Nullable: registros "rede toda" (material,
 * pesquisa etc.) permanecem nulos e visiveis em qualquer escola.
 */
return new class extends Migration
{
    private const TABELAS = [
        'matriculas',
        'mensalidades',
        'diarios',
        'frequencias',
        'avaliacoes',
        'notas',
        'emprestimos_livros',
        'tarefas',
        'entregas_tarefa',
        'saude_alunos',
        'aluno_documentos',
        'transporte_alunos',
        'paradas_transporte',
        'atribuicoes',
        'pesquisa_respostas',
        'movimentacoes_estoque',
    ];

    public function up(): void
    {
        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->foreignId('escola_id')
                    ->nullable()
                    ->after('empresa_id')
                    ->constrained('escolas')
                    ->nullOnDelete();
                $table->index('escola_id');
            });
        }

        $this->backfill();
    }

    public function down(): void
    {
        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropConstrainedForeignId('escola_id');
            });
        }
    }

    /**
     * Preenche escola_id dos registros existentes resolvendo a escola pela
     * cadeia de relacoes (turma/aluno/material/pesquisa/etc). Portavel
     * entre MySQL e SQLite — nao usa UPDATE...JOIN.
     */
    private function backfill(): void
    {
        $mapaTurma = DB::table('turmas')->pluck('escola_id', 'id')->all();
        $mapaAluno = DB::table('alunos')->pluck('escola_id', 'id')->all();
        $mapaRota = DB::table('rotas_transporte')->pluck('escola_id', 'id')->all();
        $mapaPesquisa = DB::table('pesquisas')->pluck('escola_id', 'id')->all();
        $mapaMaterial = DB::table('materiais')->pluck('escola_id', 'id')->all();

        $mapaMatricula = DB::table('matriculas')
            ->join('turmas', 'matriculas.turma_id', '=', 'turmas.id')
            ->pluck('turmas.escola_id', 'matriculas.id')->all();

        $mapaDiario = DB::table('diarios')
            ->join('turmas', 'diarios.turma_id', '=', 'turmas.id')
            ->pluck('turmas.escola_id', 'diarios.id')->all();

        $mapaAvaliacao = DB::table('avaliacoes')
            ->join('turmas', 'avaliacoes.turma_id', '=', 'turmas.id')
            ->pluck('turmas.escola_id', 'avaliacoes.id')->all();

        $mapaTarefa = DB::table('tarefas')
            ->join('turmas', 'tarefas.turma_id', '=', 'turmas.id')
            ->pluck('turmas.escola_id', 'tarefas.id')->all();

        $this->preencher('matriculas', 'turma_id', $mapaTurma);
        $this->preencher('mensalidades', 'matricula_id', $mapaMatricula);
        $this->preencher('diarios', 'turma_id', $mapaTurma);
        $this->preencher('frequencias', 'diario_id', $mapaDiario);
        $this->preencher('frequencias', 'matricula_id', $mapaMatricula);
        $this->preencher('avaliacoes', 'turma_id', $mapaTurma);
        $this->preencher('notas', 'matricula_id', $mapaMatricula);
        $this->preencher('notas', 'avaliacao_id', $mapaAvaliacao);
        $this->preencher('emprestimos_livros', 'aluno_id', $mapaAluno);
        $this->preencher('tarefas', 'turma_id', $mapaTurma);
        $this->preencher('entregas_tarefa', 'tarefa_id', $mapaTarefa);
        $this->preencher('entregas_tarefa', 'matricula_id', $mapaMatricula);
        $this->preencher('saude_alunos', 'aluno_id', $mapaAluno);
        $this->preencher('aluno_documentos', 'aluno_id', $mapaAluno);
        $this->preencher('transporte_alunos', 'aluno_id', $mapaAluno);
        $this->preencher('transporte_alunos', 'rota_id', $mapaRota);
        $this->preencher('paradas_transporte', 'rota_id', $mapaRota);
        $this->preencher('atribuicoes', 'turma_id', $mapaTurma);
        $this->preencher('pesquisa_respostas', 'pesquisa_id', $mapaPesquisa);
        $this->preencher('movimentacoes_estoque', 'material_id', $mapaMaterial);
    }

    private function preencher(string $tabela, string $fk, array $mapa): void
    {
        DB::table($tabela)
            ->whereNull('escola_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($tabela, $fk, $mapa) {
                foreach ($rows as $row) {
                    $escolaId = $mapa[$row->{$fk}] ?? null;

                    if ($escolaId !== null) {
                        DB::table($tabela)->where('id', $row->id)->update(['escola_id' => $escolaId]);
                    }
                }
            });
    }
};
