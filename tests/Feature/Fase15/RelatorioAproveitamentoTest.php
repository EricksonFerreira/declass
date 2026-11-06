<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Avaliacao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupAproveitamento($empresa, $admin): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Aproveitamento', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => 'Fundamental', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '5º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create([
        'nome' => 'Matemática', 'empresa_id' => $empresa->id,
    ]);

    // 2 alunos com notas: um aprovado (media 8), um reprovado (media 4)
    $chamada = 0;
    foreach ([['Aprovado', 8.0], ['Reprovado', 4.0]] as [$nome, $nota]) {
        $chamada++;
        $pessoa = Pessoa::create(['nome' => "Aluno {$nome}", 'empresa_id' => $empresa->id]);
        $aluno = Aluno::create([
            'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
            'registro_academico' => 'RA-'.$nome, 'empresa_id' => $empresa->id,
        ]);
        $matricula = Matricula::create([
            'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
            'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
            'numero_chamada' => $chamada, 'empresa_id' => $empresa->id,
        ]);

        $avaliacao = Avaliacao::create([
            'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
            'user_id' => $admin->id,
            'tipo' => 'prova', 'nome' => 'Prova 1',
            'data' => now()->subDays(10)->toDateString(),
            'nota_maxima' => 10, 'empresa_id' => $empresa->id,
        ]);
        Nota::create([
            'avaliacao_id' => $avaliacao->id, 'matricula_id' => $matricula->id,
            'nota' => $nota, 'empresa_id' => $empresa->id,
        ]);
    }

    // Aluno sem notas
    $pessoa3 = Pessoa::create(['nome' => 'Sem Notas', 'empresa_id' => $empresa->id]);
    $aluno3 = Aluno::create([
        'pessoa_id' => $pessoa3->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-SEM', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $aluno3->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 3, 'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'turma', 'anoLetivo', 'etapa');
}

test('relatorio consolida media aprovacao e cobertura por turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    setupAproveitamento($empresa, $admin);
    $user = usuarioTenant($empresa, 'diretor');

    $this->actingAs($user)->get(route('relatorio_aproveitamento.index'))->assertOk();

    $service = app(\App\Services\RelatorioAproveitamentoService::class);
    $dados = $service->porTurma([]);

    expect($dados['resumo']['turmas'])->toBe(1)
        ->and($dados['resumo']['alunos'])->toBe(3)
        ->and($dados['resumo']['alunos_com_media'])->toBe(2)
        ->and($dados['resumo']['aprovados'])->toBe(1)
        ->and($dados['resumo']['pct_aprovacao'])->toBe(50.0)
        ->and($dados['resumo']['media_geral'])->toBe(6.0);

    $linha = $dados['linhas']->first();
    expect($linha['turma'])->toBe('5º Ano A')
        ->and($linha['alunos_com_nota'])->toBe(2)
        ->and($linha['notas_lancadas'])->toBe(2);
});

test('filtro por escola limita o relatorio', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupAproveitamento($empresa, $admin);

    // Segunda escola com turma e notas proprias
    $escola2 = Escola::create(['nome' => 'Escola B', 'empresa_id' => $empresa->id]);
    $ano2 = AnoLetivo::create(['escola_id' => $escola2->id, 'ano' => 2026, 'empresa_id' => $empresa->id]);
    $etapa2 = Etapa::create(['nome' => 'Medio', 'segmento' => 'medio', 'empresa_id' => $empresa->id]);
    $turma2 = Turma::create([
        'escola_id' => $escola2->id, 'ano_letivo_id' => $ano2->id,
        'etapa_id' => $etapa2->id, 'nome' => '1º Ano', 'turno' => 'vespertino',
        'empresa_id' => $empresa->id,
    ]);
    $disc2 = Disciplina::create(['nome' => 'Física', 'empresa_id' => $empresa->id]);
    $pessoa = Pessoa::create(['nome' => 'Aluno B', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola2->id,
        'registro_academico' => 'RA-B', 'empresa_id' => $empresa->id,
    ]);
    $mat = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma2->id,
        'ano_letivo_id' => $ano2->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);
    $av = Avaliacao::create([
        'turma_id' => $turma2->id, 'disciplina_id' => $disc2->id,
        'user_id' => $admin->id,
        'tipo' => 'prova', 'nome' => 'Prova',
        'data' => now()->subDay()->toDateString(),
        'nota_maxima' => 10, 'empresa_id' => $empresa->id,
    ]);
    Nota::create([
        'avaliacao_id' => $av->id, 'matricula_id' => $mat->id,
        'nota' => 9.0, 'empresa_id' => $empresa->id,
    ]);

    $user = usuarioTenant($empresa, 'diretor');

    $this->actingAs($user)
        ->get(route('relatorio_aproveitamento.index', ['escola_id' => $ids['escola']->id]))
        ->assertOk()
        ->assertSee('5º Ano A');

    // o dropdown lista todas as turmas — o filtro efetivo e validado no service
    $dados = app(\App\Services\RelatorioAproveitamentoService::class)
        ->porTurma(['escola_id' => $ids['escola']->id]);
    expect($dados['linhas'])->toHaveCount(1)
        ->and($dados['linhas']->first()['turma'])->toBe('5º Ano A');
});

test('professor nao acessa o relatorio', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    setupAproveitamento($empresa, $admin);
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)
        ->get(route('relatorio_aproveitamento.index'))
        ->assertForbidden();
});

test('dados de outro tenant nao aparecem', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    setupAproveitamento($empresa, $admin);

    $outro = tenant(['nome' => 'Rede B', 'slug' => 'rede-b-aproveitamento']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $userB = usuarioTenant($outro, 'diretor');

    $service = app(\App\Services\RelatorioAproveitamentoService::class);
    $dados = $service->porTurma([]);

    expect($dados['resumo']['turmas'])->toBe(0)
        ->and($dados['linhas'])->toBeEmpty();
});
