<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Despesa;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Pessoa;
use App\Models\PlanoMensalidade;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Notifications\AvisoSistemaNotification;
use App\Services\DreService;
use App\Services\FluxoCaixaService;
use App\Services\MensalidadeService;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\Notification;

function setupFinanceiroF18($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Financeiro', 'empresa_id' => $empresa->id]);
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
    $plano = PlanoMensalidade::create([
        'empresa_id' => $empresa->id, 'nome' => 'Integral', 'valor' => 1000, 'ativo' => 'S',
    ]);

    $pessoa = Pessoa::create(['nome' => 'Aluno Fin', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-FIN1', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'plano_mensalidade_id' => $plano->id, 'numero_chamada' => 1,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'plano', 'aluno', 'matricula');
}

function mensalidadeFixture($empresa, $ids, array $attrs = []): Mensalidade
{
    return Mensalidade::create(array_merge([
        'empresa_id' => $empresa->id,
        'matricula_id' => $ids['matricula']->id,
        'plano_mensalidade_id' => $ids['plano']->id,
        'competencia' => '2026-03',
        'valor' => 1000,
        'vencimento' => '2026-03-10',
        'status' => 'pendente',
    ], $attrs));
}

// ---------- Contas a pagar ----------

test('financeiro gerencia despesas: criar baixar e cancelar', function () {
    $empresa = tenant();
    setupFinanceiroF18($empresa);
    $financeiro = usuarioTenant($empresa, 'financeiro');

    $this->actingAs($financeiro)->get('/admin/despesa')->assertOk();

    $this->actingAs($financeiro)->post('/admin/despesa', [
        'descricao' => 'Conta de luz março',
        'fornecedor' => 'Enel',
        'categoria' => 'utilidades',
        'valor' => 850.50,
        'data_vencimento' => '2026-03-15',
    ])->assertRedirect();

    $despesa = Despesa::first();
    expect($despesa->status)->toBe('prevista')
        ->and($despesa->empresa_id)->toBe($empresa->id);

    // baixar como paga
    $this->actingAs($financeiro)->post("/admin/despesa/{$despesa->id}/pagar", [
        'forma_pagamento' => 'pix', 'data_pagamento' => '2026-03-14',
    ]);

    $despesa->refresh();
    expect($despesa->status)->toBe('paga')
        ->and($despesa->forma_pagamento)->toBe('pix')
        ->and($despesa->data_pagamento->toDateString())->toBe('2026-03-14');

    // paga nao pode ser editada nem excluida
    $this->actingAs($financeiro)->put("/admin/despesa/{$despesa->id}", [
        'descricao' => 'x', 'categoria' => 'outros', 'valor' => 1, 'data_vencimento' => '2026-01-01',
    ])->assertForbidden();
    $this->actingAs($financeiro)->delete("/admin/despesa/{$despesa->id}")->assertRedirect();
    expect(Despesa::find($despesa->id))->not->toBeNull();
});

test('despesa prevista pode ser cancelada e nao volta', function () {
    $empresa = tenant();
    setupFinanceiroF18($empresa);
    $financeiro = usuarioTenant($empresa, 'financeiro');
    $despesa = Despesa::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Material escolar',
        'categoria' => 'material', 'valor' => 300, 'data_vencimento' => '2026-04-01',
        'status' => 'prevista',
    ]);

    $this->actingAs($financeiro)->post("/admin/despesa/{$despesa->id}/cancelar");
    expect($despesa->fresh()->status)->toBe('cancelada');

    // cancelada nao pode ser paga
    $this->actingAs($financeiro)->post("/admin/despesa/{$despesa->id}/pagar");
    expect($despesa->fresh()->status)->toBe('cancelada');
});

test('despesas tem escopo de tenant e professor nao acessa', function () {
    $empresa = tenant();
    setupFinanceiroF18($empresa);
    $financeiro = usuarioTenant($empresa, 'financeiro');
    Despesa::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Conta A',
        'categoria' => 'outros', 'valor' => 100, 'data_vencimento' => '2026-01-10',
    ]);

    $this->actingAs($financeiro)->get('/admin/despesa')->assertSee('Conta A');

    $professor = usuarioTenant($empresa, 'professor');
    $this->actingAs($professor)->get('/admin/despesa')->assertForbidden();
});

// ---------- Bolsa / desconto ----------

