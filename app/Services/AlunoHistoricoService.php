<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\AlunoDocumento;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Nota;
use App\Models\Ocorrencia;
use App\Models\SaudeAluno;
use App\Models\TransporteAluno;
use Illuminate\Support\Collection;

/**
 * Consolida o historico completo do aluno em uma unica visao
 * (padrao "Student 360"/registro cumulativo): matriculas por ano
 * letivo, desempenho (frequencia e media), resumo financeiro,
 * saude, transporte e uma linha do tempo cronologica dos eventos.
 */
class AlunoHistoricoService
{
    /**
     * @return array<string, mixed>
     */
    public function montar(Aluno $aluno): array
    {
        $matriculas = $aluno->matriculas()
            ->with(['turma.anoLetivo', 'turma.etapa'])
            ->orderBy('data_matricula')
            ->orderBy('id')
            ->get();

        $matriculaIds = $matriculas->pluck('id');

        $frequencias = $this->frequenciaPorMatricula($matriculaIds);
        $medias = $this->mediaPorMatricula($matriculaIds);
        $financeiro = $this->resumoFinanceiro($matriculaIds);
        $eventos = $this->linhaDoTempo($aluno, $matriculas);

        $saude = SaudeAluno::query()->where('aluno_id', $aluno->id)->first();
        $transporte = TransporteAluno::query()
            ->where('aluno_id', $aluno->id)
            ->where('ativo', 'S')
            ->with(['rota', 'parada'])
            ->first();

        $anosNaEscola = $matriculas->pluck('turma.anoLetivo.ano')->filter()->unique()->count();
        $frequenciaGeral = $this->frequenciaGeral($frequencias);

        return [
            'matriculas' => $matriculas,
            'frequencias' => $frequencias,
            'medias' => $medias,
            'financeiro' => $financeiro,
            'eventos' => $eventos,
            'saude' => $saude,
            'transporte' => $transporte,
            'anosNaEscola' => $anosNaEscola,
            'frequenciaGeral' => $frequenciaGeral,
        ];
    }

    /**
     * Frequencia por matricula: total por status para calcular
     * percentual de presenca e faltas.
     *
     * @return Collection<int, Collection<string, int>>
     */
    private function frequenciaPorMatricula(Collection $matriculaIds): Collection
    {
        if ($matriculaIds->isEmpty()) {
            return collect();
        }

        return Frequencia::query()
            ->whereIn('matricula_id', $matriculaIds)
            ->selectRaw('matricula_id, status, COUNT(*) as total')
            ->groupBy('matricula_id', 'status')
            ->get()
            ->groupBy('matricula_id')
            ->map(fn ($rows) => $rows->pluck('total', 'status'));
    }

    /**
     * Media de notas por matricula, em percentual sobre a nota
     * maxima de cada avaliacao (nota_maxima pode variar).
     *
     * @return Collection<int, array{media: float, total: int}>
     */
    private function mediaPorMatricula(Collection $matriculaIds): Collection
    {
        if ($matriculaIds->isEmpty()) {
            return collect();
        }

        return Nota::query()
            ->join('avaliacoes', 'notas.avaliacao_id', '=', 'avaliacoes.id')
            ->whereIn('notas.matricula_id', $matriculaIds)
            ->where('avaliacoes.nota_maxima', '>', 0)
            ->selectRaw('notas.matricula_id, AVG(notas.nota * 100.0 / avaliacoes.nota_maxima) as media, COUNT(*) as total')
            ->groupBy('notas.matricula_id')
            ->get()
            ->keyBy('matricula_id')
            ->map(fn ($r) => ['media' => round((float) $r->media, 1), 'total' => (int) $r->total]);
    }

