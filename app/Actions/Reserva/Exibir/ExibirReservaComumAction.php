<?php

namespace App\Actions\Reserva\Exibir;

use App\DTO\Reserva\ReservaResolvida;
use App\Models\Reserva;

class ExibirReservaComumAction
{
    public function handle(ReservaResolvida $reserva): mixed
    {
        $registro = $reserva->registro;
        $registro->load([
            'criadoPor', 'nfseAtual', 'estacaoRetirada', 'estacaoDevolucao',
            'grupoFrota', 'cupomDesconto', 'opcionais',
            'transacoes' => fn ($q) => $q->with(['user', 'pagoPor'])->orderByDesc('id'),
            'contratoCheckin.grupoFrotaOriginal', 'contratoCheckin.veiculo.marca',
            'contratoCheckin.usuarioUpgrade', 'contratoCheckout', 'calcao',
            'veiculoDanos' => fn ($q) => $q->with('imagem')->orderByDesc('id')->take(10),
            'avariaCobrancas' => fn ($q) => $q->orderByDesc('id')->take(10),
            'multas' => fn ($q) => $q->orderByDesc('id')->take(5),
        ]);

        return view('reserva.show', [
            'reserva' => $registro,
            'codigo' => $reserva->codigo,
        ]);
    }
}
