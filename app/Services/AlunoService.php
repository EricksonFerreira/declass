<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Pessoa;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AlunoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Aluno $aluno)
    {
        $this->model = $aluno;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Aluno) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Aluno');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Paginacao com filtros da sessao. Diferente do metodo generico,
     * 'nome'/'email' buscam na pessoa relacionada (nao sao colunas
     * da tabela alunos).
     */
    public function paginateComFiltros(array $filters, int $perPage): LengthAwarePaginator
    {
        $filters = $this->sanitizeEscolaContextFilter($filters);

        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->with(['pessoa', 'escola']);

        foreach ($filters as $field => $filter) {
            if (! is_array($filter) || ! array_key_exists('valor', $filter)) {
                continue;
            }

            $valor = $filter['valor'];
            if ($valor === null || $valor === '') {
                continue;
            }

            $typeWhere = $filter['typeWhere'] ?? 'where';

            if ($field === 'nome') {
                $query->whereHas('pessoa', function ($q) use ($valor) {
                    $q->where('nome', 'like', '%'.$valor.'%')
                        ->orWhere('nome_social', 'like', '%'.$valor.'%');
                });

                continue;
            }

            if (! SchemaCache::hasColumn($this->model->getTable(), (string) $field)) {
                continue;
            }

            if ($typeWhere === 'like') {
                $query->where($field, 'like', '%'.$valor.'%');
            } elseif (is_array($valor)) {
                $query->whereIn($field, $valor);
            } else {
                $query->where($field, $valor);
            }
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Cria a Pessoa (dados pessoais) e o Aluno (dados escolares)
     * em uma unica transacao.
     */
    public function createWithPessoa(array $pessoaData, array $alunoData): Aluno
    {
        $this->injectEmpresaId($pessoaData);
        $this->injectEmpresaId($alunoData);

        return DB::transaction(function () use ($pessoaData, $alunoData) {
            $pessoa = Pessoa::create($pessoaData);
            $alunoData['pessoa_id'] = $pessoa->id;

            return Aluno::create($alunoData);
        });
    }

    /**
     * Atualiza Pessoa e Aluno do registro informado.
     */
    public function updateWithPessoa($id, array $pessoaData, array $alunoData): ?Aluno
    {
        $aluno = $this->applyEmpresaScope($this->model->newQuery())
            ->with('pessoa')
            ->find($id);

        if (! $aluno) {
            return null;
        }

        $this->injectEmpresaId($alunoData, true);

        return DB::transaction(function () use ($aluno, $pessoaData, $alunoData) {
            if ($aluno->pessoa) {
                $aluno->pessoa->update($pessoaData);
            }

            $aluno->update($alunoData);

            return $aluno->fresh(['pessoa', 'escola']);
        });
    }

    /**
     * Vincula (ou atualiza) um responsavel ao aluno com o papel
     * informado. A unicidade e por (aluno, responsavel, tipo).
     */
    public function vincularResponsavel(Aluno $aluno, array $data): void
    {
        DB::transaction(function () use ($aluno, $data) {
            $responsavelId = (int) $data['responsavel_id'];
            $tipo = $data['tipo'] ?? 'legal';

            if (! empty($data['principal'])) {
                DB::table('aluno_responsavel')
                    ->where('aluno_id', $aluno->id)
                    ->update(['principal' => false]);
            }

            $atributos = [
                'tipo' => $tipo,
                'principal' => ! empty($data['principal']),
                'pode_retirar' => ! empty($data['pode_retirar']),
                'autoriza_contato' => array_key_exists('autoriza_contato', $data)
                    ? (bool) $data['autoriza_contato']
                    : true,
                'restricoes' => $data['restricoes'] ?? null,
                'observacoes' => $data['observacoes'] ?? null,
                'updated_at' => now(),
            ];

            $existente = DB::table('aluno_responsavel')
                ->where('aluno_id', $aluno->id)
                ->where('responsavel_id', $responsavelId)
                ->where('tipo', $tipo)
                ->first();

            if ($existente) {
                DB::table('aluno_responsavel')->where('id', $existente->id)->update($atributos);
            } else {
                DB::table('aluno_responsavel')->insert($atributos + [
                    'aluno_id' => $aluno->id,
                    'responsavel_id' => $responsavelId,
                    'created_at' => now(),
                ]);
            }
        });
    }

    /**
     * Atualiza flags/autorizacoes de um vinculo existente.
     */
    public function atualizarVinculo(Aluno $aluno, int $vinculoId, array $data): bool
    {
        return DB::transaction(function () use ($aluno, $vinculoId, $data) {
            if (! empty($data['principal'])) {
                DB::table('aluno_responsavel')
                    ->where('aluno_id', $aluno->id)
                    ->where('id', '!=', $vinculoId)
                    ->update(['principal' => false]);
            }

            $atributos = ['updated_at' => now()];

            foreach (['principal', 'pode_retirar', 'autoriza_contato'] as $flag) {
                if (array_key_exists($flag, $data)) {
                    $atributos[$flag] = (bool) $data[$flag];
                }
            }

            foreach (['restricoes', 'observacoes'] as $campo) {
                if (array_key_exists($campo, $data)) {
                    $atributos[$campo] = $data[$campo];
                }
            }

            return DB::table('aluno_responsavel')
                ->where('id', $vinculoId)
                ->where('aluno_id', $aluno->id)
                ->update($atributos) > 0;
        });
    }

    public function desvincularResponsavel(Aluno $aluno, int $vinculoId): bool
    {
        return DB::table('aluno_responsavel')
            ->where('id', $vinculoId)
            ->where('aluno_id', $aluno->id)
            ->delete() > 0;
    }

    /**
     * Vinculos do aluno com dados da pessoa do responsavel —
     * pronto para exibicao na tela de detalhes.
     */
    public function vinculosDoAluno(Aluno $aluno)
    {
        return DB::table('aluno_responsavel as ar')
            ->join('responsaveis as r', 'r.id', '=', 'ar.responsavel_id')
            ->join('pessoas as p', 'p.id', '=', 'r.pessoa_id')
            ->where('ar.aluno_id', $aluno->id)
            ->select('ar.*', 'r.id as responsavel_id', 'p.nome as pessoa_nome', 'p.email as pessoa_email')
            ->orderBy('p.nome')
            ->get();
    }
}
