<?php

/**
 * Testes unitários do EmpresaPlanoService.
 *
 * Cobre:
 * - valorTotalPlano (mensal / anual)
 * - contratar (nova assinatura, cancelamento de antigas, desativação de veículos)
 * - ativarAposConfirmacao (contratação, upgrade, cancelamento de pendentes)
 * - criarCobrancaUpgrade (proration mesmo ciclo, mensal→anual com reinício)
 * - isDowngrade
 * - veiculosExcedentes
 */

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\EmpresaPlanoPagamento;
use App\Models\Plano;
use App\Models\Veiculo;
use App\Services\EmpresaPlanoService;
use Carbon\Carbon;

// ─────────────────────────────────────────────────────────────────────────────
// valorTotalPlano
// ─────────────────────────────────────────────────────────────────────────────

describe('valorTotalPlano', function () {
    it('retorna valor_mensal para periodicidade mensal', function () {
        $plano   = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 170.00]);
        $service = app(EmpresaPlanoService::class);

        expect($service->valorTotalPlano($plano, 'mensal'))->toBe(200.0);
    });

    it('retorna valor_anual * 12 para periodicidade anual', function () {
        $plano   = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 170.00]);
        $service = app(EmpresaPlanoService::class);

        expect($service->valorTotalPlano($plano, 'anual'))->toBe(170.0 * 12);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// contratar — nova assinatura
// ─────────────────────────────────────────────────────────────────────────────

describe('contratar — nova assinatura', function () {
    it('cria assinatura com status aguardando_pagamento', function () {
        $empresa    = Empresa::factory()->create();
        $plano      = Plano::factory()->create(['valor_mensal' => 199.90]);
        $service    = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'pix', null, null);

        expect($assinatura->status)->toBe('aguardando_pagamento');
        expect($assinatura->empresa_id)->toBe($empresa->id);
        expect($assinatura->plano_id)->toBe($plano->id);
    });

    it('cria primeiro pagamento pendente junto com a assinatura', function () {
        $empresa    = Empresa::factory()->create();
        $plano      = Plano::factory()->create(['valor_mensal' => 199.90]);
        $service    = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'pix', null, null);
        $pagamento  = EmpresaPlanoPagamento::where('empresa_plano_id', $assinatura->id)->first();

        expect($pagamento)->not->toBeNull();
        expect($pagamento->status)->toBe('pendente');
        expect((float) $pagamento->valor)->toBe(199.90);
    });

    it('valor cobrado anual e valor_anual * 12', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 150.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'anual', 'pix', null, null);

        expect((float) $assinatura->valor_cobrado)->toBe(150.0 * 12);
    });

    it('data de vencimento mensal e hoje + 1 mes', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 100.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'pix', null, null);

        expect($assinatura->data_vencimento->format('Y-m-d'))->toBe(now()->addMonth()->format('Y-m-d'));
    });

    it('data de vencimento anual e hoje + 1 ano', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_anual' => 150.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'anual', 'pix', null, null);

        expect($assinatura->data_vencimento->format('Y-m-d'))->toBe(now()->addYear()->format('Y-m-d'));
    });

    it('converte metodo automatico para cartao', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 199.90]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'automatico', null, null);

        expect($assinatura->metodo_cobranca)->toBe('cartao');
    });

    it('renovacao_automatica so e verdadeiro para metodo cartao', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 199.90]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'pix', null, null, [], true);

        expect($assinatura->renovacao_automatica)->toBeFalse();
    });

    it('renovacao_automatica e verdadeiro quando metodo e cartao', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 199.90]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'cartao', null, null, [], true);

        expect($assinatura->renovacao_automatica)->toBeTrue();
    });

    it('referencia_interna do pagamento comeca com INI-', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 100.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'pix', null, null);
        $pagamento  = EmpresaPlanoPagamento::where('empresa_plano_id', $assinatura->id)->first();

        expect($pagamento->referencia_interna)->toStartWith('INI-');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// contratar — cancelamento de assinaturas anteriores
// ─────────────────────────────────────────────────────────────────────────────

