<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Models\DocumentoSolicitacao;
use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Pedidos de documento feitos pela escola a familia:
 * a secretaria solicita (com prazo) e confere/devolve o que
 * a familia envia pelo portal.
 */
class DocumentoSolicitacaoController extends BaseController implements FiltroInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        private BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Documentos Solicitados';
        $this->nomeFiltro = 'documento_solicitacao';

        $this->middleware('can:documento_solicitacao.index')->only('index');
        $this->middleware('can:documento_solicitacao.store')->only('store');
        $this->middleware('can:documento_solicitacao.update')->only('update');
        $this->middleware('can:documento_solicitacao.arquivo')->only('arquivo');
        $this->middleware('can:documento_solicitacao.filtro')->only('filtro');
        $this->middleware('can:documento_solicitacao.removerFiltro')->only('removerFiltro');
        $this->middleware('can:documento_solicitacao.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 25);

        $nome = $itensFiltered['nome']['valor'] ?? null;
        $status = $itensFiltered['status']['valor'] ?? null;
        $realizada = $itensFiltered['realizada']['valor'] ?? null;
        $escolaId = app(\App\Support\EscolaContext::class)->filtrar()
            ? null
            : ($itensFiltered['escola_id']['valor'] ?? null);

        $solicitacoes = DocumentoSolicitacao::query()
            ->with('aluno.pessoa:id,nome')
            ->when($nome, fn ($q) => $q->whereHas('aluno.pessoa', fn ($p) => $p->where('nome', 'like', '%'.$nome.'%')))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($realizada === 'S', fn ($q) => $q->whereIn('status', ['enviado', 'aceito']))
            ->when($realizada === 'N', fn ($q) => $q->whereIn('status', ['pendente', 'devolvido']))
            ->when($escolaId, fn ($q) => $q->where('escola_id', (int) $escolaId))
            ->orderByRaw("CASE status WHEN 'enviado' THEN 0 WHEN 'devolvido' THEN 1 WHEN 'pendente' THEN 2 ELSE 3 END")
            ->orderByDesc('id')
            ->paginate((int) $perPage);

        $alunosPicker = \App\Models\Aluno::query()
            ->whereHas('matriculas', fn ($q) => $q->where('status', 'ativa'))
            ->with(['pessoa:id,nome', 'escola:id,nome'])
            ->orderBy('id')
            ->get(['id', 'pessoa_id', 'escola_id', 'registro_academico'])
            ->sortBy(fn ($a) => $a->pessoa->nome ?? '')
            ->values()
            ->map(fn ($a) => [
                'id' => $a->id,
                'rotulo' => $a->pessoa->nome ?? "Aluno #{$a->id}",
                'detalhe' => trim(($a->registro_academico ? 'RA '.$a->registro_academico.' · ' : '').($a->escola->nome ?? ''), ' ·'),
                'escola_id' => $a->escola_id,
                'escola' => $a->escola->nome ?? '',
            ])
            ->all();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Documentos Solicitados', 'Alunos e Família');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('documento_solicitacao.index', compact(
            'solicitacoes', 'alunosPicker', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    /** Secretaria pede um documento da familia (com prazo). */
    public function store(Request $request)
    {
        $dados = $request->validate([
            'aluno_id' => ['required', 'integer', 'exists:alunos,id'],
            'tipo' => ['required', 'string', 'max:120'],
            'titulo' => ['nullable', 'string', 'max:200'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'prazo' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $aluno = \App\Models\Aluno::findOrFail($dados['aluno_id']);

        DocumentoSolicitacao::create($dados + [
            'empresa_id' => $aluno->empresa_id,
            'escola_id' => $aluno->escola_id,
            'user_id' => auth()->id(),
            'titulo' => $dados['titulo'] ?? $dados['tipo'],
            'status' => 'pendente',
        ]);

        return redirect()->route('documento_solicitacao.index')
            ->with('success', 'Documento solicitado à família.');
    }

    /** Confere (aceita) ou devolve o arquivo enviado pela familia. */
    public function update(Request $request, $id)
    {
        $solicitacao = DocumentoSolicitacao::findOrFail($id);

        $dados = $request->validate([
            'status' => ['required', Rule::in(['aceito', 'devolvido', 'enviado'])],
            'observacao' => ['nullable', 'string', 'max:1000', 'required_if:status,devolvido'],
            'arquivo' => ['required_if:status,enviado', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:8192'],
        ]);

        if ($dados['status'] === 'enviado' && $request->hasFile('arquivo')) {
            // Entrega registrada pela propria escola (arquivo entregue em maos ou por fora do portal)
            $dados['arquivo_path'] = $request->file('arquivo')->store(
                "empresas/{$solicitacao->empresa_id}/documentos-solicitados/{$solicitacao->id}", 'public');
            $dados['enviado_em'] = now();
        }
        unset($dados['arquivo']);

        $solicitacao->update($dados);

        return redirect()->route('documento_solicitacao.index')
            ->with('success', 'Solicitação atualizada.');
    }

    /** Baixa o arquivo enviado pela familia (conferencia). */
    public function arquivo($id)
    {
        $solicitacao = DocumentoSolicitacao::findOrFail($id);
        abort_unless($solicitacao->arquivo_path, 404, 'Nenhum arquivo enviado.');

        return \Illuminate\Support\Facades\Storage::disk('public')->response($solicitacao->arquivo_path);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'documentos-solicitados');
        session()->put("$this->nomeFiltro.rotaRedirect", 'documento_solicitacao.index');
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
                    collect(DocumentoSolicitacao::STATUS)
                        ->map(fn ($label, $v) => ['value' => $v, 'label' => $label, 'selected' => false])
                        ->values()->all()
                ),
            ],
            [
                'name' => 'realizada', 'label' => 'Realizada', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => [
                    ['value' => '', 'label' => 'Todas', 'selected' => false],
                    ['value' => 'S', 'label' => 'Já realizadas', 'selected' => false],
                    ['value' => 'N', 'label' => 'Não realizadas', 'selected' => false],
                ],
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
