<?php

use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Horario;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupVinculo33($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id, 'ativo' => 'S',
    ]);
    $etapa = Etapa::create([
        'nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $matriz = Horario::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'turno' => 'matutino', 'nome' => 'Matutino Regular', 'ativo' => 'S',
    ]);
    $matrizVespertino = Horario::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'turno' => 'vespertino', 'nome' => 'Vespertino Regular', 'ativo' => 'S',
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'matriz', 'matrizVespertino');
}

test('gestao vincula matriz da escola e turno a turma sem grade', function () {
    $empresa = tenant();
    $secretario = usuarioTenant($empresa, 'secretario');
    $ids = setupVinculo33($empresa);

    $this->actingAs($secretario)
        ->put(route('horarios.vincular', $ids['turma']->id), ['horario_id' => $ids['matriz']->id])
        ->assertRedirect();

    expect($ids['turma']->fresh()->horario_id)->toBe($ids['matriz']->id);
});

test('vincular rejeita matriz de outro turno', function () {
    $empresa = tenant();
    $secretario = usuarioTenant($empresa, 'secretario');
    $ids = setupVinculo33($empresa);

    $this->actingAs($secretario)
        ->put(route('horarios.vincular', $ids['turma']->id), ['horario_id' => $ids['matrizVespertino']->id])
        ->assertSessionHasErrors('horario_id');

    expect($ids['turma']->fresh()->horario_id)->toBeNull();
});

test('listagem mostra matrizes disponiveis no modal para turma sem grade', function () {
    $empresa = tenant();
    $secretario = usuarioTenant($empresa, 'secretario');
    $ids = setupVinculo33($empresa);

    $this->actingAs($secretario)
        ->get(route('horarios.index'))
        ->assertOk()
        ->assertSee('modal-vincular-matriz')
        ->assertSee('sem matriz');
});

test('professor nao vincula matriz a turma', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupVinculo33($empresa);

    $this->actingAs($professor)
        ->put(route('horarios.vincular', $ids['turma']->id), ['horario_id' => $ids['matriz']->id])
        ->assertForbidden();
});
