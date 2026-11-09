<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Empresa;
use App\Models\Escola;
use App\Models\Matricula;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Consolida os numeros da rede para o painel do superadmin: uma linha
 * por empresa com totais de escolas, alunos, matriculas, turmas,
 * professores e usuarios. Ignora o escopo de tenant — e justamente
 * para o superadmin enxergar todas as empresas.
 */
class PainelRedeService
{
    /**
     * @return array{kpis: array, linhas: \Illuminate\Support\Collection}
     */
    public function dados(?int $ano = null): array
    {
        $empresas = Empresa::query()->orderBy('nome')->get([
            'id', 'nome', 'slug', 'dominio', 'logo_path', 'site_ativo', 'ativo',
        ]);

        $porEmpresa = [
            'escolas' => $this->contarPorEmpresa(Escola::query()),
            'alunos' => $this->contarPorEmpresa(Aluno::query()->where('ativo', 1)),
            'matriculas' => $this->contarPorEmpresa(
                Matricula::query()->where('matriculas.status', 'ativa')
                    ->when($ano, function ($q) use ($ano) {
                        $q->join('anos_letivos', 'matriculas.ano_letivo_id', '=', 'anos_letivos.id')
                            ->where('anos_letivos.ano', $ano)
                            ->select('matriculas.empresa_id');
                    })
            ),
            'turmas' => $this->contarPorEmpresa(
                Turma::query()
                    ->when($ano, function ($q) use ($ano) {
                        $q->join('anos_letivos', 'turmas.ano_letivo_id', '=', 'anos_letivos.id')
                            ->where('anos_letivos.ano', $ano)
                            ->select('turmas.empresa_id');
                    })
            ),
            'professores' => $this->contarPorEmpresa(
                User::query()->join('roles', 'users.role_id', '=', 'roles.id')
                    ->where('roles.name', 'professor')
                    ->select('users.empresa_id')
            ),
            'usuarios' => $this->contarPorEmpresa(User::query()),
        ];

        $linhas = $empresas->map(function (Empresa $empresa) use ($porEmpresa) {
            return (object) [
                'empresa' => $empresa,
                'escolas' => $porEmpresa['escolas'][$empresa->id] ?? 0,
                'alunos' => $porEmpresa['alunos'][$empresa->id] ?? 0,
                'matriculas' => $porEmpresa['matriculas'][$empresa->id] ?? 0,
                'turmas' => $porEmpresa['turmas'][$empresa->id] ?? 0,
                'professores' => $porEmpresa['professores'][$empresa->id] ?? 0,
                'usuarios' => $porEmpresa['usuarios'][$empresa->id] ?? 0,
            ];
        });

        return [
            'kpis' => [
                'empresas_ativas' => $empresas->where('ativo', 'S')->count(),
                'escolas' => array_sum($porEmpresa['escolas']),
                'alunos' => array_sum($porEmpresa['alunos']),
                'matriculas' => array_sum($porEmpresa['matriculas']),
                'turmas' => array_sum($porEmpresa['turmas']),
                'professores' => array_sum($porEmpresa['professores']),
                'usuarios' => array_sum($porEmpresa['usuarios']),
            ],
            'linhas' => $linhas,
        ];
    }

    /** Anos letivos distintos na rede, para o filtro. */
    public function anosDisponiveis(): array
    {
        return DB::table('anos_letivos')
            ->selectRaw('DISTINCT ano')
            ->orderByDesc('ano')
            ->pluck('ano')
            ->map(fn ($a) => (int) $a)
            ->all();
    }

    /**
     * COUNT(*) agrupado por empresa_id, sem o escopo de tenant.
     *
     * @return array<int, int>
     */
    private function contarPorEmpresa($query): array
    {
        $table = $query->getModel()->getTable();

        return $query
            ->withoutGlobalScope('empresa')
            ->groupBy($table.'.empresa_id')
            ->get([$table.'.empresa_id', DB::raw('COUNT(*) as c')])
            ->pluck('c', 'empresa_id')
            ->all();
    }
}
