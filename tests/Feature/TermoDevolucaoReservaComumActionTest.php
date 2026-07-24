<?php

use App\Actions\Reserva\Checkout\TermoDevolucao\Exibir\ExibirTermoDevolucaoReservaComumAction;
use App\Models\ContratoCheckout;
use App\Models\Reserva;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

it('returns a response when a checkout pdf already exists on storage', function () {
    Storage::fake('public');
    Storage::disk('public')->put('contratos/checkout/existente.pdf', 'pdf-content');

    $reserva = new Reserva();
    $reserva->id = 123;
    $reserva->setRelation('contratoCheckout', new ContratoCheckout([
        'pdf_path' => 'contratos/checkout/existente.pdf',
    ]));

    $action = app(ExibirTermoDevolucaoReservaComumAction::class);
    $response = $action->servirPdf($reserva);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toContain('application/pdf');
});
