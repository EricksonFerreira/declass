<?php

use App\Models\Escola;
use App\Models\Material;
use App\Models\MovimentacaoEstoque;
use App\Support\EmpresaContext;

function movimentacaoCentralSetup($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Mov', 'empresa_id' => $empresa->id]);
    $material = Material::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'nome' => 'Caderno universitário', 'categoria' => 'papelaria',
        'unidade_medida' => 'un', 'estoque_atual' => 50, 'estoque_minimo' => 10,
        'ativo' => 'S',
    ]);

    return compact('escola', 'material');
}

test('tela central de movimentacao renderiza materiais e historico', function () {
    $empresa = tenant();
    $ids = movimentacaoCentralSetup($empresa);
    $user = usuarioTenant($empresa, 'auxiliar_secretaria');

    $this->actingAs($user)->get('/admin/material/movimentacao')
        ->assertOk()
        ->assertSee('Caderno universitário')
        ->assertSee('Nova movimentação')
        ->assertSee('Movimentações recentes');
});

test('tela central registra entrada saida e ajuste pelo material_id', function () {
    $empresa = tenant();
    $ids = movimentacaoCentralSetup($empresa);
    $user = usuarioTenant($empresa, 'auxiliar_secretaria');

    // entrada de 20 -> saldo 70
    $this->actingAs($user)->post('/admin/material/movimentacao', [
        'material_id' => $ids['material']->id,
        'tipo' => 'entrada', 'quantidade' => 20,
        'motivo' => 'Reposição', 'data' => '2026-03-10',
    ])->assertRedirect(route('material.movimentacao.index'));

    expect((float) $ids['material']->fresh()->estoque_atual)->toBe(70.0);

    // ajuste para 15 (saldo absoluto)
    $this->actingAs($user)->post('/admin/material/movimentacao', [
        'material_id' => $ids['material']->id,
        'tipo' => 'ajuste', 'quantidade' => 15, 'motivo' => 'Inventário',
    ]);

    expect((float) $ids['material']->fresh()->estoque_atual)->toBe(15.0);

    $movs = MovimentacaoEstoque::where('material_id', $ids['material']->id)->orderBy('id')->get();
    expect($movs)->toHaveCount(2)
        ->and($movs[0]->tipo)->toBe('entrada')
        ->and($movs[0]->saldo_anterior)->toBe('50.00')
        ->and($movs[0]->saldo_novo)->toBe('70.00')
        ->and($movs[0]->escola_id)->toBe($ids['escola']->id)
        ->and($movs[1]->tipo)->toBe('ajuste')
        ->and($movs[1]->quantidade)->toBe('15.00');
});

test('saida maior que o saldo volta com erro de validacao', function () {
    $empresa = tenant();
    $ids = movimentacaoCentralSetup($empresa);
    $user = usuarioTenant($empresa, 'auxiliar_secretaria');

    $this->actingAs($user)->post('/admin/material/movimentacao', [
        'material_id' => $ids['material']->id,
        'tipo' => 'saida', 'quantidade' => 999,
    ])->assertSessionHasErrors('quantidade');

    expect((float) $ids['material']->fresh()->estoque_atual)->toBe(50.0);
    expect(MovimentacaoEstoque::count())->toBe(0);
});

test('material de outro tenant retorna 404 na movimentacao central', function () {
    $empresa = tenant();
    $empresaB = tenant();
    $ids = movimentacaoCentralSetup($empresa);
    $user = usuarioTenant($empresa, 'auxiliar_secretaria');

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    $escolaB = Escola::create(['nome' => 'Escola B', 'empresa_id' => $empresaB->id]);
    $materialB = Material::create([
        'empresa_id' => $empresaB->id, 'escola_id' => $escolaB->id,
        'nome' => 'Material B', 'categoria' => 'outro',
        'unidade_medida' => 'un', 'estoque_atual' => 10, 'ativo' => 'S',
    ]);

    $this->actingAs($user)->post('/admin/material/movimentacao', [
        'material_id' => $materialB->id, 'tipo' => 'entrada', 'quantidade' => 5,
    ])->assertNotFound();

    // e nao aparece no select da tela
    $this->actingAs($user)->get('/admin/material/movimentacao')
        ->assertOk()
        ->assertDontSee('Material B');
});

test('quem so visualiza materiais nao acessa a tela de movimentacao', function () {
    $empresa = tenant();
    movimentacaoCentralSetup($empresa);

    $financeiro = usuarioTenant($empresa, 'financeiro');
    $this->actingAs($financeiro)->get('/admin/material/movimentacao')->assertForbidden();
    $this->actingAs($financeiro)->post('/admin/material/movimentacao', [
        'material_id' => 1, 'tipo' => 'entrada', 'quantidade' => 1,
    ])->assertForbidden();
});
