<?php

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\EmpresaPlanoPagamento;
use App\Models\Plano;
use App\Models\Veiculo;
use App\Services\EmpresaPlanoService;
use Carbon\Carbon;

// ---------------------------------------------------------------------------
// valorTotalPlano
// ---------------------------------------------------------------------------

describe('valorTotalPlano', function () {
    it('retorna valor_mensal quando periodicidade e mensal', function () {
        $plano = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 170.00]);
        $service = app(EmpresaPlanoService::class);

        expect($service->valorTotalPlano($plano, 'mensal'))->toBe(200.0);
    });

    it('retorna valor_anual * 12 quando periodicidade e anual', function () {
        $plano = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 170.00]);
        $service = app(EmpresaPlanoService::class);

        expect($service->valorTotalPlano($plano, 'anual'))->toBe(170.0 * 12);
    });
});

// ---------------------------------------------------------------------------
// contratar
// ---------------------------------------------------------------------------

describe('contratar', function () {
    it('cria assinatura com status aguardando_pagamento', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 199.90]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar(
            empresa:         $empresa,
            plano:           $plano,
            periodicidade:   'mensal',
            metodoPagamento: 'pix',
            diaVencimento:   null,
            criadoPorUserId: null,
        );

        expect($assinatura->status)->toBe('aguardando_pagamento');
        expect($assinatura->empresa_id)->toBe($empresa->id);
        expect($assinatura->plano_id)->toBe($plano->id);
    });

    it('cria primeiro pagamento pendente junto com a assinatura', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 199.90]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar(
            empresa:         $empresa,
            plano:           $plano,
            periodicidade:   'mensal',
            metodoPagamento: 'pix',
            diaVencimento:   null,
            criadoPorUserId: null,
        );

        $pagamento = EmpresaPlanoPagamento::where('empresa_plano_id', $assinatura->id)->first();

        expect($pagamento)->not->toBeNull();
        expect($pagamento->status)->toBe('pendente');
        expect((float) $pagamento->valor)->toBe(199.90);
    });

    it('cancela assinaturas ativas anteriores da empresa', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 200.00]);
        $service = app(EmpresaPlanoService::class);

        $assinaturaAntiga = EmpresaPlano::create([
            'empresa_id'      => $empresa->id,
            'plano_id'        => $planoA->id,
            'periodicidade'   => 'mensal',
            'status'          => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio'     => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado'   => 100.00,
        ]);

        $service->contratar(
            empresa:         $empresa,
            plano:           $planoB,
            periodicidade:   'mensal',
            metodoPagamento: 'pix',
            diaVencimento:   null,
            criadoPorUserId: null,
        );

        expect($assinaturaAntiga->fresh()->status)->toBe('cancelado');
    });

    it('calcula vencimento anual como 12 meses a partir de hoje', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_anual' => 150.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar(
            empresa:         $empresa,
            plano:           $plano,
            periodicidade:   'anual',
            metodoPagamento: 'pix',
            diaVencimento:   null,
            criadoPorUserId: null,
        );

        expect($assinatura->data_vencimento->format('Y-m-d'))
            ->toBe(now()->addYear()->format('Y-m-d'));
    });

    it('desativa veiculos excedentes passados em veiculosDesativar', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->comLimite(1)->create(['valor_mensal' => 99.00]);
        $service = app(EmpresaPlanoService::class);

        $veiculo1 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'Carro A', 'placa_licenca' => 'AAA0001']);
        $veiculo2 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'Carro B', 'placa_licenca' => 'BBB0002']);

        $service->contratar(
            empresa:           $empresa,
            plano:             $plano,
            periodicidade:     'mensal',
            metodoPagamento:   'pix',
            diaVencimento:     null,
            criadoPorUserId:   null,
            veiculosDesativar: [$veiculo2->id],
        );

        expect(Veiculo::find($veiculo1->id)->ativo)->toBeTrue();
        expect(Veiculo::find($veiculo2->id)->ativo)->toBeFalse();
    });

    it('converte metodo automatico para cartao', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 199.90]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar(
            empresa:         $empresa,
            plano:           $plano,
            periodicidade:   'mensal',
            metodoPagamento: 'automatico',
            diaVencimento:   null,
            criadoPorUserId: null,
        );

        expect($assinatura->metodo_cobranca)->toBe('cartao');
    });
});

// ---------------------------------------------------------------------------
// ativarAposConfirmacao
// ---------------------------------------------------------------------------

