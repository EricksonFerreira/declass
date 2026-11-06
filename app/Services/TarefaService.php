<?php

namespace App\Services;

use App\Models\EntregaTarefa;
use App\Models\Matricula;
use App\Models\Tarefa;
use App\Models\Turma;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TarefaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Tarefa $tarefa)
    {
        $this->model = $tarefa;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Tarefa) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Tarefa');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        $gerarEntregas = (bool) ($data['gerar_entregas'] ?? false);
        unset($data['gerar_entregas']);

        return DB::transaction(function () use ($data, $gerarEntregas) {
            $tarefa = parent::create($data);

            if ($gerarEntregas) {
                $this->gerarEntregas($tarefa);
            }

            return $tarefa;
        });
    }

    /**
     * Cria uma entrega "pendente" para cada matricula ativa da turma.
     * Idempotente: nao duplica entregas existentes.
     */
    public function gerarEntregas(Tarefa $tarefa): int
    {
        $matriculaIds = Matricula::query()
            ->where('turma_id', $tarefa->turma_id)
            ->where('status', 'ativa')
            ->pluck('id');

        $existentes = EntregaTarefa::query()
            ->where('tarefa_id', $tarefa->id)
            ->pluck('matricula_id')
            ->all();

        $agora = now();
        $rows = [];
        foreach ($matriculaIds as $matriculaId) {
            if (in_array($matriculaId, $existentes, true)) {
                continue;
            }
            $rows[] = [
                'empresa_id' => $tarefa->empresa_id,
                'tarefa_id' => $tarefa->id,
                'matricula_id' => $matriculaId,
                'status' => 'pendente',
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }

        foreach (array_chunk($rows, 300) as $chunk) {
            EntregaTarefa::insert($chunk);
        }

        return count($rows);
    }

    /**
     * Professor so lanca/corrige tarefas de turmas que leciona.
     */
    public function validarAcessoTurma(Turma $turma, $user): void
    {
        if ($user->isProfessor()
            && ! app(BoletimService::class)->podeVerTurma($turma, $user)) {
            throw ValidationException::withMessages([
                'turma_id' => 'Você não leciona nesta turma.',
            ]);
        }
    }
}
