<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mensalidade\GerarMensalidadesRequest;
use App\Http\Requests\Mensalidade\StoreMensalidadeRequest;
use App\Http\Requests\Mensalidade\UpdateMensalidadeRequest;
use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\MensalidadeService;
use Illuminate\Http\Request;

class MensalidadeController extends BaseController
{
    public function __construct(
        protected MensalidadeService $service,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Mensalidades';

        $this->middleware('can:mensalidade.index')->only('index');
        $this->middleware('can:mensalidade.create')->only(['create', 'store']);
        $this->middleware('can:mensalidade.edit')->only(['edit', 'update', 'baixar']);
        $this->middleware('can:mensalidade.destroy')->only('destroy');
        $this->middleware('can:mensalidade.gerar')->only('gerar');
        $this->middleware('can:mensalidade.recibo')->only('recibo');
    }

    public function index(Request $request)
    {
        $filters = $request->only(['aluno_id', 'turma_id', 'competencia', 'status', 'inadimplente']);
        $mensalidades = $this->service->listarComFiltros($filters, 15);

        $inadimplencia = $this->service->inadimplentes();
        $totalInadimplente = $inadimplencia->flatten()->count();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Mensalidades', 'Financeiro');

        return view('mensalidade.index', [
            'titulo' => $this->titulo,
            'mensalidades' => $mensalidades,
            'filters' => $filters,
            'alunos' => $this->alunosOpcoes(),
            'turmas' => $this->turmasOpcoes(),
            'statuses' => ['pendente' => 'Pendente', 'vencida' => 'Vencida', 'paga' => 'Paga', 'cancelada' => 'Cancelada'],
            'totalInadimplente' => $totalInadimplente,
            'breadcrumb' => $breadcrumb,
        ]);
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
        $data['status'] = $data['status'] ?? 'pendente';
        $data['plano_mensalidade_id'] = Matricula::find($data['matricula_id'])?->plano_mensalidade_id;

        $this->service->create($data);

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
        $this->service->update($id, $request->validated());

        return redirect()->route('mensalidade.index')->with('success', 'Mensalidade atualizada com sucesso.');
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

    private function alunosOpcoes(): array
    {
        return Aluno::query()->with('pessoa')->orderBy('id')->get()
            ->mapWithKeys(fn (Aluno $a) => [
                $a->id => $a->pessoa?->nome ?? ('Aluno #'.$a->id),
            ])->all();
    }

    private function turmasOpcoes(): array
    {
        return Turma::query()->orderBy('nome')->pluck('nome', 'id')->all();
    }
}
