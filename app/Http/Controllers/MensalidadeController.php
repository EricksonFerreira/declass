<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Requests\Mensalidade\GerarMensalidadesRequest;
use App\Http\Requests\Mensalidade\StoreMensalidadeRequest;
use App\Http\Requests\Mensalidade\UpdateMensalidadeRequest;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\MensalidadeService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class MensalidadeController extends BaseController implements FiltroInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected MensalidadeService $service,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Mensalidades';
        $this->nomeFiltro = 'mensalidade';

        $this->middleware('can:mensalidade.index')->only('index');
        $this->middleware('can:mensalidade.create')->only(['create', 'store']);
        $this->middleware('can:mensalidade.edit')->only(['edit', 'update', 'baixar', 'boleto']);
        $this->middleware('can:mensalidade.destroy')->only('destroy');
        $this->middleware('can:mensalidade.gerar')->only('gerar');
        $this->middleware('can:mensalidade.recibo')->only('recibo');
        $this->middleware('can:mensalidade.filtro')->only('filtro');
        $this->middleware('can:mensalidade.removerFiltro')->only('removerFiltro');
        $this->middleware('can:mensalidade.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $mensalidades = $this->service->listarComFiltros($itensFiltered, (int) $perPage);
        $resumo = $this->service->resumoIndex($itensFiltered);

        $inadimplencia = $this->service->inadimplentes();
        $totalInadimplente = $inadimplencia->flatten()->count();

        // Extrato individual: quando o filtro aluno_id esta ativo
        // (escolhido pelo modal de busca), mostramos o banner do aluno.
        $alunoSel = null;
        if ($alunoId = ($itensFiltered['aluno_id']['valor'] ?? null)) {
            $alunoSel = \App\Models\Aluno::with('pessoa')->find($alunoId);
        }

        $alunosPicker = Aluno::query()->with('pessoa')->orderBy('id')->get()
            ->map(fn (Aluno $a) => [
                'id' => $a->id,
                'rotulo' => $a->pessoa->nome ?? ('Aluno #'.$a->id),
                'detalhe' => 'RA '.($a->registro_academico ?? '—'),
            ])->values()->all();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Mensalidades', 'Financeiro');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('mensalidade.index', compact(
            'mensalidades', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro', 'totalInadimplente',
            'resumo', 'alunosPicker', 'alunoSel'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Mensalidades', 'mensalidade.index', 'Nova Mensalidade', 'Financeiro');

        return view('mensalidade.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreMensalidadeRequest $request)
    {
        $data = $request->validated();
        unset($data['boleto']);
        $data['status'] = $data['status'] ?? 'pendente';
        $data['plano_mensalidade_id'] = Matricula::find($data['matricula_id'])?->plano_mensalidade_id;

        $mensalidade = $this->service->create($data);

        if ($request->hasFile('boleto')) {
            $this->service->salvarBoleto($mensalidade, $request->file('boleto'));
        }

        return redirect()->route('mensalidade.index')->with('success', 'Mensalidade criada com sucesso.');
    }

    public function edit($id)
    {
        $mensalidade = $this->service->getById($id, ['matricula.aluno.pessoa', 'matricula.turma']);
        abort_if(! $mensalidade, 404, 'Mensalidade não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Mensalidades', 'mensalidade.index', 'Editar: competência '.$mensalidade->competencia, 'Financeiro');

        return view('mensalidade.add-edit', array_merge(compact('mensalidade', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateMensalidadeRequest $request, $id)
    {
        $data = $request->validated();
        unset($data['boleto'], $data['remover_boleto']);

        $mensalidade = $this->service->update($id, $data);

        if ($mensalidade) {
            if ($request->boolean('remover_boleto')) {
                $this->service->removerBoleto($mensalidade);
            }

            if ($request->hasFile('boleto')) {
                $this->service->salvarBoleto($mensalidade, $request->file('boleto'));
            }
        }

        return redirect()->route('mensalidade.index')->with('success', 'Mensalidade atualizada com sucesso.');
    }

    /** Abre o PDF do boleto anexado a mensalidade. */
    public function boleto($id)
    {
        $mensalidade = $this->service->getById($id);
        abort_if(! $mensalidade?->boleto_path, 404, 'Boleto não encontrado.');

        return \Illuminate\Support\Facades\Storage::disk('public')
            ->response($mensalidade->boleto_path, $mensalidade->boletoNomeDownload());
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return redirect()->route('mensalidade.index')->with('success', 'Mensalidade excluída com sucesso.');
    }

    /** Baixa: marca a mensalidade como paga. */
    public function baixar(Request $request, $id)
    {
        $mensalidade = $this->service->getById($id);
        abort_if(! $mensalidade, 404);

        try {
            $this->service->baixar(
                $mensalidade,
                $request->input('forma_pagamento'),
                $request->input('data_pagamento')
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Mensalidade baixada com sucesso.');
    }

    /** Recibo de pagamento em PDF — somente mensalidades baixadas. */
    public function recibo($id)
    {
        $mensalidade = $this->service->getById($id, [
            'matricula.aluno.pessoa', 'matricula.turma.escola', 'matricula.turma.anoLetivo',
            'planoMensalidade',
        ]);
        abort_if(! $mensalidade, 404);
        abort_if($mensalidade->status !== 'paga', 404, 'Recibo disponível apenas para mensalidades pagas.');

        return app(\App\Services\PdfService::class)->download(
            'mensalidade.recibo-pdf',
            ['mensalidade' => $mensalidade],
            'recibo-mensalidade-'.$mensalidade->id.'.pdf'
        );
    }

    /** Gera mensalidades em lote para uma competencia. */
    public function gerar(GerarMensalidadesRequest $request)
    {
        $data = $request->validated();
        $resultado = $this->service->gerarCompetencia($data['competencia'], $data['vencimento']);

        return redirect()->route('mensalidade.index')
            ->with('success', "Competência {$data['competencia']}: {$resultado['criadas']} mensalidade(s) gerada(s), {$resultado['ignoradas']} já existente(s).");
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
        session()->put("$this->nomeFiltro.urlPadrao", 'mensalidade');
        session()->put("$this->nomeFiltro.rotaRedirect", 'mensalidade.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'busca', 'label' => 'Buscar', 'type' => 'text', 'typeWhere' => 'like',
                'placeholder' => 'Aluno ou RA...'],
            [
                'name' => 'competencia', 'label' => 'Competência', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todas', 'selected' => false]],
                    Mensalidade::query()->select('competencia')->distinct()
                        ->orderByDesc('competencia')->limit(24)->pluck('competencia')
                        ->map(fn ($c) => ['value' => $c, 'label' => $c, 'selected' => false])
                        ->values()->all()
                ),
            ],
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todos', 'selected' => false]],
                    collect(['pendente' => 'Pendente', 'vencida' => 'Vencida', 'paga' => 'Paga', 'cancelada' => 'Cancelada'])
                        ->map(fn ($label, $v) => ['value' => $v, 'label' => $label, 'selected' => false])
                        ->values()->all()
                ),
            ],
            [
                'name' => 'turma_id', 'label' => 'Turma', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todas', 'selected' => false]],
                    Turma::query()->orderBy('nome')->get(['id', 'nome'])
                        ->map(fn ($t) => ['value' => $t->id, 'label' => $t->nome, 'selected' => false])
                        ->values()->all()
                ),
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }

    private function opcoesFormulario(): array
    {
        return [
            'matriculas' => Matricula::query()
                ->where('status', 'ativa')
                ->with(['aluno.pessoa', 'turma'])
                ->get()
                ->mapWithKeys(fn (Matricula $m) => [
                    $m->id => ($m->aluno->pessoa->nome ?? 'Aluno').' — '.($m->turma->nome ?? ''),
                ])->all(),
            'statuses' => Mensalidade::STATUS,
            'formasPagamento' => Mensalidade::FORMAS_PAGAMENTO,
        ];
    }
}
