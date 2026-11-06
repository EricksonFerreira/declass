<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\PlanoMensalidade;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupRematricula($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Rematrícula', 'empresa_id' => $empresa->id]);
    $anoOrigem = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2025, 'empresa_id' => $empresa->id,
    ]);
    $anoDestino = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => 'Fundamental', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turmaOrigem = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoOrigem->id,
        'etapa_id' => $etapa->id, 'nome' => '5º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $turmaDestino = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoDestino->id,
        'etapa_id' => $etapa->id, 'nome' => '6º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $turmaDestinoB = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoDestino->id,
        'etapa_id' => $etapa->id, 'nome' => '6º Ano B', 'turno' => 'vespertino',
        'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'anoOrigem', 'anoDestino', 'etapa', 'turmaOrigem', 'turmaDestino', 'turmaDestinoB');
}

function alunoComMatricula($empresa, $ids, string $nome, int $chamada = 1): Matricula
{
    $pessoa = Pessoa::create(['nome' => $nome, 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-'.strtoupper(substr(md5($nome), 0, 6)),
        'empresa_id' => $empresa->id,
    ]);

    return Matricula::create([
        'empresa_id' => $empresa->id,
        'aluno_id' => $aluno->id,
        'turma_id' => $ids['turmaOrigem']->id,
        'ano_letivo_id' => $ids['anoOrigem']->id,
        'status' => 'ativa',
        'numero_chamada' => $chamada,
    ]);
}

function urlRematricula($ids): string
{
    return '/admin/matricula/rematricula?ano_origem='.$ids['anoOrigem']->id.'&ano_destino='.$ids['anoDestino']->id;
}

test('secretaria acessa wizard e ve alunos elegiveis', function () {
    $empresa = tenant();
    $ids = setupRematricula($empresa);
    $matricula = alunoComMatricula($empresa, $ids, 'Ana Rematricula', 1);
    $secretaria = usuarioTenant($empresa, 'secretario');

    $this->actingAs($secretaria)
        ->get('/admin/matricula/rematricula')
        ->assertOk()
        ->assertSee('Ano letivo de origem');

    $this->actingAs($secretaria)
        ->get(urlRematricula($ids))
        ->assertOk()
        ->assertSee('Ana Rematricula')
        ->assertSee('6º Ano A');
});

