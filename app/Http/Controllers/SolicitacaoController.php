<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Models\Escola;
use App\Models\Solicitacao;
use App\Services\BreadcrumbService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Fila da secretaria: solicitacoes feitas pelas familias no
 * portal (declaracoes, atendimento, financeiro) — resposta
 * direta que aparece para a familia.
 */
class SolicitacaoController extends BaseController implements FiltroInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        private BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Solicitações das Famílias';
        $this->nomeFiltro = 'solicitacao';

        $this->middleware('can:solicitacao.index')->only(['index', 'anexo']);
        $this->middleware('can:solicitacao.responder')->only(['responder', 'mensagem']);
        $this->middleware('can:solicitacao.filtro')->only('filtro');
        $this->middleware('can:solicitacao.removerFiltro')->only('removerFiltro');
        $this->middleware('can:solicitacao.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 25);

        $nome = $itensFiltered['nome']['valor'] ?? null;
        $status = $itensFiltered['status']['valor'] ?? null;
        $escolaId = app(\App\Support\EscolaContext::class)->filtrar()
            ? null
            : ($itensFiltered['escola_id']['valor'] ?? null);

        $solicitacoes = Solicitacao::query()
            ->with(
                'aluno.pessoa:id,nome', 'autor:id,nome', 'respondidoPor:id,nome',
                'anexos', 'mensagens.autor:id,nome', 'mensagens.anexos'
            )
            ->when($nome, fn ($q) => $q->whereHas('aluno.pessoa', fn ($p) => $p->where('nome', 'like', '%'.$nome.'%')))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($escolaId, fn ($q) => $q->where('escola_id', (int) $escolaId))
            ->orderByRaw("CASE status WHEN 'aberta' THEN 0 WHEN 'em_analise' THEN 1 WHEN 'atendida' THEN 2 ELSE 3 END")
            ->orderByDesc('id')
            ->paginate((int) $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Solicitações das Famílias', 'Alunos e Família');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('solicitacao.index', compact(
            'solicitacoes', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    /** Responde uma solicitacao — aparece na tela da familia. */
    public function responder(Request $request, $id)
    {
        $solicitacao = Solicitacao::findOrFail($id);

        $dados = $request->validate([
            'status' => ['required', Rule::in(['em_analise', 'atendida', 'negada'])],
            'resposta' => ['nullable', 'string', 'max:3000', 'required_if:status,atendida'],
        ]);

        $solicitacao->update([
            'status' => $dados['status'],
            'resposta' => $dados['resposta'] ?? null,
            'respondida_por' => auth()->id(),
            'respondida_em' => now(),
        ]);

        if (filled($dados['resposta'] ?? null)) {
            $solicitacao->mensagens()->create([
                'empresa_id' => $solicitacao->empresa_id,
                'user_id' => auth()->id(),
                'mensagem' => $dados['resposta'],
            ]);
        }

        return redirect()->route('solicitacao.index')->with('success', 'Solicitação atualizada.');
    }

    /**
     * Mensagem da secretaria dentro da conversa: pedir esclarecimento
     * ou outro documento sem encerrar a solicitacao.
     */
    public function mensagem(Request $request, $id)
    {
        $solicitacao = Solicitacao::findOrFail($id);

        abort_if(! $solicitacao->conversaAberta(), 422, 'Esta solicitação já foi finalizada.');

        $dados = $request->validate([
            'mensagem' => ['required', 'string', 'max:3000'],
        ]);

        $solicitacao->mensagens()->create([
            'empresa_id' => $solicitacao->empresa_id,
            'user_id' => auth()->id(),
            'mensagem' => $dados['mensagem'],
        ]);

        if ($solicitacao->status === 'aberta') {
            $solicitacao->update(['status' => 'em_analise']);
        }

        return redirect()->route('solicitacao.index')->with('success', 'Mensagem enviada à família.');
    }

    /** Baixa um anexo enviado pela familia junto da solicitacao. */
    public function anexo($id, $anexoId)
    {
        $anexo = \App\Models\SolicitacaoAnexo::query()
            ->where('solicitacao_id', $id)
            ->findOrFail($anexoId);

        return \Illuminate\Support\Facades\Storage::disk('public')
            ->response($anexo->arquivo_path, $anexo->nomeExibicao());
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $this->removeSessao($this->nomeFiltro, $request->input('atributo'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'solicitacoes');
        session()->put("$this->nomeFiltro.rotaRedirect", 'solicitacao.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Aluno', 'type' => 'text', 'typeWhere' => 'like'],
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todos', 'selected' => false]],
                    collect(Solicitacao::STATUS)
                        ->map(fn ($label, $v) => ['value' => $v, 'label' => $label, 'selected' => false])
                        ->values()->all()
                ),
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todas', 'selected' => false]],
                    Escola::query()->orderBy('nome')->get(['id', 'nome'])
                        ->map(fn ($e) => ['value' => $e->id, 'label' => $e->nome, 'selected' => false])
                        ->values()->all()
                ),
            ],
        ];
    }
}
