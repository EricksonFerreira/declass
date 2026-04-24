<?php

namespace App\Http\Controllers;

use App\Models\EmpresaAlerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class EmpresaAlertaController extends Controller
{
    public function resolver(EmpresaAlerta $alerta): JsonResponse
    {
        $empresa = Auth::user()?->empresa;

        // Apenas a própria empresa ou superadmin pode resolver
        if (!$empresa || ($alerta->empresa_id !== $empresa->id && !Auth::user()->isAdministrador())) {
            abort(403);
        }

        $alerta->resolver(Auth::id());

        return response()->json(['ok' => true]);
    }
}
