<?php

namespace App\Http\Controllers;

use App\Models\Nfse;
use App\Models\Empresa;
use App\Support\EmpresaContext;
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
        [$podeImportar, $ultimaImportacao, $proximaImportacao] = $this->statusImportacao();

        return view('nfse.index', [
            'nfseList' => $nfseList,
            'titulo' => 'Notas Fiscais',
            'podeImportar' => $podeImportar,
            'ultimaImportacao' => $ultimaImportacao,
            'proximaImportacao' => $proximaImportacao,
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

    public function importarPendentes(Request $request)
    {
        $empresa = $this->resolveEmpresaImportacao();
        if (!$empresa) {
            return redirect()
                ->back()
                ->withErrors(['nfse' => 'Selecione uma empresa para importar notas fiscais.']);
        }

        if (($empresa->nfse_ativo ?? 'N') !== 'S') {
            return redirect()
                ->back()
                ->withErrors(['nfse' => 'NFS-e nao esta ativa para a empresa selecionada.']);
        }

        $ultima = $empresa->nfse_ultima_importacao_em;
        if ($ultima && $ultima->copy()->addHour()->isFuture()) {
            return redirect()
                ->back()
                ->withErrors(['nfse' => 'Aguarde ate ' . $ultima->copy()->addHour()->format('d/m/Y H:i') . ' para importar novamente.']);
        }

        $resultado = $this->nfseService->sincronizarPendentes((int) $empresa->id);
        $empresa->nfse_ultima_importacao_em = now();
        $empresa->save();

        return redirect()
            ->back()
            ->with('success', sprintf(
                'Importacao concluida. %d atualizadas, %d ignoradas (janela 1h), %d erros.',
                $resultado['atualizadas'],
                $resultado['ignoradas'],
                $resultado['erros']
            ));
    }

    private function resolveEmpresaImportacao(): ?Empresa
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if ($user->isAdministrador()) {
            /** @var EmpresaContext $empresaContext */
            $empresaContext = app(EmpresaContext::class);
            $empresaId = $empresaContext->empresaId() ?? $user->empresa_id;
        } else {
            $empresaId = $user->empresa_id;
        }

        if (!$empresaId) {
            return null;
        }

        return Empresa::query()->find((int) $empresaId);
    }

    /**
     * @return array{0:bool,1:\Carbon\Carbon|null,2:\Carbon\Carbon|null}
     */
    private function statusImportacao(): array
    {
        $empresa = $this->resolveEmpresaImportacao();
        if (!$empresa) {
            return [false, null, null];
        }

        $ultima = $empresa->nfse_ultima_importacao_em;
        if (!$ultima) {
            return [true, null, null];
        }

        $proxima = $ultima->copy()->addHour();
        $pode = now()->greaterThanOrEqualTo($proxima);

        return [$pode, $ultima, $proxima];
    }
}
