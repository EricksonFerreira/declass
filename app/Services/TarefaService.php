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
        $questoes = $data['questoes'] ?? [];
        unset($data['gerar_entregas'], $data['questoes']);

        return DB::transaction(function () use ($data, $gerarEntregas, $questoes) {
            $tarefa = parent::create($data);
            $this->sincronizarQuestoes($tarefa, $questoes);

            if ($gerarEntregas) {
                $this->gerarEntregas($tarefa);
            }

            return $tarefa;
        });
    }

    public function update($id, array $data)
    {
        $temQuestoes = array_key_exists('questoes', $data);
        $questoes = $data['questoes'] ?? [];
        unset($data['questoes']);

        return DB::transaction(function () use ($id, $data, $temQuestoes, $questoes) {
            $tarefa = parent::update($id, $data);

            if ($temQuestoes) {
                $this->sincronizarQuestoes($tarefa, $questoes);
            }

            return $tarefa;
        });
    }

    /**
     * Questoes da atividade/prova respondidas online: objetivas
     * (A-E com gabarito) ou discursivas. Substitui o conjunto
     * atual — alunos ainda nao enviaram respostas na edicao.
     */
    private function sincronizarQuestoes(Tarefa $tarefa, array $questoes): void
    {
        $tarefa->questoes()->delete();

        foreach (array_values($questoes) as $i => $questao) {
            $alternativas = collect($questao['alternativas'] ?? [])
                ->filter(fn ($texto) => trim((string) $texto) !== '')
                ->all();

            $tarefa->questoes()->create([
                'empresa_id' => $tarefa->empresa_id,
                'ordem' => $i + 1,
                'tipo' => $questao['tipo'],
                'enunciado' => $questao['enunciado'],
                'alternativas' => $questao['tipo'] === 'objetiva' ? $alternativas : null,
                'gabarito' => $questao['tipo'] === 'objetiva' ? ($questao['gabarito'] ?? null) : null,
                'valor' => $questao['valor'] ?? null,
            ]);
        }
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
