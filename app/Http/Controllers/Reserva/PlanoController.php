<?php

namespace App\Http\Controllers\Reserva;

use App\Http\Controllers\Controller;
use App\Models\PlanoMotoristaApp;
use Illuminate\Http\JsonResponse;

class PlanoController extends Controller
{
    /**
     * Retorna os opcionais de um PlanoMotoristaApp para uso via AJAX na renovação.
     *
     * @param  int  $id  ID do PlanoMotoristaApp
     * @return JsonResponse
     */
    public function opcionais(int $id): JsonResponse
    {
        $plano = PlanoMotoristaApp::with('opcionais')->findOrFail($id);

        $opcionais = $plano->opcionais->map(fn ($opc) => [
            'id'    => $opc->id,
            'nome'  => $opc->nome ?? $opc->label ?? 'Opcional',
            'valor' => (float) ($opc->pivot->valor ?? $opc->valor ?? 0),
        ])->values();

        return response()->json($opcionais);
    }
}