describe('ativarAposConfirmacao', function () {
    it('ativa assinatura apos confirmar pagamento de contratacao', function () {
        $empresa    = Empresa::factory()->create();
        $plano      = Plano::factory()->create(['valor_mensal' => 199.90]);
        $service    = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar(
            empresa:         $empresa,
            plano:           $plano,
            periodicidade:   'mensal',
            metodoPagamento: 'pix',
            diaVencimento:   null,
            criadoPorUserId: null,
        );

        $pagamento = EmpresaPlanoPagamento::where('empresa_plano_id', $assinatura->id)
            ->where('status', 'pendente')
            ->first();

        $service->ativarAposConfirmacao($pagamento);

        expect($assinatura->fresh()->status)->toBe('ativo');
        expect($pagamento->fresh()->status)->toBe('pago');
    });

    it('marca pago_em quando nao estava definido', function () {
        $empresa    = Empresa::factory()->create();
        $plano      = Plano::factory()->create(['valor_mensal' => 100.00]);
        $service    = app(EmpresaPlanoService::class);

        $assinatura = $service->contratar(
            empresa:         $empresa,
            plano:           $plano,
            periodicidade:   'mensal',
            metodoPagamento: 'pix',
            diaVencimento:   null,
            criadoPorUserId: null,
        );

        $pagamento = EmpresaPlanoPagamento::where('empresa_plano_id', $assinatura->id)->first();
        $service->ativarAposConfirmacao($pagamento);

        expect($pagamento->fresh()->pago_em)->not->toBeNull();
    });

    it('aplica upgrade ao confirmar pagamento de upgrade', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id'      => $empresa->id,
            'plano_id'        => $planoA->id,
            'periodicidade'   => 'mensal',
            'status'          => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio'     => now()->subDays(15)->toDateString(),
            'data_vencimento' => now()->addDays(15)->toDateString(),
            'valor_cobrado'   => 100.00,
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

        $assinaturaAtualizada = $assinatura->fresh();
        expect($assinaturaAtualizada->plano_id)->toBe($planoB->id);
        expect($assinaturaAtualizada->periodicidade)->toBe('mensal');
    });

    it('cancela outros pagamentos pendentes de upgrade apos confirmar', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 200.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id'      => $empresa->id,
            'plano_id'        => $plano->id,
            'periodicidade'   => 'mensal',
            'status'          => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio'     => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado'   => 200.00,
        ]);

        $pag1 = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 50.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => ['acao' => 'upgrade', 'novo_plano_id' => $plano->id, 'nova_periodicidade' => 'mensal', 'reinicia_periodo' => false],
        ]);

        $pag2 = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 50.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => ['acao' => 'upgrade', 'novo_plano_id' => $plano->id, 'nova_periodicidade' => 'mensal', 'reinicia_periodo' => false],
        ]);

        $service->ativarAposConfirmacao($pag1);

        expect($pag2->fresh()->status)->toBe('cancelado');
    });
});

// ---------------------------------------------------------------------------
// isDowngrade
// ---------------------------------------------------------------------------

describe('isDowngrade', function () {
    it('retorna true quando novo plano tem limite menor', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->comLimite(20)->create();
        $planoB  = Plano::factory()->comLimite(5)->create();
        $service = app(EmpresaPlanoService::class);

        EmpresaPlano::create([
            'empresa_id'      => $empresa->id,
            'plano_id'        => $planoA->id,
            'periodicidade'   => 'mensal',
            'status'          => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio'     => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado'   => 200.00,
        ]);

        expect($service->isDowngrade($empresa, $planoB))->toBeTrue();
    });

    it('retorna true quando plano atual e ilimitado e novo tem limite', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->ilimitado()->create();
        $planoB  = Plano::factory()->comLimite(10)->create();
        $service = app(EmpresaPlanoService::class);

        EmpresaPlano::create([
            'empresa_id'      => $empresa->id,
            'plano_id'        => $planoA->id,
            'periodicidade'   => 'mensal',
            'status'          => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio'     => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado'   => 500.00,
        ]);

        expect($service->isDowngrade($empresa, $planoB))->toBeTrue();
    });

    it('retorna false quando empresa nao tem assinatura ativa', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->comLimite(5)->create();
        $service = app(EmpresaPlanoService::class);

        expect($service->isDowngrade($empresa, $plano))->toBeFalse();
    });

    it('retorna false quando novo plano tem mais veiculos', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->comLimite(5)->create();
        $planoB  = Plano::factory()->comLimite(20)->create();
        $service = app(EmpresaPlanoService::class);

        EmpresaPlano::create([
            'empresa_id'      => $empresa->id,
            'plano_id'        => $planoA->id,
            'periodicidade'   => 'mensal',
            'status'          => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio'     => now()->toDateString(),
            'data_vencimento' => now()->addMonth()->toDateString(),
            'valor_cobrado'   => 100.00,
        ]);

        expect($service->isDowngrade($empresa, $planoB))->toBeFalse();
    });
});

