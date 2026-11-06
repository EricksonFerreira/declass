<?php

namespace App\Services;

use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Matricula;
use App\Models\PeriodoLetivo;
use App\Models\Turma;
use App\Models\User;
use App\Support\EmpresaContext;

/**
 * Checklist de configuracao inicial do tenant: guia o usuario
 * pelos passos minimos para colocar o ano letivo para funcionar
 * (ano letivo -> periodos -> turmas -> disciplinas -> professores
 * -> matriculas). Some da home quando tudo esta concluido.
 *
 * As contagens respeitam os escopos globais de tenant e de escola:
 * com uma escola especifica no seletor, o checklist mostra o
 * progresso daquela unidade.
 */
class SetupChecklistService
{
    /**
     * @return array{itens: array<int, array{label: string, descricao: string, url: string, feito: bool}>, feitos: int, total: int, progresso: int}|null
     */
    public function itens(User $user): ?array
    {
        // Visao consolidada do superadmin nao tem tenant ativo.
        if (app(EmpresaContext::class)->empresaId() === null) {
            return null;
        }

        $candidatos = [
            [
                'permissao' => 'ano_letivo.index',
                'label' => 'Criar o ano letivo',
                'descricao' => 'Defina ano, datas e periodicidade (bimestre, trimestre, semestre).',
                'url' => 'ano_letivo.index',
                'feito' => AnoLetivo::query()->exists(),
            ],
            [
                'permissao' => 'periodo_letivo.index',
                'label' => 'Gerar os períodos letivos',
                'descricao' => 'Os períodos são criados automaticamente pela periodicidade do ano.',
                'url' => 'periodo_letivo.index',
                'feito' => PeriodoLetivo::query()->exists(),
            ],
            [
                'permissao' => 'turma.index',
                'label' => 'Criar as turmas',
                'descricao' => 'Use a geração em massa no planejamento do ano para criar A, B, C... de uma vez.',
                'url' => 'turma.index',
                'feito' => Turma::query()->exists(),
            ],
            [
                'permissao' => 'turma.index',
                'label' => 'Ofertar disciplinas',
                'descricao' => 'Defina quais disciplinas cada turma cursa e a carga horária.',
                'url' => 'turma.index',
                'feito' => Turma::query()->whereHas('disciplinas')->exists(),
            ],
            [
                'permissao' => 'turma.index',
                'label' => 'Atribuir professores',
                'descricao' => 'Vincule os professores às turmas e disciplinas.',
                'url' => 'turma.index',
                'feito' => Atribuicao::query()->exists(),
            ],
            [
                'permissao' => 'matricula.index',
                'label' => 'Matricular alunos',
                'descricao' => 'Matricule alunos manualmente ou aprove as solicitações da fila.',
                'url' => 'matricula.index',
                'feito' => Matricula::query()->exists(),
            ],
        ];

        $itens = collect($candidatos)
            ->filter(fn (array $item) => $user->hasPermissao($item['permissao']))
            ->map(fn (array $item) => [
                'label' => $item['label'],
                'descricao' => $item['descricao'],
                'url' => $item['url'],
                'feito' => $item['feito'],
            ])
            ->values()
            ->all();

        if ($itens === []) {
            return null;
        }

        $total = count($itens);
        $feitos = count(array_filter($itens, fn (array $item) => $item['feito']));

        return [
            'itens' => $itens,
            'feitos' => $feitos,
            'total' => $total,
            'progresso' => (int) round($feitos / $total * 100),
        ];
    }
}