    /**
     * Resumo financeiro das mensalidades das matriculas do aluno:
     * contagens por status e valor total em aberto (pendentes,
     * separando vencidas das a vencer).
     *
     * @return array<string, mixed>
     */
    private function resumoFinanceiro(Collection $matriculaIds): array
    {
        $vazio = [
            'total' => 0, 'pagas' => 0, 'pendentes' => 0,
            'atrasadas' => 0, 'a_vencer' => 0, 'canceladas' => 0,
            'valor_em_aberto' => 0.0, 'valor_atrasado' => 0.0,
        ];

        if ($matriculaIds->isEmpty()) {
            return $vazio;
        }

        $mensalidades = Mensalidade::query()->whereIn('matricula_id', $matriculaIds)->get();
        $hoje = now()->toDateString();

        $emAberto = $mensalidades->where('status', 'pendente');
        $atrasadas = $emAberto->filter(fn ($m) => $m->vencimento && $m->vencimento->toDateString() < $hoje);

        return [
            'total' => $mensalidades->count(),
            'pagas' => $mensalidades->where('status', 'paga')->count(),
            'pendentes' => $emAberto->count(),
            'atrasadas' => $atrasadas->count(),
            'a_vencer' => $emAberto->count() - $atrasadas->count(),
            'canceladas' => $mensalidades->where('status', 'cancelada')->count(),
            'valor_em_aberto' => round((float) $emAberto->sum('valor'), 2),
            'valor_atrasado' => round((float) $atrasadas->sum('valor'), 2),
        ];
    }

    /**
     * Linha do tempo cronologica: matriculas, ocorrencias e
     * documentos ordenados do mais recente para o mais antigo.
     *
     * @return Collection<int, array{data: string, tipo: string, titulo: string, detalhe: string|null, cor: string}>
     */
    private function linhaDoTempo(Aluno $aluno, Collection $matriculas): Collection
    {
        $eventos = collect();

        foreach ($matriculas as $matricula) {
            $turma = $matricula->turma;
            $eventos->push([
                'data' => $matricula->data_matricula?->toDateString() ?? $matricula->created_at?->toDateString(),
                'tipo' => 'Matrícula',
                'titulo' => 'Matrícula em '.($turma->nome ?? 'turma'),
                'detalhe' => trim(collect([$turma->anoLetivo->ano ?? null, $turma->etapa->nome ?? null])->filter()->implode(' · ')) ?: null,
                'cor' => 'success',
            ]);
        }

        Ocorrencia::query()->where('aluno_id', $aluno->id)->get()->each(function (Ocorrencia $o) use ($eventos) {
            $eventos->push([
                'data' => $o->data?->toDateString() ?? $o->created_at?->toDateString(),
                'tipo' => 'Ocorrência',
                'titulo' => \App\Models\Ocorrencia::TIPOS[$o->tipo] ?? $o->tipo,
                'detalhe' => \Illuminate\Support\Str::limit($o->descricao, 120),
                'cor' => $o->gravidade === 'grave' ? 'danger' : 'warning',
            ]);
        });

        $aluno->documentos->each(function (AlunoDocumento $doc) use ($eventos) {
            $eventos->push([
                'data' => $doc->created_at?->toDateString(),
                'tipo' => 'Documento',
                'titulo' => 'Documento: '.$doc->titulo,
                'detalhe' => \App\Models\AlunoDocumento::TIPOS[$doc->tipo] ?? $doc->tipo,
                'cor' => 'info',
            ]);
        });

        return $eventos
            ->filter(fn ($e) => $e['data'])
            ->sortByDesc('data')
            ->take(40)
            ->values();
    }

    /**
     * Frequencia geral do aluno: presencas / total de registros
     * em todas as matriculas.
     */
    private function frequenciaGeral(Collection $frequencias): ?float
    {
        $presentes = 0;
        $total = 0;

        foreach ($frequencias as $porStatus) {
            foreach ($porStatus as $status => $qtd) {
                $total += (int) $qtd;
                if ($status === 'presente') {
                    $presentes += (int) $qtd;
                }
            }
        }

        return $total > 0 ? round($presentes / $total * 100, 1) : null;
    }
}