test('bolsa percentual abate o valor na geracao de mensalidades', function () {
    $empresa = tenant();
    $ids = setupFinanceiroF18($empresa);
    $ids['matricula']->update(['bolsa_percentual' => 50]);

    $service = app(MensalidadeService::class);
    $resultado = $service->gerarCompetencia('2026-03', '2026-03-10');

    expect($resultado['criadas'])->toBe(1);

    $mensalidade = Mensalidade::first();
    expect((float) $mensalidade->valor)->toBe(500.0); // 1000 - 50%
});

// ---------- Recibo PDF ----------

test('recibo pdf de mensalidade paga e bloqueado para pendente', function () {
    $empresa = tenant();
    $ids = setupFinanceiroF18($empresa);
    $financeiro = usuarioTenant($empresa, 'financeiro');

    $paga = mensalidadeFixture($empresa, $ids, [
        'status' => 'paga', 'data_pagamento' => '2026-03-09', 'forma_pagamento' => 'pix',
    ]);
    $pendente = mensalidadeFixture($empresa, $ids, ['competencia' => '2026-04']);

    $this->actingAs($financeiro)
        ->get("/admin/mensalidade/{$paga->id}/recibo")
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->actingAs($financeiro)
        ->get("/admin/mensalidade/{$pendente->id}/recibo")
        ->assertNotFound();
});

test('portal baixa recibo apenas de aluno vinculado', function () {
    $empresa = tenant();
    $ids = setupFinanceiroF18($empresa);

    // responsavel com conta vinculada ao aluno
    $pessoaResp = Pessoa::create(['nome' => 'Mãe Fin', 'empresa_id' => $empresa->id]);
    $responsavel = Responsavel::create(['pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id]);
    $ids['aluno']->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true,
        'pode_retirar' => true, 'autoriza_contato' => true,
    ]);
    $mae = usuarioTenant($empresa, 'responsavel_legal');
    $responsavel->update(['user_id' => $mae->id]);

    $mensalidade = mensalidadeFixture($empresa, $ids, [
        'status' => 'paga', 'data_pagamento' => '2026-03-09', 'forma_pagamento' => 'pix',
    ]);

    $this->actingAs($mae)
        ->get("/admin/portal/mensalidades/{$mensalidade->id}/recibo")
        ->assertOk();

    // responsavel com OUTRO aluno vinculado nao acessa a mensalidade alheia
    $pessoa2 = Pessoa::create(['nome' => 'Outro Aluno', 'empresa_id' => $empresa->id]);
    $aluno2 = Aluno::create([
        'pessoa_id' => $pessoa2->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-FIN2', 'empresa_id' => $empresa->id,
    ]);
    $pessoaResp2 = Pessoa::create(['nome' => 'Outro Resp', 'empresa_id' => $empresa->id]);
    $resp2 = Responsavel::create(['pessoa_id' => $pessoaResp2->id, 'empresa_id' => $empresa->id]);
    $aluno2->responsaveis()->attach($resp2->id, [
        'tipo' => 'legal', 'principal' => true,
        'pode_retirar' => true, 'autoriza_contato' => true,
    ]);
    $outro = usuarioTenant($empresa, 'responsavel_legal');
    $resp2->update(['user_id' => $outro->id]);

    $this->actingAs($outro)
        ->get("/admin/portal/mensalidades/{$mensalidade->id}/recibo")
        ->assertNotFound();
});

// ---------- Fluxo de caixa ----------

test('fluxo de caixa consolida realizado e previsto por mes', function () {
    $empresa = tenant();
    $ids = setupFinanceiroF18($empresa);

    // receitas: R$1000 pago em março + R$1000 pendente venc. abril
    mensalidadeFixture($empresa, $ids, [
        'status' => 'paga', 'data_pagamento' => '2026-03-05', 'competencia' => '2026-03',
    ]);
    mensalidadeFixture($empresa, $ids, [
        'competencia' => '2026-04', 'vencimento' => '2026-04-10',
    ]);

    // despesas: R$400 paga em março + R$300 prevista venc. maio
    Despesa::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Paga', 'categoria' => 'material',
        'valor' => 400, 'data_vencimento' => '2026-03-01', 'status' => 'paga',
        'data_pagamento' => '2026-03-02',
    ]);
    Despesa::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Prevista', 'categoria' => 'servicos',
        'valor' => 300, 'data_vencimento' => '2026-05-10', 'status' => 'prevista',
    ]);

    $dados = app(FluxoCaixaService::class)->anual(2026, null, $empresa->id);

    expect($dados['meses'][3]['rec_realizado'])->toBe(1000.0)
        ->and($dados['meses'][3]['des_realizado'])->toBe(400.0)
        ->and($dados['meses'][3]['saldo'])->toBe(600.0)
        ->and($dados['meses'][4]['rec_previsto'])->toBe(1000.0)
        ->and($dados['meses'][5]['des_previsto'])->toBe(300.0)
        ->and($dados['totais']['saldo_projetado'])->toBe(1300.0); // (2000 rec) - (700 des)
});

