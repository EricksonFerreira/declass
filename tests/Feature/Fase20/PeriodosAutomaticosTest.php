<?php

use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\PeriodoLetivo;
use App\Support\EmpresaContext;

function anoLetivoPayload(Escola $escola, array $overrides = []): array
{
    return array_merge([
        'escola_id' => $escola->id,
        'ano' => 2026,
        'data_inicio' => '2026-02-01',
        'data_fim' => '2026-12-15',
        'status' => 'letivo',
        'ativo' => 'S',
    ], $overrides);
}

test('criar ano letivo bimestral gera 4 periodos com datas divididas', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $escola = Escola::create(['nome' => 'Unidade', 'empresa_id' => $empresa->id]);

    $this->actingAs($user)->post('/admin/ano-letivo', anoLetivoPayload($escola, [
        'periodicidade' => 'bimestral',
    ]))->assertRedirect();

    $ano = AnoLetivo::where('ano', 2026)->firstOrFail();
    $periodos = PeriodoLetivo::where('ano_letivo_id', $ano->id)->orderBy('ordem')->get();

    expect($periodos)->toHaveCount(4)
        ->and($periodos->pluck('nome')->all())->toBe(['1º Bimestre', '2º Bimestre', '3º Bimestre', '4º Bimestre'])
        ->and($periodos->pluck('ordem')->all())->toBe([1, 2, 3, 4]);

    // Periodos cobrem o ano inteiro sem buracos: fim do 1 = véspera do inicio do 2 etc.
    expect($periodos[0]->data_inicio->toDateString())->toBe('2026-02-01')
        ->and($periodos[3]->data_fim->toDateString())->toBe('2026-12-15');

    for ($i = 1; $i < 4; $i++) {
        expect($periodos[$i]->data_inicio->toDateString())
            ->toBe($periodos[$i - 1]->data_fim->copy()->addDay()->toDateString());
    }
});

test('periodicidades trimestral semestral e anual geram a quantidade certa', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $escola = Escola::create(['nome' => 'Unidade', 'empresa_id' => $empresa->id]);

    foreach ([['trimestral', 3, '1º Trimestre'], ['semestral', 2, '1º Semestre'], ['anual', 1, 'Ano Letivo']] as [$periodicidade, $qtd, $primeiro]) {
        $this->actingAs($user)->post('/admin/ano-letivo', anoLetivoPayload($escola, [
            'ano' => 2020 + $qtd,
            'periodicidade' => $periodicidade,
        ]));

        $ano = AnoLetivo::where('ano', 2020 + $qtd)->firstOrFail();
        $periodos = PeriodoLetivo::where('ano_letivo_id', $ano->id)->orderBy('ordem')->get();

        expect($periodos)->toHaveCount($qtd)
            ->and($periodos->first()->nome)->toBe($primeiro);
    }
});

test('periodicidade personalizado ou ausente nao gera periodos', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $escola = Escola::create(['nome' => 'Unidade', 'empresa_id' => $empresa->id]);

    $this->actingAs($user)->post('/admin/ano-letivo', anoLetivoPayload($escola, [
        'ano' => 2027, 'periodicidade' => 'personalizado',
    ]));
    $this->actingAs($user)->post('/admin/ano-letivo', anoLetivoPayload($escola, [
        'ano' => 2028,
    ]));

    expect(PeriodoLetivo::whereIn('ano_letivo_id', AnoLetivo::pluck('id'))->count())->toBe(0);
});

test('sem datas do ano letivo os periodos sao criados sem datas', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $escola = Escola::create(['nome' => 'Unidade', 'empresa_id' => $empresa->id]);

    $this->actingAs($user)->post('/admin/ano-letivo', anoLetivoPayload($escola, [
        'ano' => 2029, 'periodicidade' => 'semestral',
        'data_inicio' => null, 'data_fim' => null,
    ]));

    $ano = AnoLetivo::where('ano', 2029)->firstOrFail();
    $periodos = PeriodoLetivo::where('ano_letivo_id', $ano->id)->get();

    expect($periodos)->toHaveCount(2)
        ->and($periodos->every(fn ($p) => $p->data_inicio === null && $p->data_fim === null))->toBeTrue();
});

test('editar ano letivo nao regenera periodos', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $escola = Escola::create(['nome' => 'Unidade', 'empresa_id' => $empresa->id]);

    $this->actingAs($user)->post('/admin/ano-letivo', anoLetivoPayload($escola, [
        'periodicidade' => 'bimestral',
    ]));
    $ano = AnoLetivo::where('ano', 2026)->firstOrFail();

    // Muda periodicidade e salva — os 4 bimestres permanecem.
    $this->actingAs($user)->put("/admin/ano-letivo/{$ano->id}", anoLetivoPayload($escola, [
        'periodicidade' => 'semestral',
    ]));

    expect(PeriodoLetivo::where('ano_letivo_id', $ano->id)->count())->toBe(4);
});

test('acao manual gera periodos para ano existente sem periodos', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $escola = Escola::create(['nome' => 'Unidade', 'empresa_id' => $empresa->id]);

    // Ano criado como personalizado (sem periodos).
    $this->actingAs($user)->post('/admin/ano-letivo', anoLetivoPayload($escola, [
        'periodicidade' => 'personalizado',
    ]));
    $ano = AnoLetivo::where('ano', 2026)->firstOrFail();

    // Simula definicao posterior da periodicidade.
    $ano->update(['periodicidade' => 'trimestral']);

    $this->actingAs($user)->post("/admin/ano-letivo/{$ano->id}/gerar-periodos")
        ->assertRedirect(route('ano_letivo.edit', $ano->id));

    expect(PeriodoLetivo::where('ano_letivo_id', $ano->id)->count())->toBe(3);

    // Segunda chamada nao duplica.
    $this->actingAs($user)->post("/admin/ano-letivo/{$ano->id}/gerar-periodos");
    expect(PeriodoLetivo::where('ano_letivo_id', $ano->id)->count())->toBe(3);
});

test('periodicidade invalida falha na validacao', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $escola = Escola::create(['nome' => 'Unidade', 'empresa_id' => $empresa->id]);

    $this->actingAs($user)->post('/admin/ano-letivo', anoLetivoPayload($escola, [
        'periodicidade' => 'semanal',
    ]))->assertSessionHasErrors('periodicidade');
});