describe('contratar — cancelamento de assinaturas anteriores', function () {
    it('cancela assinatura ativa da empresa ao contratar novo plano', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 200.00]);
        $service = app(EmpresaPlanoService::class);

        $antiga = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $service->contratar($empresa, $planoB, 'mensal', 'pix', null, null);

        expect($antiga->fresh()->status)->toBe('cancelado');
    });

    it('cancela assinatura em trial ao contratar novo plano', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 200.00]);
        $service = app(EmpresaPlanoService::class);

        $trial = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'trial',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado' => 0.00,
        ]);

        $service->contratar($empresa, $planoB, 'mensal', 'pix', null, null);

        expect($trial->fresh()->status)->toBe('cancelado');
    });

    it('cancela assinatura inadimplente ao contratar novo plano', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 200.00]);
        $service = app(EmpresaPlanoService::class);

        $inadimplente = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'inadimplente',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $service->contratar($empresa, $planoB, 'mensal', 'pix', null, null);

        expect($inadimplente->fresh()->status)->toBe('cancelado');
    });

    it('nao cancela assinatura ja cancelada', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 200.00]);
        $service = app(EmpresaPlanoService::class);

        $cancelada = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'cancelado',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $service->contratar($empresa, $planoB, 'mensal', 'pix', null, null);

        // status continua cancelado (não houve mudança desnecessária)
        expect($cancelada->fresh()->status)->toBe('cancelado');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// contratar — desativação de veículos no downgrade imediato
// ─────────────────────────────────────────────────────────────────────────────

