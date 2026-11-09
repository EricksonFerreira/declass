<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupRede($empresa, string $nomeEscola, int $ano, int $qtdMatriculas = 1): void
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => $nomeEscola, 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => $ano, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => 'Etapa '.$nomeEscola, 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => 'Turma '.$nomeEscola, 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    for ($i = 0; $i < $qtdMatriculas; $i++) {
        $pessoa = Pessoa::create(['nome' => 'Aluno '.$i, 'empresa_id' => $empresa->id]);
        $aluno = Aluno::create([
            'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
            'registro_academico' => 'RA-'.$nomeEscola.$i, 'empresa_id' => $empresa->id, 'ativo' => 1,
        ]);
        Matricula::create([
            'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
            'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
            'numero_chamada' => $i + 1, 'empresa_id' => $empresa->id,
        ]);
    }
}

test('superadmin ve a rede consolidada com uma linha por empresa', function () {
    $a = tenant(['nome' => 'Empresa Alfa']);
    $b = tenant(['nome' => 'Empresa Beta']);
    setupRede($a, 'Escola A1', (int) date('Y'), 2);
    setupRede($b, 'Escola B1', (int) date('Y'), 1);

    $response = $this->actingAs(superadmin())->get(route('rede.index'));

    $response->assertOk()
        ->assertSee('Empresa Alfa')
        ->assertSee('Empresa Beta')
        ->assertSee('Empresas da rede');

    $dados = $response->viewData('dados');
    $alfa = $dados['linhas']->firstWhere('empresa.id', $a->id);
    $beta = $dados['linhas']->firstWhere('empresa.id', $b->id);

    expect($alfa->escolas)->toBe(1)
        ->and($alfa->matriculas)->toBe(2)
        ->and($alfa->turmas)->toBe(1)
        ->and($beta->matriculas)->toBe(1)
        ->and($dados['kpis']['escolas'])->toBe(2)
        ->and($dados['kpis']['matriculas'])->toBe(3);
});

test('filtro por ano restringe matriculas e turmas', function () {
    $a = tenant();
    setupRede($a, 'Escola Ano', (int) date('Y'), 1);

    $response = $this->actingAs(superadmin())
        ->get(route('rede.index', ['ano' => 1999]));

    $response->assertOk();
    $dados = $response->viewData('dados');
    $linha = $dados['linhas']->firstWhere('empresa.id', $a->id);

    expect($linha->matriculas)->toBe(0)
        ->and($linha->turmas)->toBe(0)
        ->and($linha->alunos)->toBe(1);
});

test('usuario de tenant nao acessa o painel da rede', function () {
    $empresa = tenant();
    $this->actingAs(usuarioTenant($empresa, 'administrador'))
        ->get(route('rede.index'))
        ->assertForbidden();
});
