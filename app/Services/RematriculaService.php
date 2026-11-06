<?php

namespace App\Services;

use App\Models\Matricula;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Rematricula em lote: copia as matriculas ativas de um ano letivo
 * para o ano seguinte, escolhendo a turma de destino por aluno.
 *
 * Regras:
 * - origem e destino precisam ser da mesma escola (transferencia
 *   entre unidades e uma operacao individual, nao em lote);
 * - aluno que ja possui qualquer matricula no ano destino e pulado;
 * - numero_chamada recebe o proximo livre da turma de destino
 *   (considerando registros soft-deleted, como na aprovacao de
 *   solicitacao de matricula);
 * - plano de mensalidade e copiado da matricula de origem.
 */
class RematriculaService
{
    public function __construct(protected EmpresaContext $empresaContext) {}

    /**
     * Matriculas ativas do ano origem elegiveis para rematricula:
     * o aluno ainda nao tem nenhuma matricula no ano destino.
     */
    public function matriculasElegiveis(int $anoOrigemId, int $anoDestinoId, int $empresaId): Collection
    {
        $alunosComMatriculaDestino = Matricula::query()
            ->where('empresa_id', $empresaId)
            ->where('ano_letivo_id', $anoDestinoId)
            ->pluck('aluno_id');

        return Matricula::query()
            ->where('empresa_id', $empresaId)
            ->where('ano_letivo_id', $anoOrigemId)
            ->where('status', 'ativa')
            ->whereNotIn('aluno_id', $alunosComMatriculaDestino)
            ->with(['aluno.pessoa', 'turma.escola', 'planoMensalidade'])
            ->orderBy('numero_chamada')
            ->orderBy('id')
            ->get();
    }

    /**
     * Turmas do ano letivo destino da escola informada.
     */
    public function turmasDestino(int $anoDestinoId, int $escolaId, int $empresaId): Collection
    {
        return Turma::query()
            ->where('empresa_id', $empresaId)
            ->where('ano_letivo_id', $anoDestinoId)
            ->where('escola_id', $escolaId)
            ->with('etapa:id,nome')
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
            ->with(['aluno.pessoa', 'turma'])
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

        if ((int) $turma->escola_id !== (int) $origem->turma?->escola_id) {
            return "{$nomeAluno}: turma de destino é de outra escola.";
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
     * Valida que os dois anos letivos existem no tenant e pertencem
     * a mesma escola. Lanca ValidationException em caso contrario.
     */
    public function validarAnos(int $anoOrigemId, int $anoDestinoId, int $empresaId): array
    {
        $anos = \App\Models\AnoLetivo::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('id', [$anoOrigemId, $anoDestinoId])
            ->with('escola:id,nome')
            ->get()
            ->keyBy('id');

        if ($anos->count() !== 2) {
            throw ValidationException::withMessages([
                'ano_origem' => 'Ano letivo de origem ou destino não encontrado.',
            ]);
        }

        $origem = $anos[$anoOrigemId];
        $destino = $anos[$anoDestinoId];

        if ((int) $origem->escola_id !== (int) $destino->escola_id) {
            throw ValidationException::withMessages([
                'ano_destino' => 'Ano letivo de destino deve ser da mesma escola do ano de origem.',
            ]);
        }

        if ($anoOrigemId === $anoDestinoId) {
            throw ValidationException::withMessages([
                'ano_destino' => 'Ano letivo de destino deve ser diferente do ano de origem.',
            ]);
        }

        return [$origem, $destino];
    }
}