describe('contratar — desativacao de veiculos', function () {
    it('desativa somente os veiculos passados em veiculosDesativar', function () {
        $empresa  = Empresa::factory()->create();
        $plano    = Plano::factory()->comLimite(1)->create(['valor_mensal' => 99.00]);
        $service  = app(EmpresaPlanoService::class);

        $v1 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'AAA0001']);
        $v2 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'BBB0002']);

        $service->contratar($empresa, $plano, 'mensal', 'pix', null, null, [$v2->id]);

        expect(Veiculo::find($v1->id)->ativo)->toBeTrue();
        expect(Veiculo::find($v2->id)->ativo)->toBeFalse();
    });

    it('nao desativa veiculos de outra empresa', function () {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $plano    = Plano::factory()->comLimite(1)->create(['valor_mensal' => 99.00]);
        $service  = app(EmpresaPlanoService::class);

        $vB = Veiculo::create(['empresa_id' => $empresaB->id, 'ativo' => true, 'vendido' => false, 'nome' => 'X', 'placa_licenca' => 'XXX0001']);

        $service->contratar($empresaA, $plano, 'mensal', 'pix', null, null, [$vB->id]);

        // Veículo de outra empresa não deve ser desativado
        expect(Veiculo::find($vB->id)->ativo)->toBeTrue();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// ativarAposConfirmacao — contratação
// ─────────────────────────────────────────────────────────────────────────────

describe('ativarAposConfirmacao — contratacao', function () {
    it('muda status da assinatura para ativo', function () {
        $empresa    = Empresa::factory()->create();
        $plano      = Plano::factory()->create(['valor_mensal' => 199.90]);
        $service    = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'pix', null, null);
        $pagamento  = EmpresaPlanoPagamento::where('empresa_plano_id', $assinatura->id)->first();

        $service->ativarAposConfirmacao($pagamento);

        expect($assinatura->fresh()->status)->toBe('ativo');
    });

    it('muda status do pagamento para pago', function () {
        $empresa    = Empresa::factory()->create();
        $plano      = Plano::factory()->create(['valor_mensal' => 100.00]);
        $service    = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'pix', null, null);
        $pagamento  = EmpresaPlanoPagamento::where('empresa_plano_id', $assinatura->id)->first();

        $service->ativarAposConfirmacao($pagamento);

        expect($pagamento->fresh()->status)->toBe('pago');
    });

    it('define pago_em quando nao estava preenchido', function () {
        $empresa    = Empresa::factory()->create();
        $plano      = Plano::factory()->create(['valor_mensal' => 100.00]);
        $service    = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'pix', null, null);
        $pagamento  = EmpresaPlanoPagamento::where('empresa_plano_id', $assinatura->id)->first();

        $service->ativarAposConfirmacao($pagamento);

        expect($pagamento->fresh()->pago_em)->not->toBeNull();
    });

    it('nao sobrescreve pago_em quando ja estava definido', function () {
        $empresa    = Empresa::factory()->create();
        $plano      = Plano::factory()->create(['valor_mensal' => 100.00]);
        $service    = app(EmpresaPlanoService::class);
        $dataOriginal = now()->subDay();

        $assinatura = $service->contratar($empresa, $plano, 'mensal', 'pix', null, null);
        $pagamento  = EmpresaPlanoPagamento::where('empresa_plano_id', $assinatura->id)->first();
        $pagamento->update(['pago_em' => $dataOriginal]);

        $service->ativarAposConfirmacao($pagamento);

        expect($pagamento->fresh()->pago_em->format('Y-m-d'))->toBe($dataOriginal->format('Y-m-d'));
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// ativarAposConfirmacao — upgrade
// ─────────────────────────────────────────────────────────────────────────────

describe('ativarAposConfirmacao — upgrade', function () {
    it('troca o plano da assinatura para o novo plano', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subDays(15)->toDateString(),
            'data_vencimento' => now()->addDays(15)->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $pagamento = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 100.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => [
                'acao'               => 'upgrade',
                'novo_plano_id'      => $planoB->id,
                'nova_periodicidade' => 'mensal',
                'reinicia_periodo'   => false,
            ],
        ]);

        $service->ativarAposConfirmacao($pagamento);

        expect($assinatura->fresh()->plano_id)->toBe($planoB->id);
    });

    it('atualiza valor_cobrado para o total do novo plano', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subDays(15)->toDateString(),
            'data_vencimento' => now()->addDays(15)->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $pagamento = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 100.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => [
                'acao'               => 'upgrade',
                'novo_plano_id'      => $planoB->id,
                'nova_periodicidade' => 'mensal',
                'reinicia_periodo'   => false,
            ],
        ]);

        $service->ativarAposConfirmacao($pagamento);

        expect((float) $assinatura->fresh()->valor_cobrado)->toBe(300.0);
    });

    it('reinicia data_vencimento para +1 ano quando reinicia_periodo e verdadeiro', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 250.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subDays(10)->toDateString(),
            'data_vencimento' => now()->addDays(20)->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $pagamento = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 2000.00,
            'periodicidade'    => 'anual',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => [
                'acao'               => 'upgrade',
                'novo_plano_id'      => $planoB->id,
                'nova_periodicidade' => 'anual',
                'reinicia_periodo'   => true,
            ],
        ]);

        $service->ativarAposConfirmacao($pagamento);

        $atualizado = $assinatura->fresh();
        expect($atualizado->data_inicio->format('Y-m-d'))->toBe(now()->format('Y-m-d'));
        expect($atualizado->data_vencimento->format('Y-m-d'))->toBe(now()->addYear()->format('Y-m-d'));
    });

    it('limpa mudanca agendada ao confirmar upgrade', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00]);
        $planoC  = Plano::factory()->create(['valor_mensal' => 50.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subDays(5)->toDateString(),
            'data_vencimento' => now()->addDays(25)->toDateString(),
            'valor_cobrado' => 100.00,
            'proximo_plano_id' => $planoC->id,
            'proxima_periodicidade' => 'mensal',
            'mudanca_tipo' => 'downgrade',
            'mudanca_efetiva_em' => now()->addDays(25)->toDateString(),
        ]);

        $pagamento = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 100.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => [
                'acao'               => 'upgrade',
                'novo_plano_id'      => $planoB->id,
                'nova_periodicidade' => 'mensal',
                'reinicia_periodo'   => false,
            ],
        ]);

        $service->ativarAposConfirmacao($pagamento);

        $atualizado = $assinatura->fresh();
        expect($atualizado->proximo_plano_id)->toBeNull();
        expect($atualizado->mudanca_tipo)->toBeNull();
        expect($atualizado->mudanca_efetiva_em)->toBeNull();
    });

    it('cancela outros pagamentos pendentes de upgrade', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 200.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $plano->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado' => 200.00,
        ]);

        $pag1 = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id, 'empresa_id' => $empresa->id,
            'valor' => 50.00, 'periodicidade' => 'mensal', 'metodo' => 'pix',
            'status' => 'pendente', 'data_vencimento' => now()->addDays(3)->toDateString(),
            'metadados' => ['acao' => 'upgrade', 'novo_plano_id' => $plano->id, 'nova_periodicidade' => 'mensal', 'reinicia_periodo' => false],
        ]);

        $pag2 = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id, 'empresa_id' => $empresa->id,
            'valor' => 50.00, 'periodicidade' => 'mensal', 'metodo' => 'pix',
            'status' => 'pendente', 'data_vencimento' => now()->addDays(3)->toDateString(),
            'metadados' => ['acao' => 'upgrade', 'novo_plano_id' => $plano->id, 'nova_periodicidade' => 'mensal', 'reinicia_periodo' => false],
        ]);

        $service->ativarAposConfirmacao($pag1);

        expect($pag2->fresh()->status)->toBe('cancelado');
    });

    it('nao cancela pagamento de contratacao ao confirmar upgrade', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 200.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $plano->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado' => 200.00,
        ]);

        $pagContratacao = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id, 'empresa_id' => $empresa->id,
            'valor' => 200.00, 'periodicidade' => 'mensal', 'metodo' => 'pix',
            'status' => 'pago', 'data_vencimento' => now()->toDateString(),
            'metadados' => ['acao' => 'contratacao'],
        ]);

        $pagUpgrade = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id, 'empresa_id' => $empresa->id,
            'valor' => 50.00, 'periodicidade' => 'mensal', 'metodo' => 'pix',
            'status' => 'pendente', 'data_vencimento' => now()->addDays(3)->toDateString(),
            'metadados' => ['acao' => 'upgrade', 'novo_plano_id' => $plano->id, 'nova_periodicidade' => 'mensal', 'reinicia_periodo' => false],
        ]);

        $service->ativarAposConfirmacao($pagUpgrade);

        // Pagamento de contratação (já pago) não deve ser afetado
        expect($pagContratacao->fresh()->status)->toBe('pago');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// isDowngrade
