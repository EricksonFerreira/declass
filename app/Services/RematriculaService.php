<?php

namespace App\Services;

use App\Models\Matricula;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Rematricula em lote: copia as matriculas ativas de uma turma de
 * origem para um ano letivo de destino, escolhendo a turma de
 * destino por aluno.
 *
 * Regras:
 * - a escola de destino pode ser diferente da origem (a operacao
 *   em lote cobre tambem transferencia entre unidades); a escola
 *   de cadastro do aluno nao e alterada;
 * - o ano letivo de destino precisa ser diferente do ano da turma
 *   de origem;
 * - aluno que ja possui qualquer matricula no ano destino e pulado;
 * - numero_chamada recebe o proximo livre da turma de destino
 *   (considerando registros soft-deleted, como na aprovacao de
 *   solicitacao de matricula);
 * - plano de mensalidade e bolsa sao copiados da matricula de origem.
 */
class RematriculaService
{
    public function __construct(protected EmpresaContext $empresaContext) {}

    /**
     * Matriculas ativas da turma de origem elegiveis para rematricula:
     * o aluno ainda nao tem nenhuma matricula no ano destino.
     */
    public function matriculasElegiveis(int $turmaOrigemId, int $anoDestinoId, int $empresaId): Collection
    {
        $alunosComMatriculaDestino = Matricula::query()
            ->where('empresa_id', $empresaId)
            ->where('ano_letivo_id', $anoDestinoId)
            ->pluck('aluno_id');

        return Matricula::query()
            ->where('empresa_id', $empresaId)
            ->where('turma_id', $turmaOrigemId)
            ->where('status', 'ativa')
            ->whereNotIn('aluno_id', $alunosComMatriculaDestino)
            ->with(['aluno.pessoa', 'planoMensalidade'])
            ->orderBy('numero_chamada')
            ->orderBy('id')
            ->get();
    }

    /**
     * Todas as turmas do tenant com ocupacao (matriculas ativas),
     * para os selects em cascata da tela.
     */
    public function turmasComOcupacao(int $empresaId): Collection
    {
        return Turma::query()
            ->where('empresa_id', $empresaId)
            ->with('etapa:id,nome')
            ->withCount(['matriculas as alunos_ativos_count' => fn ($q) => $q->where('status', 'ativa')])
            ->orderBy('nome')
            ->get(['id', 'ano_letivo_id', 'etapa_id', 'nome', 'capacidade_maxima']);
    }

    /**
     * Turmas do ano letivo destino com ocupacao (matriculas ativas).
     */
    public function turmasDestino(int $anoDestinoId, int $empresaId): Collection
    {
        return Turma::query()
            ->where('empresa_id', $empresaId)
            ->where('ano_letivo_id', $anoDestinoId)
            ->with('etapa:id,nome')
            ->withCount(['matriculas as alunos_ativos_count' => fn ($q) => $q->where('status', 'ativa')])
            ->orderBy('nome')
            ->get();
    }

    /**
     * Cria as matriculas do ano destino em transacao.
     *
     * @param  array<int, int>  $itens  mapa matricula_origem_id => turma_destino_id
     * @return array{criadas: int, ignoradas: array<int, string>}
     */
    public function rematricular(array $itens, int $anoDestinoId, int $empresaId): array
    {
        $criadas = 0;
        $ignoradas = [];

        DB::transaction(function () use ($itens, $anoDestinoId, $empresaId, &$criadas, &$ignoradas) {
            foreach ($itens as $matriculaOrigemId => $turmaDestinoId) {
                $motivo = $this->criarUma((int) $matriculaOrigemId, (int) $turmaDestinoId, $anoDestinoId, $empresaId);

                if ($motivo === null) {
                    $criadas++;
                } else {
                    $ignoradas[] = $motivo;
                }
            }
        });

        return ['criadas' => $criadas, 'ignoradas' => $ignoradas];
    }

    /**
     * Retorna null quando a matricula foi criada, ou o motivo do
     * pulo (para exibicao ao usuario).
     */
    private function criarUma(int $matriculaOrigemId, int $turmaDestinoId, int $anoDestinoId, int $empresaId): ?string
    {
        $origem = Matricula::query()
            ->where('empresa_id', $empresaId)
            ->with('aluno.pessoa')
            ->find($matriculaOrigemId);

        if (! $origem) {
            return "Matrícula #{$matriculaOrigemId} não encontrada.";
        }

        $nomeAluno = $origem->aluno?->pessoa?->nome ?? "aluno #{$origem->aluno_id}";

        if ($origem->status !== 'ativa') {
            return "{$nomeAluno}: matrícula de origem não está ativa.";
        }

        $turma = Turma::query()
            ->where('empresa_id', $empresaId)
            ->find($turmaDestinoId);

        if (! $turma || (int) $turma->ano_letivo_id !== $anoDestinoId) {
            return "{$nomeAluno}: turma de destino inválida.";
        }

        $jaMatriculado = Matricula::query()
            ->where('empresa_id', $empresaId)
            ->where('aluno_id', $origem->aluno_id)
            ->where('ano_letivo_id', $anoDestinoId)
            ->exists();

        if ($jaMatriculado) {
            return "{$nomeAluno}: já possui matrícula no ano letivo de destino.";
        }

        $numeroChamada = ((int) Matricula::withTrashed()
            ->where('turma_id', $turma->id)
            ->max('numero_chamada')) + 1;

        Matricula::create([
            'empresa_id' => $empresaId,
            'aluno_id' => $origem->aluno_id,
            'turma_id' => $turma->id,
            'ano_letivo_id' => $turma->ano_letivo_id,
            'status' => 'ativa',
            'numero_chamada' => $numeroChamada,
            'data_matricula' => now()->toDateString(),
            'plano_mensalidade_id' => $origem->plano_mensalidade_id,
            'bolsa_percentual' => $origem->bolsa_percentual,
            'observacoes' => "Rematrícula — origem: matrícula #{$origem->id} (ano letivo {$origem->ano_letivo_id}).",
        ]);

        return null;
    }

    /**
     * Valida a selecao da tela: a turma de origem e o ano letivo de
     * destino existem no tenant e o destino nao e o ano da propria
     * turma. Lanca ValidationException em caso contrario.
     *
     * @return array{0: Turma, 1: \App\Models\AnoLetivo}
     */
    public function validarSelecao(int $turmaOrigemId, int $anoDestinoId, int $empresaId): array
    {
        $turmaOrigem = Turma::query()
            ->where('empresa_id', $empresaId)
            ->with(['anoLetivo.escola:id,nome', 'etapa:id,nome'])
            ->find($turmaOrigemId);

        if (! $turmaOrigem) {
            throw ValidationException::withMessages([
                'turma_origem' => 'Turma de origem não encontrada.',
            ]);
        }

        $anoDestino = \App\Models\AnoLetivo::query()
            ->where('empresa_id', $empresaId)
            ->with('escola:id,nome')
            ->find($anoDestinoId);

        if (! $anoDestino) {
            throw ValidationException::withMessages([
                'ano_destino' => 'Ano letivo de destino não encontrado.',
            ]);
        }

        if ((int) $turmaOrigem->ano_letivo_id === (int) $anoDestino->id) {
            throw ValidationException::withMessages([
                'ano_destino' => 'Ano letivo de destino deve ser diferente do ano da turma de origem.',
            ]);
        }

        return [$turmaOrigem, $anoDestino];
    }
}