test('financeiro acessa fluxo de caixa e dre', function () {
    $empresa = tenant();
    setupFinanceiroF18($empresa);
    $financeiro = usuarioTenant($empresa, 'financeiro');

    $this->actingAs($financeiro)->get('/admin/fluxo-caixa?ano=2026')->assertOk()->assertSee('Fluxo de Caixa');
    $this->actingAs($financeiro)->get('/admin/dre?ano=2026')->assertOk()->assertSee('RESULTADO');

    $professor = usuarioTenant($empresa, 'professor');
    $this->actingAs($professor)->get('/admin/fluxo-caixa')->assertForbidden();
    $this->actingAs($professor)->get('/admin/dre')->assertForbidden();
});

// ---------- DRE ----------

test('dre consolida receita liquida e despesas por categoria', function () {
    $empresa = tenant();
    $ids = setupFinanceiroF18($empresa);

    // mensalidade paga com bolsa: bruta 1000, liquida 500 (desconto 500)
    $ids['matricula']->update(['bolsa_percentual' => 50]);
    mensalidadeFixture($empresa, $ids, [
        'status' => 'paga', 'data_pagamento' => '2026-03-05', 'competencia' => '2026-03',
        'valor' => 500,
    ]);

    Despesa::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Salários', 'categoria' => 'pessoal',
        'valor' => 7000, 'data_vencimento' => '2026-03-05', 'status' => 'prevista',
    ]);
    Despesa::create([
        'empresa_id' => $empresa->id, 'descricao' => 'Cancelada', 'categoria' => 'outros',
        'valor' => 999, 'data_vencimento' => '2026-03-06', 'status' => 'cancelada',
    ]);

    $dados = app(DreService::class)->demonstrativo(2026, null, $empresa->id);

    expect($dados['bruta']['total'])->toBe(1000.0)
        ->and($dados['descontos']['total'])->toBe(500.0)
        ->and($dados['liquida']['total'])->toBe(500.0)
        ->and($dados['despesas']['pessoal']['total'])->toBe(7000.0)
        ->and($dados['resultado']['total'])->toBe(-6500.0); // 500 - 7000
});

// ---------- Lembretes ----------

test('lembretes de vencimento sao enviados uma unica vez', function () {
    Notification::fake();
    $empresa = tenant();
    $ids = setupFinanceiroF18($empresa);

    // responsável com conta do portal
    $pessoaResp = Pessoa::create(['nome' => 'Resp', 'empresa_id' => $empresa->id]);
    $responsavel = Responsavel::create(['pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id]);
    $ids['aluno']->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true,
        'pode_retirar' => true, 'autoriza_contato' => true,
    ]);
    $mae = usuarioTenant($empresa, 'responsavel_legal');
    $responsavel->update(['user_id' => $mae->id]);

    // mensalidade que vence em 3 dias
    $aVencer = mensalidadeFixture($empresa, $ids, [
        'competencia' => '2026-03', 'vencimento' => now()->addDays(3)->toDateString(),
    ]);
    // mensalidade ja vencida
    $vencida = mensalidadeFixture($empresa, $ids, [
        'competencia' => '2026-02', 'vencimento' => now()->subDays(2)->toDateString(),
    ]);

    $this->artisan('financeiro:lembrar-vencimentos')->assertSuccessful();

    expect($aVencer->fresh()->lembrete_a_vencer_em)->not->toBeNull()
        ->and($vencida->fresh()->lembrete_vencida_em)->not->toBeNull();

    Notification::assertSentTo($mae, AvisoSistemaNotification::class, 2);

    // segunda execucao nao repete
    Notification::fake();
    $this->artisan('financeiro:lembrar-vencimentos')->assertSuccessful();
    Notification::assertNothingSent();
});