// ─────────────────────────────────────────────────────────────────────────────

describe('isDowngrade', function () {
    it('retorna true quando novo plano tem limite menor que o atual', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->comLimite(20)->create();
        $planoB  = Plano::factory()->comLimite(5)->create();
        $service = app(EmpresaPlanoService::class);

        EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado' => 200.00,
        ]);

        expect($service->isDowngrade($empresa, $planoB))->toBeTrue();
    });

    it('retorna true quando plano atual e ilimitado e novo tem limite', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->ilimitado()->create();
        $planoB  = Plano::factory()->comLimite(10)->create();
        $service = app(EmpresaPlanoService::class);

        EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado' => 500.00,
        ]);

        expect($service->isDowngrade($empresa, $planoB))->toBeTrue();
    });

    it('retorna false quando empresa nao tem assinatura ativa', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->comLimite(5)->create();
        $service = app(EmpresaPlanoService::class);

        expect($service->isDowngrade($empresa, $plano))->toBeFalse();
    });

    it('retorna false quando novo plano tem limite maior', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->comLimite(5)->create();
        $planoB  = Plano::factory()->comLimite(20)->create();
        $service = app(EmpresaPlanoService::class);

        EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        expect($service->isDowngrade($empresa, $planoB))->toBeFalse();
    });

    it('retorna false quando novo plano e ilimitado', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->comLimite(10)->create();
        $planoB  = Plano::factory()->ilimitado()->create();
        $service = app(EmpresaPlanoService::class);

        EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        expect($service->isDowngrade($empresa, $planoB))->toBeFalse();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// veiculosExcedentes
// ─────────────────────────────────────────────────────────────────────────────

describe('veiculosExcedentes', function () {
    it('retorna colecao vazia quando plano e ilimitado', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->ilimitado()->create();
        $service = app(EmpresaPlanoService::class);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'XYZ1111']);

        expect($service->veiculosExcedentes($empresa, $plano))->toHaveCount(0);
    });

    it('retorna colecao vazia quando dentro do limite', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->comLimite(5)->create();
        $service = app(EmpresaPlanoService::class);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'INL0001']);

        expect($service->veiculosExcedentes($empresa, $plano))->toHaveCount(0);
    });

    it('retorna exatamente os excedentes quando acima do limite', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->comLimite(1)->create();
        $service = app(EmpresaPlanoService::class);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'EXC0001']);
        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'EXC0002']);

        expect($service->veiculosExcedentes($empresa, $plano))->toHaveCount(1);
    });

    it('ignora veiculos inativos na contagem', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->comLimite(1)->create();
        $service = app(EmpresaPlanoService::class);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true,  'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'VVV0001']);
        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => false, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'VVV0002']);

        expect($service->veiculosExcedentes($empresa, $plano))->toHaveCount(0);
    });

    it('ignora veiculos vendidos na contagem', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->comLimite(1)->create();
        $service = app(EmpresaPlanoService::class);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'SLD0001']);
        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => true,  'nome' => 'B', 'placa_licenca' => 'SLD0002']);

        expect($service->veiculosExcedentes($empresa, $plano))->toHaveCount(0);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// criarCobrancaUpgrade — proration
