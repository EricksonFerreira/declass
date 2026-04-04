<?php

namespace App\Http\Controllers;

use App\Models\Nfse;
use App\Services\NfseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NfseController extends Controller
{
    public function __construct(private readonly NfseService $nfseService)
    {
    }

    public function index(Request $request)
    {
        $query = Nfse::query()->with(['reserva', 'empresa']);

        if (!Auth::user()?->isAdministrador()) {
            $empresaId = Auth::user()?->empresa_id;
            if ($empresaId && Schema::hasColumn('nfse', 'empresa_id')) {
                $query->where('empresa_id', $empresaId);
            }
        }

        $nfseList = $query->orderByDesc('id')->paginate(15);

        return view('nfse.index', [
            'nfseList' => $nfseList,
            'titulo' => 'Notas Fiscais',
        ]);
    }

    public function download(Request $request, int $id)
    {
        $tipo = $request->query('tipo', 'xml');
        if (!in_array($tipo, ['xml', 'dps'], true)) {
            $tipo = 'xml';
        }

        $nfse = Nfse::query()->with(['empresa'])->findOrFail($id);

        if (!Auth::user()?->isAdministrador()) {
            $empresaId = Auth::user()?->empresa_id;
            if ($empresaId && (int) $nfse->empresa_id !== (int) $empresaId) {
                abort(403, 'Sem acesso a esta nota fiscal.');
            }
        }

        $result = $this->nfseService->baixarXml($nfse, $tipo);
        if (($result['status'] ?? 'error') !== 'success') {
            return redirect()
                ->back()
                ->withErrors(['nfse' => $result['message'] ?? 'Falha ao baixar XML da NFS-e.']);
        }

        $content = (string) ($result['content'] ?? '');
        $contentType = (string) ($result['content_type'] ?? 'application/xml');

        $filename = 'nfse-' . $nfse->id . '-' . $tipo . '.xml';
        return response($content, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
