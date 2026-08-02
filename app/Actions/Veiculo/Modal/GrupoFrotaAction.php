<?php

namespace App\Actions\Veiculo\Modal;

use App\Services\GrupoFrotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrupoFrotaAction
{
    public function __construct(private readonly GrupoFrotaService $grupoFrotaService) {}
    public function handle(Request $request): JsonResponse { return response()->json(['items' => []]); }
}