// ─────────────────────────────────────────────────────────────────────────────

describe('criarCobrancaUpgrade', function () {
    it('cria pagamento proporcional no mesmo ciclo mensal', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subDays(15)->toDateString(),
            'data_vencimento' => now()->addDays(15)->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $pagamento = $service->criarCobrancaUpgrade($assinatura, $planoB, 'mensal', 'pix', null);

        expect($pagamento->status)->toBe('pendente');
        expect((float) $pagamento->valor)->toBeGreaterThan(0);
        expect($pagamento->metadados['acao'])->toBe('upgrade');
    });

    it('valor proporcional e calculado corretamente (dias restantes / dias total * diff)', function () {
        $empresa = Empresa::factory()->create();
        // Plano A: R$100/mês, Plano B: R$200/mês
        // Período: 30 dias total, 15 restantes → diff = 100 → proporcional = 15/30 * 100 = 50
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 180.00]);
        $service = app(EmpresaPlanoService::class);

        Carbon::setTestNow(now());

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subDays(15)->toDateString(),
            'data_vencimento' => now()->addDays(15)->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $pagamento = $service->criarCobrancaUpgrade($assinatura, $planoB, 'mensal', 'pix', null);

        // 15/30 * (200 - 100) = 50.00
        expect((float) $pagamento->valor)->toEqual(50.0);

        Carbon::setTestNow();
    });

    it('cria pagamento com metadados de upgrade corretos', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subDays(10)->toDateString(),
            'data_vencimento' => now()->addDays(20)->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $pagamento = $service->criarCobrancaUpgrade($assinatura, $planoB, 'mensal', 'pix', null);

        expect($pagamento->metadados['novo_plano_id'])->toBe($planoB->id);
        expect($pagamento->metadados['nova_periodicidade'])->toBe('mensal');
        expect($pagamento->metadados['reinicia_periodo'])->toBeFalse();
    });

    it('reinicia_periodo e verdadeiro quando troca de mensal para anual', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 80.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 160.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subDays(10)->toDateString(),
            'data_vencimento' => now()->addDays(20)->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $pagamento = $service->criarCobrancaUpgrade($assinatura, $planoB, 'anual', 'pix', null);

        expect($pagamento->metadados['reinicia_periodo'])->toBeTrue();
    });

    it('lanca excecao quando diferenca e zero (mesmo plano e mesmo valor)', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subDays(25)->toDateString(),
            'data_vencimento' => now()->addDays(5)->toDateString(),
            'valor_cobrado' => 300.00,
        ]);

        expect(fn () => $service->criarCobrancaUpgrade($assinatura, $planoB, 'mensal', 'pix', null))
            ->toThrow(RuntimeException::class);
    });

    it('lanca excecao para troca anual para mensal (nao suportada no upgrade imediato)', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 170.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'anual', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subMonths(2)->toDateString(),
            'data_vencimento' => now()->addMonths(10)->toDateString(),
            'valor_cobrado' => 2040.00,
        ]);

        expect(fn () => $service->criarCobrancaUpgrade($assinatura, $planoB, 'mensal', 'pix', null))
            ->toThrow(RuntimeException::class);
    });

    it('referencia_interna do upgrade comeca com UPG-', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id' => $empresa->id, 'plano_id' => $planoA->id,
            'periodicidade' => 'mensal', 'status' => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio' => now()->subDays(5)->toDateString(),
            'data_vencimento' => now()->addDays(25)->toDateString(),
            'valor_cobrado' => 100.00,
        ]);

        $pagamento = $service->criarCobrancaUpgrade($assinatura, $planoB, 'mensal', 'pix', null);

        expect($pagamento->referencia_interna)->toStartWith('UPG-');
    });
});
