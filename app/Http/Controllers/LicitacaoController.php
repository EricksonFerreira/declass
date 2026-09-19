<?php

namespace App\Http\Controllers;

use App\Actions\Licitacao\DeleteLicitacaoAction;
use App\Actions\Licitacao\SaveLicitacaoAction;
use App\Models\Licitacao;
use App\Services\Licitacao\AnaliseLicitacaoService;
use App\Services\Licitacao\EditalArquivoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LicitacaoController extends Controller
{
    public function index(Request $request): View
    {
        $licitacoes = Licitacao::with('ultimaAnalise')
            ->when($request->search, fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('orgao', 'like', "%{$request->search}%")
                    ->orWhere('objeto', 'like', "%{$request->search}%")
                    ->orWhere('numero_edital', 'like', "%{$request->search}%");
            }))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('data_abertura')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('licitacoes.index', [
            'titulo' => 'Licitações',
            'licitacoes' => $licitacoes,
            'statusOpcoes' => Licitacao::STATUS,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Licitações', 'active' => true],
            ],
        ]);
    }

    public function store(Request $request, SaveLicitacaoAction $action): RedirectResponse
    {
        $data = $request->validate([
            'orgao' => 'required|string|max:255',
            'objeto' => 'required|string|max:2000',
            'numero_edital' => 'nullable|string|max:100',
            'modalidade' => 'nullable|string|max:100',
            'criterio_julgamento' => 'nullable|string|max:100',
            'data_abertura' => 'nullable|date',
            'valor_estimado' => 'nullable|numeric|min:0',
            'url_edital' => 'nullable|url|max:500',
            'arquivo' => 'nullable|file|mimes:pdf,docx,txt|max:15360',
            'texto_edital' => 'nullable|string|max:400000',
        ]);

        $licitacao = $action->handle($data);

        if ($request->hasFile('arquivo')) {
            $aviso = $this->importarArquivo($request->file('arquivo'), $licitacao, app(EditalArquivoService::class));
            if ($aviso) {
                return redirect()->route('licitacoes.show', $licitacao)->with('error', $aviso);
            }
        }

        return redirect()->route('licitacoes.index')
            ->with('success', 'Licitação cadastrada! Abra-a para colar o edital e analisar.');
    }

    public function show(Licitacao $licitacao): View
    {
        $licitacao->load(['analises.usuario']);

        return view('licitacoes.show', [
            'titulo' => 'Licitação — '.$licitacao->orgao,
            'licitacao' => $licitacao,
            'statusOpcoes' => Licitacao::STATUS,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Licitações', 'url' => route('licitacoes.index')],
                ['name' => 'Detalhe', 'active' => true],
            ],
        ]);
    }

    public function update(Request $request, Licitacao $licitacao, EditalArquivoService $servico): RedirectResponse
    {
        $data = $request->validate([
            'orgao' => 'required|string|max:255',
            'objeto' => 'required|string|max:2000',
            'numero_edital' => 'nullable|string|max:100',
            'modalidade' => 'nullable|string|max:100',
            'criterio_julgamento' => 'nullable|string|max:100',
            'data_abertura' => 'nullable|date',
            'valor_estimado' => 'nullable|numeric|min:0',
            'url_edital' => 'nullable|url|max:500',
            'arquivo' => 'nullable|file|mimes:pdf,docx,txt|max:15360',
        ]);

        $licitacao->update($data);

        if ($request->hasFile('arquivo')) {
            $aviso = $this->importarArquivo($request->file('arquivo'), $licitacao, $servico);
            if ($aviso) {
                return back()->with('error', $aviso);
            }
        }

        return back()->with('success', 'Licitação atualizada.');
    }

    public function updateTexto(Request $request, Licitacao $licitacao): RedirectResponse
    {
        $data = $request->validate(['texto_edital' => 'required|string|max:400000']);
        $licitacao->update(['texto_edital' => $data['texto_edital']]);

        return redirect()->route('licitacoes.show', $licitacao)
            ->with('success', 'Texto do edital salvo.');
    }

    public function uploadArquivo(Request $request, Licitacao $licitacao, EditalArquivoService $servico): RedirectResponse
    {
        $request->validate(['arquivo' => 'required|file|mimes:pdf,docx,txt|max:15360']);

        $aviso = $this->importarArquivo($request->file('arquivo'), $licitacao, $servico);
        if ($aviso) {
            return redirect()->route('licitacoes.show', $licitacao)->with('error', $aviso);
        }

        return redirect()->route('licitacoes.show', $licitacao)
            ->with('success', 'Arquivo anexado e texto extraído — confira abaixo antes de analisar.');
    }

    public function downloadArquivo(Licitacao $licitacao): StreamedResponse
    {
        abort_unless($licitacao->arquivo_path && Storage::exists($licitacao->arquivo_path), 404);

        return Storage::download($licitacao->arquivo_path, 'edital-'.$licitacao->id.'.'.pathinfo($licitacao->arquivo_path, PATHINFO_EXTENSION));
    }

    private function importarArquivo($arquivo, Licitacao $licitacao, EditalArquivoService $servico): ?string
    {
        try {
            $chars = $servico->importar($arquivo, $licitacao);
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }

        return $chars === 0 ? 'Arquivo anexado, mas não extraí texto dele — cole o conteúdo manualmente.' : null;
    }

    public function updateStatus(Request $request, Licitacao $licitacao): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:'.implode(',', array_keys(Licitacao::STATUS))]);
        $licitacao->update(['status' => $data['status']]);

        return redirect()->route('licitacoes.show', $licitacao)
            ->with('success', 'Status atualizado.');
    }

    public function analisar(Licitacao $licitacao, AnaliseLicitacaoService $servico): RedirectResponse
    {
        try {
            $servico->analisar($licitacao, (int) auth()->id());
        } catch (RuntimeException $e) {
            return redirect()->route('licitacoes.show', $licitacao)->with('error', $e->getMessage());
        }

        return redirect()->route('licitacoes.show', $licitacao)
            ->with('success', 'Análise concluída — veja a pontuação estimada abaixo.');
    }

    public function destroy(Licitacao $licitacao, DeleteLicitacaoAction $action): RedirectResponse
    {
        $action->handle($licitacao->id);

        return redirect()->route('licitacoes.index')
            ->with('success', 'Licitação removida.');
    }
}
