<?php

use App\Models\Escola;
use App\Models\Material;
use App\Models\MovimentacaoEstoque;
use App\Support\EmpresaContext;

function setupEstoque($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Estoque', 'empresa_id' => $empresa->id]);

    return compact('escola');
}

function materialDemo($empresa, $escola, array $over = []): Material
{
    return Material::create(array_merge([
        'empresa_id' => $empresa->id,
        'escola_id' => $escola->id,
        'nome' => 'Material Teste',
        'categoria' => 'papelaria',
        'unidade_medida' => 'un',
        'estoque_atual' => 100,
        'estoque_minimo' => 20,
        'ativo' => 'S',
    ], $over));
}

test('secretaria cadastra material e ele aparece na listagem', function () {
    $empresa = tenant();
    $ids = setupEstoque($empresa);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->get(route('material.index'))->assertOk();

    $this->actingAs($user)->post(route('material.store'), [
        'nome' => 'Papel sulfite A4',
        'categoria' => 'papelaria',
        'unidade_medida' => 'pct',
        'escola_id' => $ids['escola']->id,
        'estoque_atual' => 50,
        'estoque_minimo' => 10,
        'localizacao' => 'Almoxarifado',
    ])->assertRedirect(route('material.index'));

    $material = Material::where('nome', 'Papel sulfite A4')->firstOrFail();
    expect($material->empresa_id)->toBe($empresa->id)
        ->and((float) $material->estoque_atual)->toBe(50.0);

    $this->actingAs($user)->get(route('material.index'))
        ->assertOk()
        ->assertSee('Papel sulfite A4');
});

test('entrada e saida atualizam o saldo e geram historico', function () {
    $empresa = tenant();
    $ids = setupEstoque($empresa);
    $material = materialDemo($empresa, $ids['escola']);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->post(route('material.movimentar', $material->id), [
        'tipo' => 'entrada', 'quantidade' => 30, 'motivo' => 'Compra',
    ]);
    expect((float) $material->fresh()->estoque_atual)->toBe(130.0);

    $this->actingAs($user)->post(route('material.movimentar', $material->id), [
        'tipo' => 'saida', 'quantidade' => 40, 'motivo' => 'Uso interno',
    ]);
    expect((float) $material->fresh()->estoque_atual)->toBe(90.0);

    $movs = MovimentacaoEstoque::where('material_id', $material->id)->orderBy('id')->get();
    expect($movs)->toHaveCount(2)
        ->and($movs[0]->saldo_anterior)->toBe('100.00')
        ->and($movs[0]->saldo_novo)->toBe('130.00')
        ->and($movs[1]->saldo_novo)->toBe('90.00')
        ->and($movs[1]->user_id)->toBe($user->id);
});

test('saida maior que o saldo e bloqueada', function () {
    $empresa = tenant();
    $ids = setupEstoque($empresa);
    $material = materialDemo($empresa, $ids['escola']);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->post(route('material.movimentar', $material->id), [
        'tipo' => 'saida', 'quantidade' => 999,
    ])->assertSessionHasErrors('quantidade');

    expect((float) $material->fresh()->estoque_atual)->toBe(100.0);
    expect(MovimentacaoEstoque::where('material_id', $material->id)->count())->toBe(0);
});

test('ajuste define o saldo absoluto', function () {
    $empresa = tenant();
    $ids = setupEstoque($empresa);
    $material = materialDemo($empresa, $ids['escola']);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->post(route('material.movimentar', $material->id), [
        'tipo' => 'ajuste', 'quantidade' => 42.5, 'motivo' => 'Contagem física',
    ]);

    expect((float) $material->fresh()->estoque_atual)->toBe(42.5);
});

test('material com movimentacoes nao pode ser excluido', function () {
    $empresa = tenant();
    $ids = setupEstoque($empresa);
    $material = materialDemo($empresa, $ids['escola']);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->post(route('material.movimentar', $material->id), [
        'tipo' => 'entrada', 'quantidade' => 5,
    ]);

    $this->actingAs($user)->delete(route('material.destroy', $material->id))
        ->assertRedirect(route('material.index'));

    expect(Material::find($material->id))->not->toBeNull();
});

test('material sem movimentacoes pode ser excluido', function () {
    $empresa = tenant();
    $ids = setupEstoque($empresa);
    $material = materialDemo($empresa, $ids['escola']);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->delete(route('material.destroy', $material->id));

    expect(Material::find($material->id))->toBeNull();
});

test('baixo estoque e sinalizado', function () {
    $empresa = tenant();
    $ids = setupEstoque($empresa);
    materialDemo($empresa, $ids['escola'], ['nome' => 'Quase zerado', 'estoque_atual' => 5, 'estoque_minimo' => 10]);
    materialDemo($empresa, $ids['escola'], ['nome' => 'Estoque folgado', 'estoque_atual' => 200, 'estoque_minimo' => 10]);
    $user = usuarioTenant($empresa, 'secretario');

    expect(Material::where('nome', 'Quase zerado')->first()->baixoEstoque())->toBeTrue()
        ->and(Material::where('nome', 'Estoque folgado')->first()->baixoEstoque())->toBeFalse();
});

test('coordenador le mas nao movimenta nem exclui', function () {
    $empresa = tenant();
    $ids = setupEstoque($empresa);
    $material = materialDemo($empresa, $ids['escola']);
    $user = usuarioTenant($empresa, 'coordenador_pedagogico');

    $this->actingAs($user)->get(route('material.index'))->assertOk();
    $this->actingAs($user)->get(route('material.movimentacoes', $material->id))->assertOk();
    $this->actingAs($user)->post(route('material.movimentar', $material->id), [
        'tipo' => 'entrada', 'quantidade' => 1,
    ])->assertForbidden();
    $this->actingAs($user)->delete(route('material.destroy', $material->id))->assertForbidden();
});

test('material de outro tenant nao aparece', function () {
    $empresa = tenant();
    $ids = setupEstoque($empresa);
    materialDemo($empresa, $ids['escola'], ['nome' => 'Material Secreto A']);

    $outro = tenant(['nome' => 'Rede B', 'slug' => 'rede-b-estoque']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $userB = usuarioTenant($outro, 'secretario');

    $this->actingAs($userB)
        ->get(route('material.index'))
        ->assertOk()
        ->assertDontSee('Material Secreto A');
});