test('rematricula em lote cria matriculas no ano destino', function () {
    $empresa = tenant();
    $ids = setupRematricula($empresa);
    $plano = PlanoMensalidade::create([
        'empresa_id' => $empresa->id, 'nome' => 'Integral', 'valor' => 1500, 'ativo' => 'S',
    ]);
    $m1 = alunoComMatricula($empresa, $ids, 'Aluno Um', 1);
    $m1->update(['plano_mensalidade_id' => $plano->id]);
    $m2 = alunoComMatricula($empresa, $ids, 'Aluno Dois', 2);
    $secretaria = usuarioTenant($empresa, 'secretario');

    $this->actingAs($secretaria)
        ->post('/admin/matricula/rematricula', [
            'ano_origem' => $ids['anoOrigem']->id,
            'ano_destino' => $ids['anoDestino']->id,
            'itens' => [
                $m1->id => $ids['turmaDestino']->id,
                $m2->id => $ids['turmaDestinoB']->id,
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // duas novas matriculas ativas no ano destino
    $this->assertDatabaseHas('matriculas', [
        'aluno_id' => $m1->aluno_id, 'turma_id' => $ids['turmaDestino']->id,
        'ano_letivo_id' => $ids['anoDestino']->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'plano_mensalidade_id' => $plano->id,
    ]);
    $this->assertDatabaseHas('matriculas', [
        'aluno_id' => $m2->aluno_id, 'turma_id' => $ids['turmaDestinoB']->id,
        'ano_letivo_id' => $ids['anoDestino']->id, 'status' => 'ativa',
        'numero_chamada' => 1,
    ]);
});

test('aluno ja matriculado no destino e ignorado', function () {
    $empresa = tenant();
    $ids = setupRematricula($empresa);
    $m1 = alunoComMatricula($empresa, $ids, 'Ja Rematriculado', 1);
    // aluno ja tem matricula no ano destino
    Matricula::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $m1->aluno_id,
        'turma_id' => $ids['turmaDestino']->id, 'ano_letivo_id' => $ids['anoDestino']->id,
        'status' => 'ativa', 'numero_chamada' => 5,
    ]);
    $secretaria = usuarioTenant($empresa, 'secretario');

    $this->actingAs($secretaria)
        ->post('/admin/matricula/rematricula', [
            'ano_origem' => $ids['anoOrigem']->id,
            'ano_destino' => $ids['anoDestino']->id,
            'itens' => [$m1->id => $ids['turmaDestinoB']->id],
        ])
        ->assertSessionHas('ignoradas');

    // continua existindo apenas a matricula destino original
    expect(Matricula::where('aluno_id', $m1->aluno_id)
        ->where('ano_letivo_id', $ids['anoDestino']->id)->count())->toBe(1);
});

test('turma de outra escola e rejeitada como ignorada', function () {
    $empresa = tenant();
    $ids = setupRematricula($empresa);
    $m1 = alunoComMatricula($empresa, $ids, 'Aluno Escola', 1);

    // segunda escola com ano letivo proprio
    $escola2 = Escola::create(['nome' => 'Escola 2', 'empresa_id' => $empresa->id]);
    $ano2 = AnoLetivo::create(['escola_id' => $escola2->id, 'ano' => 2026, 'empresa_id' => $empresa->id]);
    $turmaFora = Turma::create([
        'escola_id' => $escola2->id, 'ano_letivo_id' => $ano2->id,
        'etapa_id' => $ids['etapa']->id, 'nome' => 'Turma Externa', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $secretaria = usuarioTenant($empresa, 'secretario');

    $this->actingAs($secretaria)
        ->post('/admin/matricula/rematricula', [
            'ano_origem' => $ids['anoOrigem']->id,
            'ano_destino' => $ids['anoDestino']->id,
            'itens' => [$m1->id => $turmaFora->id],
        ])
        ->assertSessionHas('ignoradas');

    expect(Matricula::where('ano_letivo_id', $ids['anoDestino']->id)->count())->toBe(0);
});

test('anos de escolas diferentes retornam erro de validacao', function () {
    $empresa = tenant();
    $ids = setupRematricula($empresa);
    $secretaria = usuarioTenant($empresa, 'secretario');

    $escola2 = Escola::create(['nome' => 'Escola B', 'empresa_id' => $empresa->id]);
    $anoOutro = AnoLetivo::create(['escola_id' => $escola2->id, 'ano' => 2026, 'empresa_id' => $empresa->id]);

    $this->actingAs($secretaria)
        ->get('/admin/matricula/rematricula?ano_origem='.$ids['anoOrigem']->id.'&ano_destino='.$anoOutro->id)
        ->assertOk()
        ->assertSee('mesma escola');
});

test('coordenador nao acessa rematricula em lote', function () {
    $empresa = tenant();
    $ids = setupRematricula($empresa);
    $coordenador = usuarioTenant($empresa, 'coordenador_pedagogico');

    $this->actingAs($coordenador)
        ->get('/admin/matricula/rematricula')
        ->assertForbidden();
});

test('rematricula tem escopo de tenant', function () {
    $empresa = tenant();
    $ids = setupRematricula($empresa);
    $m1 = alunoComMatricula($empresa, $ids, 'Aluno Tenant A', 1);
    $secretaria = usuarioTenant($empresa, 'secretario');

    // outro tenant nao ve os alunos do primeiro
    $outro = tenant(['nome' => 'Rede B', 'slug' => 'rede-b']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $secretariaB = usuarioTenant($outro, 'secretario');
    $idsB = setupRematricula($outro);

    $this->actingAs($secretariaB)
        ->get(urlRematricula($idsB))
        ->assertOk()
        ->assertDontSee('Aluno Tenant A');

    // e nao consegue rematricular matricula de outro tenant
    $this->actingAs($secretariaB)
        ->post('/admin/matricula/rematricula', [
            'ano_origem' => $idsB['anoOrigem']->id,
            'ano_destino' => $idsB['anoDestino']->id,
            'itens' => [$m1->id => $idsB['turmaDestino']->id],
        ])
        ->assertSessionHas('ignoradas');
});
