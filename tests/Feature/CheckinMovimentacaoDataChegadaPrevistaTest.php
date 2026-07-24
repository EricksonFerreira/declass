<?php

use App\Actions\Reserva\Checkin\Finalizar\FinalizarCheckinReservaComumAction;
use App\DTO\Reserva\ReservaResolvida;
use App\Models\ContratoCheckin;
use App\Models\Reserva;
use App\Services\MovimentacaoService;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

it('sets the planned arrival date on the check-in movement when the reservation is finalized', function () {
    $reserva = Reserva::factory()->create([
        'data_inicio' => now()->subDay(),
        'data_fim' => now()->addDays(3),
        'estacao_retirada_id' => 1,
        'estacao_devolucao_id' => 2,
    ]);

    $contrato = ContratoCheckin::factory()->create([
        'reserva_id' => $reserva->id,
        'veiculo_id' => $reserva->veiculo_id,
        'assinado' => 'N',
    ]);

    $reserva->setRelation('contratoCheckin', $contrato);

    $registro = new ReservaResolvida($reserva, $reserva);

    $request = new Request([
        'contrato_id' => $contrato->id,
        'assinatura' => 'assinatura-teste',
        'modo' => 'digital',
    ]);

    $movimentacaoService = Mockery::mock(MovimentacaoService::class);
    $movimentacaoService->shouldReceive('realizarCheckIn')
        ->once()
        ->withArgs(function ($veiculoId, $estacaoId, $reservaId, $observacao, $dataChegadaPrevista) use ($reserva, $contrato) {
            return $veiculoId === (int) $contrato->veiculo_id
                && $estacaoId === (int) $reserva->estacao_retirada_id
                && $reservaId === (int) $reserva->id
                && $observacao === 'Check-in realizado e contrato assinado'
                && $dataChegadaPrevista === $reserva->data_fim->toDateTimeString();
        });

    $reservaService = Mockery::mock(ReservaService::class);
    $reservaService->shouldReceive('checkInRealizado')->once();
    $reservaService->shouldReceive('adicionaLog')->once();

    $action = new FinalizarCheckinReservaComumAction(
        $reservaService,
        $movimentacaoService,
        app(App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction::class),
        app(App\Services\VeiculoService::class),
        app(App\Services\ModeloService::class),
        app(App\Services\UpgradeVeiculoService::class),
    );

    $response = $action->handle($registro, $request);

    expect($response->getStatusCode())->toBe(200);
});
