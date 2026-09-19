<?php

namespace App\Http\Controllers;

use App\Actions\Licitacao\DeleteLicitacaoAction;
use App\Actions\Licitacao\SaveLicitacaoAction;
use App\Models\Licitacao;
use App\Services\Licitacao\AnaliseLicitacaoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

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
            'texto_edital' => 'nullable|string|max:400000',
        ]);

        $action->handle($data);

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

    public function updateTexto(Request $request, Licitacao $licitacao): RedirectResponse
    {
        $data = $request->validate(['texto_edital' => 'required|string|max:400000']);
        $licitacao->update(['texto_edital' => $data['texto_edital']]);

        return redirect()->route('licitacoes.show', $licitacao)
            ->with('success', 'Texto do edital salvo.');
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
