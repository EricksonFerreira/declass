<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\PeriodoLetivo;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Services\SetupChecklistService;
use App\Support\EmpresaContext;

function checklistFixture($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Unidade', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026,
        'status' => 'letivo', 'periodicidade' => 'semestral',
        'empresa_id' => $empresa->id,
    ]);
    $periodo = PeriodoLetivo::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'nome' => '1º Semestre', 'ordem' => 1, 'ativo' => 'S',
        'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create(['nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'empresa_id' => $empresa->id]);
    $turma->disciplinas()->attach($disciplina->id, ['carga_horaria_semanal' => 5]);

    return compact('escola', 'anoLetivo', 'periodo', 'turma', 'disciplina');
}

test('home exibe o checklist de primeiros passos para tenant novo', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->get('/admin/home')
        ->assertOk()
        ->assertSee('Primeiros passos')
        ->assertSee('0/6');
});

test('checklist reflete o progresso conforme a configuracao avanca', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $service = app(SetupChecklistService::class);

    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $inicial = $service->itens($admin->fresh());
    expect($inicial['feitos'])->toBe(0)->and($inicial['progresso'])->toBe(0);

    ['anoLetivo' => $anoLetivo, 'turma' => $turma, 'disciplina' => $disciplina] = checklistFixture($empresa);

    // faltam apenas atribuicao de professor e matricula
    $meio = $service->itens($admin->fresh());
    expect($meio['feitos'])->toBe(4)
        ->and($meio['total'])->toBe(6)
        ->and(collect($meio['itens'])->firstWhere('label', 'Atribuir professores')['feito'])->toBeFalse();

    $prof = usuarioTenant($empresa, 'professor');
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $disciplina->id, 'user_id' => $prof->id,
        'papel' => 'titular', 'ativo' => 'S',
    ]);

    $quase = $service->itens($admin->fresh());
    expect($quase['feitos'])->toBe(5);
});

test('checklist some da home quando a configuracao esta completa', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['escola' => $escola, 'anoLetivo' => $anoLetivo, 'turma' => $turma, 'disciplina' => $disciplina] = checklistFixture($empresa);

    $prof = usuarioTenant($empresa, 'professor');
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $disciplina->id, 'user_id' => $prof->id,
        'papel' => 'titular', 'ativo' => 'S',
    ]);

    $pessoa = Pessoa::create(['empresa_id' => $empresa->id, 'nome' => 'Aluno Teste']);
    $aluno = Aluno::create([
        'empresa_id' => $empresa->id, 'pessoa_id' => $pessoa->id,
        'escola_id' => $escola->id,
    ]);
    Matricula::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
    ]);

    $this->actingAs($admin)->get('/admin/home')
        ->assertOk()
        ->assertDontSee('Primeiros passos');
});

test('superadmin na visao consolidada nao ve o checklist', function () {
    $root = superadmin();

    $this->actingAs($root)->get('/admin/home')
        ->assertOk()
        ->assertDontSee('Primeiros passos');
});
