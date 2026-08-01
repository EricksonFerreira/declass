<?php

namespace App\Actions\Reserva\Avaria\EditarCobranca;

use App\Models\AvariaCobranca;
use Illuminate\Support\Facades\Auth;

class EditarCobrancaAvariaReservaComumAction
{
    public function handle(AvariaCobranca $cobranca, array $dados): AvariaCobranca
    {
        $cobranca->forceFill([
            'valor' => (float) ($dados['valor'] ?? $cobranca->valor),
            'descricao' => (string) ($dados['descricao'] ?? $cobranca->descricao),
            'metodo' => $dados['metodo'] ?? $cobranca->metodo,
            'status' => $cobranca->status ?: AvariaCobranca::STATUS_PENDENTE,
            'dano_veiculo_ids' => $dados['dano_veiculo_ids'] ?? $cobranca->dano_veiculo_ids,
            'usuario_responsavel_id' => Auth::id(),
        ])->save();

        return $cobranca->fresh();
    }
}
