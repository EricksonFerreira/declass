<?php

namespace App\Services;

use App\Models\Pesquisa;
use App\Models\PesquisaPergunta;
use App\Models\PesquisaResposta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PesquisaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Pesquisa $pesquisa)
    {
        $this->model = $pesquisa;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Pesquisa) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Pesquisa');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /** Cria a pesquisa junto com as perguntas (array indexado). */
    public function createComPerguntas(array $data): Pesquisa
    {
        return DB::transaction(function () use ($data) {
            $perguntas = $data['perguntas'] ?? [];
            unset($data['perguntas']);

            $pesquisa = $this->create($data);
            $this->sincronizarPerguntas($pesquisa, $perguntas);

            return $pesquisa;
        });
    }

    public function updateComPerguntas($id, array $data): ?Pesquisa
    {
        return DB::transaction(function () use ($id, $data) {
            $perguntas = $data['perguntas'] ?? null;
            unset($data['perguntas']);

            $pesquisa = $this->update($id, $data);
            if ($pesquisa && $perguntas !== null) {
                $this->sincronizarPerguntas($pesquisa, $perguntas);
            }

            return $pesquisa;
        });
    }

    /** Substitui as perguntas da pesquisa pelas informadas. */
    private function sincronizarPerguntas(Pesquisa $pesquisa, array $perguntas): void
    {
        $pesquisa->perguntas()->delete();

        foreach (array_values($perguntas) as $i => $p) {
            if (empty($p['pergunta'])) {
                continue;
            }
            PesquisaPergunta::create([
                'pesquisa_id' => $pesquisa->id,
                'ordem' => $i + 1,
                'pergunta' => $p['pergunta'],
                'tipo' => $p['tipo'] ?? 'nota',
                'obrigatoria' => ($p['obrigatoria'] ?? 'S') === 'N' ? 'N' : 'S',
            ]);
        }
    }

    /**
     * Pesquisas visiveis no portal para os alunos informados:
     * abertas, dentro do periodo, da escola do aluno (ou rede
     * toda) e ainda nao respondidas pelo usuario.
     */
    public function pesquisasParaUsuario($user, Collection $alunos): Collection
    {
        $escolaIds = $alunos->pluck('escola_id')->filter()->unique();
        if ($escolaIds->isEmpty()) {
            return collect();
        }

        $respondidas = PesquisaResposta::query()
            ->where('user_id', $user->id)
            ->pluck('pesquisa_id');

        return Pesquisa::query()
            ->with('perguntas')
            ->where('status', 'aberta')
            ->whereDate('data_inicio', '<=', now()->toDateString())
            ->whereDate('data_fim', '>=', now()->toDateString())
            ->where(fn ($q) => $q->whereNull('escola_id')->orWhereIn('escola_id', $escolaIds))
            ->whereNotIn('id', $respondidas)
            ->orderBy('data_fim')
            ->get();
    }

    /**
     * Registra a resposta do usuario. Uma resposta por usuario
     * por pesquisa (unique). Valida obrigatorias e tipos.
     */
    public function responder(Pesquisa $pesquisa, $user, array $respostas, ?int $alunoId = null): PesquisaResposta
    {
        abort_unless($pesquisa->estaAberta(), 403, 'Esta pesquisa não está aberta.');

        $perguntas = $pesquisa->perguntas()->get();
        $erros = [];

        foreach ($perguntas as $p) {
            $valor = $respostas[$p->id] ?? null;
            $vazio = $valor === null || $valor === '';

            if ($p->obrigatoria === 'S' && $vazio) {
                $erros["respostas.{$p->id}"] = 'Resposta obrigatória.';

                continue;
            }
            if ($vazio) {
                continue;
            }

            match ($p->tipo) {
                'nota' => $this->validarNota($valor, $p, $erros),
                'sim_nao' => $this->validarSimNao($valor, $p, $erros),
                default => null,
            };
        }

        if ($erros !== []) {
            throw ValidationException::withMessages($erros);
        }

        // Remove respostas de perguntas que nao pertencem a pesquisa
        $validas = $perguntas->pluck('id')->map(fn ($id) => (string) $id)->all();
        $limpas = collect($respostas)
            ->only($validas)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->all();

        return PesquisaResposta::create([
            'empresa_id' => $pesquisa->empresa_id,
            'pesquisa_id' => $pesquisa->id,
            'user_id' => $user->id,
            'aluno_id' => $alunoId,
            'respostas' => $limpas,
        ]);
    }

    private function validarNota($valor, PesquisaPergunta $p, array &$erros): void
    {
        if (! is_numeric($valor) || (int) $valor < 1 || (int) $valor > 5) {
            $erros["respostas.{$p->id}"] = 'Nota deve ser entre 1 e 5.';
        }
    }

    private function validarSimNao($valor, PesquisaPergunta $p, array &$erros): void
    {
        if (! in_array($valor, ['S', 'N'], true)) {
            $erros["respostas.{$p->id}"] = 'Resposta inválida.';
        }
    }

    /**
     * Resultados agregados por pergunta:
     * nota -> media e distribuicao; sim_nao -> contagens;
     * texto -> lista de respostas.
     */
    public function resultados(Pesquisa $pesquisa, array $filtros = []): array
    {
        $respostas = $pesquisa->respostas()->with('usuario:id,name')
            ->when(! empty($filtros['de']), fn ($q) => $q->whereDate('created_at', '>=', $filtros['de']))
            ->when(! empty($filtros['ate']), fn ($q) => $q->whereDate('created_at', '<=', $filtros['ate']))
            ->get();
        $perguntas = $pesquisa->perguntas
            ->when(! empty($filtros['pergunta_id']), fn ($c) => $c->where('id', (int) $filtros['pergunta_id']));

        $porPergunta = [];
        foreach ($perguntas as $p) {
            $valores = $respostas->map(fn ($r) => $r->respostas[$p->id] ?? null)
                ->filter(fn ($v) => $v !== null && $v !== '')
                ->values();

            $item = [
                'pergunta' => $p,
                'total' => $valores->count(),
            ];

            if ($p->tipo === 'nota') {
                $nums = $valores->map(fn ($v) => (int) $v);
                $item['media'] = $nums->isNotEmpty() ? round($nums->avg(), 2) : null;
                $item['distribuicao'] = $nums->countBy()->sortKeys()->all();
            } elseif ($p->tipo === 'sim_nao') {
                $item['sim'] = $valores->filter(fn ($v) => $v === 'S')->count();
                $item['nao'] = $valores->filter(fn ($v) => $v === 'N')->count();
            } else {
                $item['textos'] = $respostas
                    ->map(fn ($r) => ['usuario' => $r->usuario?->name, 'texto' => $r->respostas[$p->id] ?? null])
                    ->filter(fn ($t) => ! empty($t['texto']))
                    ->values();
            }

            $porPergunta[] = $item;
        }

        return [
            'total_respostas' => $respostas->count(),
            'por_pergunta' => $porPergunta,
        ];
    }
}