// ---------------------------------------------------------------------------
// veiculosExcedentes
// ---------------------------------------------------------------------------

describe('veiculosExcedentes', function () {
    it('retorna veiculos que excedem o limite do novo plano', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->comLimite(1)->create();
        $service = app(EmpresaPlanoService::class);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'XYZ0001']);
        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'XYZ0002']);

        $excedentes = $service->veiculosExcedentes($empresa, $plano);

        expect($excedentes)->toHaveCount(1);
    });

    it('retorna colecao vazia quando plano e ilimitado', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->ilimitado()->create();
        $service = app(EmpresaPlanoService::class);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'XYZ1111']);

        expect($service->veiculosExcedentes($empresa, $plano))->toHaveCount(0);
    });

    it('nao conta veiculos vendidos ou inativos', function () {
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->comLimite(1)->create();
        $service = app(EmpresaPlanoService::class);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true,  'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'VVV0001']);
        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => false, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'VVV0002']);
        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true,  'vendido' => true,  'nome' => 'C', 'placa_licenca' => 'VVV0003']);

        $excedentes = $service->veiculosExcedentes($empresa, $plano);

        expect($excedentes)->toHaveCount(0);
    });
});

// ---------------------------------------------------------------------------
// criarCobrancaUpgrade
// ---------------------------------------------------------------------------

describe('criarCobrancaUpgrade', function () {
    it('cria pagamento proporcional para upgrade no mesmo ciclo mensal', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $service = app(EmpresaPlanoService::class);

        Carbon::setTestNow(now());

        $assinatura = EmpresaPlano::create([
            'empresa_id'      => $empresa->id,
            'plano_id'        => $planoA->id,
            'periodicidade'   => 'mensal',
            'status'          => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio'     => now()->subDays(15)->toDateString(),
            'data_vencimento' => now()->addDays(15)->toDateString(),
            'valor_cobrado'   => 100.00,
        ]);

        $pagamento = $service->criarCobrancaUpgrade(
            assinatura:        $assinatura,
            novoPlano:         $planoB,
            novaPeriodicidade: 'mensal',
            metodoPagamento:   'pix',
            criadoPorUserId:   null,
        );

        expect($pagamento->status)->toBe('pendente');
        expect((float) $pagamento->valor)->toBeGreaterThan(0);
        expect($pagamento->metadados['acao'])->toBe('upgrade');

        Carbon::setTestNow();
    });

    it('lanca excecao quando nao ha valor a cobrar no upgrade', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id'      => $empresa->id,
            'plano_id'        => $planoA->id,
            'periodicidade'   => 'mensal',
            'status'          => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio'     => now()->subDays(25)->toDateString(),
            'data_vencimento' => now()->addDays(5)->toDateString(),
            'valor_cobrado'   => 300.00,
        ]);

        expect(fn () => $service->criarCobrancaUpgrade(
            assinatura:        $assinatura,
            novoPlano:         $planoB,
            novaPeriodicidade: 'mensal',
            metodoPagamento:   'pix',
            criadoPorUserId:   null,
        ))->toThrow(RuntimeException::class);
    });

    it('lanca excecao para troca de periodicidade nao suportada', function () {
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 170.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $service = app(EmpresaPlanoService::class);

        $assinatura = EmpresaPlano::create([
            'empresa_id'      => $empresa->id,
            'plano_id'        => $planoA->id,
            'periodicidade'   => 'anual',
            'status'          => 'ativo',
            'metodo_cobranca' => 'pix',
            'data_inicio'     => now()->subMonths(2)->toDateString(),
            'data_vencimento' => now()->addMonths(10)->toDateString(),
            'valor_cobrado'   => 2040.00,
        ]);

        expect(fn () => $service->criarCobrancaUpgrade(
            assinatura:        $assinatura,
            novoPlano:         $planoB,
            novaPeriodicidade: 'mensal',
            metodoPagamento:   'pix',
            criadoPorUserId:   null,
        ))->toThrow(RuntimeException::class);
    });
});
