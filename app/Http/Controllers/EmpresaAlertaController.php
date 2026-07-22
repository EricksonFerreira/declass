<?php

namespace App\Http\Controllers;

use App\Models\EmpresaAlerta;
use App\Services\BreadcrumbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaAlertaController extends Controller
{
    public function __construct(
        protected BreadcrumbService $breadcrumbService
    ) {}

    public function index(Request $request)
    {
        $empresa = Auth::user()?->empresa;
        if (!$empresa) {
            abort(403);
        }

        $filtros = [
            'resolvido' => $request->query('resolvido', 'N'),
        ];

        $query = EmpresaAlerta::where('empresa_id', $empresa->id)->orderByDesc('id');

        if ($filtros['resolvido'] === 'N') {
            $query->where('resolvido', false);
        } elseif ($filtros['resolvido'] === 'S') {
            $query->where('resolvido', true);
        }

        $alertas = $query->paginate(15);
        $titulo = 'Alertas e Pendências';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Alertas e Pendências', null),
        ]);

        return view('alerta.index', compact('alertas', 'filtros', 'titulo', 'breadcrumb'));
    }

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
