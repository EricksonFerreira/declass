<?php

use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\PeriodoLetivo;
use App\Models\Turma;
use App\Support\EmpresaContext;

function loteFixture($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Unidade', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026,
        'data_inicio' => '2026-02-01', 'data_fim' => '2026-12-15',
        'status' => 'letivo', 'periodicidade' => 'bimestral',
        'empresa_id' => $empresa->id,
    ]);
    $periodo = PeriodoLetivo::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'nome' => '1º Bimestre', 'ordem' => 1, 'ativo' => 'S',
        'empresa_id' => $empresa->id,
    ]);
    $etapa1 = Etapa::create(['nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id]);
    $etapa2 = Etapa::create(['nome' => '2º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id]);

    return compact('escola', 'anoLetivo', 'periodo', 'etapa1', 'etapa2');
}

test('show do periodo exibe dados e turmas do ano letivo', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['periodo' => $periodo, 'anoLetivo' => $anoLetivo, 'etapa1' => $etapa1] = loteFixture($empresa);

    Turma::create([
        'escola_id' => $anoLetivo->escola_id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa1->id, 'nome' => '1º Ano EF A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $this->actingAs($user)->get("/admin/periodo-letivo/{$periodo->id}")
        ->assertOk()
        ->assertSee('1º Bimestre')
        ->assertSee('1º Ano EF A')
        ->assertSee('Geração de turmas em massa');
});

test('geracao em lote cria etapas x sufixos x turnos com nome do turno', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['anoLetivo' => $anoLetivo, 'etapa1' => $etapa1, 'etapa2' => $etapa2] = loteFixture($empresa);

    $this->actingAs($user)->post('/admin/turma/gerar-lote', [
        'ano_letivo_id' => $anoLetivo->id,
        'etapas' => [$etapa1->id, $etapa2->id],
        'sufixos' => 'A, B',
        'turnos' => ['matutino', 'vespertino'],
        'sala' => '101',
        'capacidade_maxima' => 40,
    ])->assertRedirect();

    // 2 etapas x 2 sufixos x 2 turnos = 8 turmas
    expect(Turma::where('ano_letivo_id', $anoLetivo->id)->count())->toBe(8);

    $turma = Turma::where('ano_letivo_id', $anoLetivo->id)
        ->where('nome', '1º Ano EF A — Matutino')->first();

    expect($turma)->not->toBeNull()
        ->and($turma->turno)->toBe('matutino')
        ->and($turma->sala)->toBe('101')
        ->and($turma->capacidade_maxima)->toBe(40)
        ->and($turma->escola_id)->toBe($anoLetivo->escola_id);
});

test('turno unico nao sufixa o nome', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['anoLetivo' => $anoLetivo, 'etapa1' => $etapa1] = loteFixture($empresa);

    $this->actingAs($user)->post('/admin/turma/gerar-lote', [
        'ano_letivo_id' => $anoLetivo->id,
        'etapas' => [$etapa1->id],
        'sufixos' => 'A, B',
        'turnos' => ['matutino'],
    ]);

    expect(Turma::where('ano_letivo_id', $anoLetivo->id)->pluck('nome')->all())
        ->toBe(['1º Ano EF A', '1º Ano EF B']);
});

test('nomes existentes sao pulados e reportados', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['anoLetivo' => $anoLetivo, 'etapa1' => $etapa1, 'escola' => $escola] = loteFixture($empresa);

    Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa1->id, 'nome' => '1º Ano EF A',
        'empresa_id' => $empresa->id,
    ]);

    $this->actingAs($user)->post('/admin/turma/gerar-lote', [
        'ano_letivo_id' => $anoLetivo->id,
        'etapas' => [$etapa1->id],
        'sufixos' => 'A, B',
    ])->assertSessionHas('success', fn ($msg) => str_contains($msg, '1 turma(s) criada(s)') && str_contains($msg, '1º Ano EF A'));

    expect(Turma::where('ano_letivo_id', $anoLetivo->id)->count())->toBe(2);
});

test('[PADRAO] validacao exige etapas e sufixos', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['anoLetivo' => $anoLetivo] = loteFixture($empresa);

    $this->actingAs($user)->post('/admin/turma/gerar-lote', [
        'ano_letivo_id' => $anoLetivo->id,
        'etapas' => [],
        'sufixos' => 'A',
    ])->assertSessionHasErrors('etapas');

    $this->actingAs($user)->post('/admin/turma/gerar-lote', [
        'ano_letivo_id' => $anoLetivo->id,
        'etapas' => [999],
        'sufixos' => 'A',
    ])->assertSessionHasErrors('etapas.0');
});

test('ano letivo de outro tenant e rejeitado', function () {
    $empresa = tenant();
    $empresaB = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['etapa1' => $etapa1] = loteFixture($empresa);

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    $escolaB = Escola::create(['nome' => 'Outra', 'empresa_id' => $empresaB->id]);
    $anoB = AnoLetivo::create([
        'escola_id' => $escolaB->id, 'ano' => 2026, 'empresa_id' => $empresaB->id,
    ]);

    $this->actingAs($user)->post('/admin/turma/gerar-lote', [
        'ano_letivo_id' => $anoB->id,
        'etapas' => [$etapa1->id],
        'sufixos' => 'A',
    ])->assertSessionHasErrors('ano_letivo_id');
});

test('perfil sem permissao de turma nao gera lote', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'professor');
    ['anoLetivo' => $anoLetivo, 'etapa1' => $etapa1] = loteFixture($empresa);

    $this->actingAs($user)->post('/admin/turma/gerar-lote', [
        'ano_letivo_id' => $anoLetivo->id,
        'etapas' => [$etapa1->id],
        'sufixos' => 'A',
    ])->assertForbidden();
});
