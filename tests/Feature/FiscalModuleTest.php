<?php

use App\Models\Empresa;
use App\Models\Reserva;
use App\Models\RegraTributaria;
use App\Models\OperacaoFiscal;
use App\Models\DocumentoFiscal;
use App\Models\ClassificacaoFiscal;
use App\Modules\Fiscal\Actions\CalcularTributosAction;
use App\Modules\Fiscal\Services\NfseNacionalService;
use App\Modules\Fiscal\Enums\TipoOperacaoFiscal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calculates ibs and cbs taxes correctly using motor and rules', function () {
    // Seed rules
    RegraTributaria::create([
        'tributo' => 'CBS',
        'tipo_operacao' => TipoOperacaoFiscal::LOCACAO_VEICULO->value,
        'aliquota' => 0.90,
        'data_inicio' => '2026-01-01',
        'data_fim' => '2026-12-31',
        'gera_credito' => false,
        'ativo' => true,
    ]);

    RegraTributaria::create([
        'tributo' => 'IBS',
        'tipo_operacao' => TipoOperacaoFiscal::LOCACAO_VEICULO->value,
        'aliquota' => 0.10,
        'data_inicio' => '2026-01-01',
        'data_fim' => '2026-12-31',
        'gera_credito' => false,
        'ativo' => true,
    ]);

    $empresa = Empresa::create([
        'nome' => 'Locadora Teste',
        'slug' => 'locadora-teste',
        'cnpj' => '12345678000199',
        'ativo' => true,
    ]);

    $operacao = OperacaoFiscal::create([
        'empresa_id' => $empresa->id,
        'locacao_id' => 999,
        'tipo' => TipoOperacaoFiscal::LOCACAO_VEICULO->value,
        'data_fato_gerador' => '2026-06-01 12:00:00',
        'valor_operacao' => 1000.00,
        'valor_base_calculo' => 1000.00,
        'status' => 'PENDENTE',
    ]);

    $action = app(CalcularTributosAction::class);
    $action->handle($operacao);

    expect($operacao->fresh()->status)->toBe('CALCULADA');

    $cbs = $operacao->tributos()->where('tributo', 'CBS')->first();
    expect($cbs)->not->toBeNull()
        ->and($cbs->aliquota)->toBe(0.90)
        ->and($cbs->valor)->toBe(9.00);

    $ibs = $operacao->tributos()->where('tributo', 'IBS')->first();
    expect($ibs)->not->toBeNull()
        ->and($ibs->aliquota)->toBe(0.10)
        ->and($ibs->valor)->toBe(1.00);
});

it('emits national nfse successfully using service and gateway', function () {
    $empresa = Empresa::create([
        'nome' => 'Locadora Teste 2',
        'slug' => 'locadora-teste-2',
        'cnpj' => '12345678000199',
        'ativo' => true,
    ]);

    $operacao = OperacaoFiscal::create([
        'empresa_id' => $empresa->id,
        'locacao_id' => 999,
        'tipo' => TipoOperacaoFiscal::LOCACAO_VEICULO->value,
        'data_fato_gerador' => now(),
        'valor_operacao' => 3000.00,
        'valor_base_calculo' => 3000.00,
        'status' => 'CALCULADA',
    ]);

    $operacao->tributos()->create([
        'tributo' => 'CBS',
        'base_calculo' => 3000.00,
        'aliquota' => 0.90,
        'valor' => 27.00,
    ]);
    $operacao->tributos()->create([
        'tributo' => 'IBS',
        'base_calculo' => 3000.00,
        'aliquota' => 0.10,
        'valor' => 3.00,
    ]);

    $reserva = new Reserva();
    $reserva->id = 999;
    $reserva->valor_total_final = 3000.00;

    $usuario = new \App\Models\User();
    $usuario->nome = 'John Doe';
    $usuario->numero_identificacao = '12345678901';
    $usuario->email = 'john@example.com';
    $reserva->setRelation('usuario', $usuario);

    $veiculo = new \App\Models\Veiculo();
    $veiculo->marca = 'Chevrolet';
    $veiculo->modelo = 'Onix';
    $cf = new ClassificacaoFiscal();
    $cf->ncm = '87032310';
    $veiculo->setRelation('classificacaoFiscal', $cf);
    $reserva->setRelation('veiculo', $veiculo);

    $operacao->setRelation('locacao', $reserva);
    $operacao->setRelation('empresa', $empresa);

    $service = app(NfseNacionalService::class);
    $documento = $service->emitirParaOperacao($operacao);

    expect($documento->status)->toBe('AUTORIZADA')
        ->and($documento->numero_nfse)->not->toBeNull()
        ->and($documento->chave_acesso)->not->toBeNull()
        ->and($documento->xml)->toContain('87032310')
        ->and($operacao->fresh()->status)->toBe('AUTORIZADA');
});
