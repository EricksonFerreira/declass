<?php

use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupFiltroIndex($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Filtro', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma');
}

test('index do diario usa a rota plural de filtro', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    setupFiltroIndex($empresa);

    $response = $this->actingAs($admin)->get(route('diario.index'));

    $response->assertOk();
    $response->assertSee('action="'.route('diario.filtro').'"', false);
});

test('selects do filtro enviam id e nao o rotulo', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['turma' => $turma] = setupFiltroIndex($empresa);

    $html = $this->actingAs($admin)->get(route('diario.index'))->assertOk()->getContent();

    expect($html)->toContain('value="'.$turma->id.'"')
        ->not->toContain('value="'.$turma->nome);
});

test('filtro do diario aceita get e grava sessao', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['turma' => $turma] = setupFiltroIndex($empresa);

    // index configura urlPadrao/rotaRedirect na sessao
    $this->actingAs($admin)->get(route('diario.index'))->assertOk();

    $this->actingAs($admin)
        ->get(route('diario.filtro', ['turma_id_where' => $turma->id]))
        ->assertRedirect(route('diario.index'))
        ->assertSessionHas('diario.filtro.turma_id', fn ($f) => $f['valor'] === $turma->id || $f['valor'] === (string) $turma->id);
});

test('remover filtro do diario aceita get', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['turma' => $turma] = setupFiltroIndex($empresa);

    $this->actingAs($admin)->get(route('diario.index'))->assertOk();
    $this->actingAs($admin)
        ->get(route('diario.filtro', ['turma_id_where' => $turma->id]))
        ->assertRedirect(route('diario.index'));

    $this->actingAs($admin)
        ->get(route('diario.removerFiltro', ['atributo' => 'turma_id']))
        ->assertRedirect(route('diario.index'));

    expect(session('diario.filtro.turma_id'))->toBeNull();
});

test('seletor de itens por pagina posta para a rota do modulo', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    setupFiltroIndex($empresa);

    $this->actingAs($admin)->get(route('diario.index'))
        ->assertOk()
        ->assertSee('action="'.route('diario.setPerPage').'"', false);
});

test('index da avaliacao tambem usa rota plural de filtro', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($admin)->get(route('avaliacao.index'))
        ->assertOk()
        ->assertSee('action="'.route('avaliacao.filtro').'"', false);
});
