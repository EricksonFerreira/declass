<?php

use App\Models\Aluno;
use App\Models\ContaReceber;
use App\Models\Escola;
use App\Models\Pessoa;
use App\Services\DreService;
use App\Services\FluxoCaixaService;
use App\Support\EmpresaContext;

function contaReceberSetup($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola CR', 'empresa_id' => $empresa->id]);
    $pessoa = Pessoa::create(['nome' => 'Aluno CR', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-CR1', 'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'aluno');
}

test('financeiro gerencia contas a receber: criar baixar e cancelar', function () {
    $empresa = tenant();
    $ids = contaReceberSetup($empresa);
    $financeiro = usuarioTenant($empresa, 'financeiro');

    $this->actingAs($financeiro)->get('/admin/conta-receber')->assertOk();
    $this->actingAs($financeiro)->get('/admin/conta-receber/create')->assertOk();

    $this->actingAs($financeiro)->post('/admin/conta-receber', [
        'descricao' => 'Uniforme completo',
        'categoria' => 'material',
        'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id,
        'valor' => 350.90,
        'data_vencimento' => '2026-03-15',
    ])->assertRedirect();

    $conta = ContaReceber::first();
    expect($conta->status)->toBe('prevista')
        ->and($conta->empresa_id)->toBe($empresa->id)
        ->and($conta->aluno_id)->toBe($ids['aluno']->id);

    // baixar como recebida
    $this->actingAs($financeiro)->post("/admin/conta-receber/{$conta->id}/receber", [
        'forma_recebimento' => 'pix', 'data_recebimento' => '2026-03-14',
    ]);

    $conta->refresh();
    expect($conta->status)->toBe('recebida')
        ->and($conta->forma_recebimento)->toBe('pix')
        ->and($conta->data_recebimento->toDateString())->toBe('2026-03-14');

    // recebida nao pode ser editada nem excluida
    $this->actingAs($financeiro)->put("/admin/conta-receber/{$conta->id}", [
        'descricao' => 'x', 'categoria' => 'outros', 'valor' => 1, 'data_vencimento' => '2026-01-01',
    ])->assertForbidden();
    $this->actingAs($financeiro)->delete("/admin/conta-receber/{$conta->id}")->assertRedirect();
    expect(ContaReceber::find($conta->id))->not->toBeNull();
});

test('conta prevista pode ser editada cancelada e excluida', function () {
    $empresa = tenant();
    contaReceberSetup($empresa);
    $financeiro = usuarioTenant($empresa, 'financeiro');
    $conta = ContaReceber::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Taxa de evento',
        'categoria' => 'eventos', 'valor' => 80, 'data_vencimento' => '2026-04-01',
        'status' => 'prevista',
    ]);

    // edicao de prevista funciona
    $this->actingAs($financeiro)->put("/admin/conta-receber/{$conta->id}", [
        'descricao' => 'Taxa de evento (revisada)', 'categoria' => 'eventos',
        'valor' => 90, 'data_vencimento' => '2026-04-05',
    ])->assertRedirect();
    expect($conta->fresh()->valor)->toBe(90.0);

    // cancelar prevista
    $this->actingAs($financeiro)->post("/admin/conta-receber/{$conta->id}/cancelar");
    expect($conta->fresh()->status)->toBe('cancelada');

    // cancelada nao pode ser recebida nem excluida
    $this->actingAs($financeiro)->post("/admin/conta-receber/{$conta->id}/receber");
    expect($conta->fresh()->status)->toBe('cancelada');
    $this->actingAs($financeiro)->delete("/admin/conta-receber/{$conta->id}")->assertRedirect();
    expect(ContaReceber::find($conta->id))->not->toBeNull();
});

test('contas a receber tem escopo de tenant e professor nao acessa', function () {
    $empresa = tenant();
    $empresaB = tenant();
    contaReceberSetup($empresa);
    $financeiro = usuarioTenant($empresa, 'financeiro');

    ContaReceber::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Conta A',
        'categoria' => 'taxas', 'valor' => 100, 'data_vencimento' => '2026-01-10',
    ]);

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    $contaB = ContaReceber::create([
        'empresa_id' => $empresaB->id, 'descricao' => 'Conta B',
        'categoria' => 'taxas', 'valor' => 200, 'data_vencimento' => '2026-01-10',
    ]);
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($financeiro)->get('/admin/conta-receber')
        ->assertOk()
        ->assertSee('Conta A')
        ->assertDontSee('Conta B');

    // conta de outro tenant nao pode ser baixada/editada
    $this->actingAs($financeiro)->post("/admin/conta-receber/{$contaB->id}/receber")->assertNotFound();
    $this->actingAs($financeiro)->get("/admin/conta-receber/{$contaB->id}/edit")->assertNotFound();

    $professor = usuarioTenant($empresa, 'professor');
    $this->actingAs($professor)->get('/admin/conta-receber')->assertForbidden();
});

test('fluxo de caixa soma contas a receber nas receitas', function () {
    $empresa = tenant();
    contaReceberSetup($empresa);

    // recebida em março + prevista vencendo em maio
    ContaReceber::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Recebida', 'categoria' => 'taxas',
        'valor' => 250, 'data_vencimento' => '2026-03-05', 'status' => 'recebida',
        'data_recebimento' => '2026-03-06',
    ]);
    ContaReceber::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Prevista', 'categoria' => 'eventos',
        'valor' => 400, 'data_vencimento' => '2026-05-10', 'status' => 'prevista',
    ]);
    // cancelada nao entra em nada
    ContaReceber::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Cancelada', 'categoria' => 'outros',
        'valor' => 999, 'data_vencimento' => '2026-03-01', 'status' => 'cancelada',
    ]);

    $dados = app(FluxoCaixaService::class)->anual(2026, null, $empresa->id);

    expect($dados['meses'][3]['rec_realizado'])->toBe(250.0)
        ->and($dados['meses'][5]['rec_previsto'])->toBe(400.0)
        ->and($dados['totais']['rec_realizado'])->toBe(250.0);
});

test('dre inclui contas a receber recebidas como outras receitas', function () {
    $empresa = tenant();
    contaReceberSetup($empresa);

    ContaReceber::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Uniforme', 'categoria' => 'material',
        'valor' => 300, 'data_vencimento' => '2026-03-10', 'status' => 'recebida',
        'data_recebimento' => '2026-04-02',
    ]);
    // prevista NAO conta como receita
    ContaReceber::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Pendente', 'categoria' => 'taxas',
        'valor' => 500, 'data_vencimento' => '2026-03-15', 'status' => 'prevista',
    ]);

    $dados = app(DreService::class)->demonstrativo(2026, null, $empresa->id);

    expect($dados['outras']['total'])->toBe(300.0)
        ->and($dados['outras'][3])->toBe(300.0)   // competencia = mes do vencimento
        ->and($dados['liquida']['total'])->toBe(300.0)
        ->and($dados['resultado']['total'])->toBe(300.0); // sem despesas
});
