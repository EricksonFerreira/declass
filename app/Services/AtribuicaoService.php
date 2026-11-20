<?php

namespace App\Services;

use App\Models\Atribuicao;
use App\Models\Turma;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AtribuicaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Atribuicao $atribuicao)
    {
        $this->model = $atribuicao;
    }

    /**
     * Atribui um professor a uma ou varias disciplinas da turma.
     * Lista vazia = regente (todas as disciplinas, disciplina_id nula),
     * como na educacao infantil. Atribuicoes identicas sao puladas.
     *
     * @return array{criadas: int, existentes: int}
     */
    public function atribuirProfessor(Turma $turma, array $data): array
    {
        $ids = array_filter($data['disciplina_ids'] ?? []);
        if ($ids === [] && ! empty($data['disciplina_id'])) {
            $ids = [(int) $data['disciplina_id']];
        }
        $alvos = $ids !== [] ? array_values(array_unique($ids)) : [null];

        $criadas = 0;
        $existentes = 0;

        DB::transaction(function () use ($turma, $data, $alvos, &$criadas, &$existentes) {
            foreach ($alvos as $disciplinaId) {
                $existe = $this->model->newQuery()
                    ->where('turma_id', $turma->id)
                    ->where('user_id', $data['user_id'])
                    ->when(
                        $disciplinaId,
                        fn ($q) => $q->where('disciplina_id', $disciplinaId),
                        fn ($q) => $q->whereNull('disciplina_id')
                    )
                    ->exists();

                if ($existe) {
                    $existentes++;

                    continue;
                }

                $payload = $data;
                $payload['turma_id'] = $turma->id;
                $payload['disciplina_id'] = $disciplinaId;
                unset($payload['vinculo'], $payload['disciplina_ids']);

                $this->create($payload);
                $criadas++;
            }
        });

        return ['criadas' => $criadas, 'existentes' => $existentes];
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Atribuicao) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Atribuicao');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
