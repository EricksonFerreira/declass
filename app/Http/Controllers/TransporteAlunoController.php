<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\TransporteAluno\StoreTransporteAlunoRequest;
use App\Http\Requests\TransporteAluno\UpdateTransporteAlunoRequest;
use App\Models\Aluno;
use App\Models\ParadaTransporte;
use App\Models\RotaTransporte;
use App\Models\TransporteAluno;
use App\Services\BreadcrumbService;
use App\Services\TransporteAlunoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class TransporteAlunoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected TransporteAlunoService $transporteService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Passageiros do Transporte';
        $this->nomeFiltro = 'transporte_aluno';

        $this->middleware('can:transporte_aluno.index')->only('index');
        $this->middleware('can:transporte_aluno.create')->only('create');
        $this->middleware('can:transporte_aluno.store')->only('store');
        $this->middleware('can:transporte_aluno.edit')->only('edit');
        $this->middleware('can:transporte_aluno.update')->only('update');
        $this->middleware('can:transporte_aluno.destroy')->only('destroy');
        $this->middleware('can:transporte_aluno.filtro')->only('filtro');
        $this->middleware('can:transporte_aluno.removerFiltro')->only('removerFiltro');
        $this->middleware('can:transporte_aluno.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $nome = $itensFiltered['nome']['valor'] ?? null;

        $passageiros = $this->transporteService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['aluno.pessoa', 'rota.escola', 'rota.veiculo', 'parada'], 'id', 'desc',
            fn ($q) => $q->when($nome, fn ($w) => $w->whereHas('aluno.pessoa', fn ($p) => $p->where('nome', 'like', '%'.$nome.'%')))
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Passageiros do Transporte', 'Transporte');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('transporte_aluno.index', compact(
            'passageiros', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Passageiros do Transporte', 'transporte_aluno.index', 'Novo Passageiro', 'Transporte');

        return view('transporte_aluno.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario(null)))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreTransporteAlunoRequest $request)
    {
        $this->transporteService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Passageiro vinculado com sucesso.');
    }

    public function edit($id)
    {
        $passageiro = $this->transporteService->getById($id, ['aluno.pessoa', 'rota', 'parada']);
        abort_if(! $passageiro, 404, 'Vínculo não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Passageiros do Transporte', 'transporte_aluno.index', "Editar: {$passageiro->aluno->pessoa->nome}", 'Transporte');

        return view('transporte_aluno.add-edit', array_merge(compact('passageiro', 'breadcrumb'), $this->opcoesFormulario($passageiro->rota_id)))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateTransporteAlunoRequest $request, $id)
    {
        $passageiro = $this->transporteService->update($id, $request->validated());
        abort_if(! $passageiro, 403, 'Sem acesso para editar este vínculo.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Vínculo atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->transporteService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este vínculo.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'transporte_aluno');
        session()->put("$this->nomeFiltro.rotaRedirect", 'transporte_aluno.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Aluno', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'ativo', 'label' => 'Situação', 'type' => 'select', 'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo']],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'rota_id', 'label' => 'Rota', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => RotaTransporte::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn ($r) => ['value' => $r->id, 'label' => $r->nome])->all(),
            ],
        ];
    }

    private function opcoesFormulario(?int $rotaId = null): array
    {
        $alunosComTransporte = TransporteAluno::query()
            ->where('ativo', 'S')
            ->when($rotaId, fn ($q) => $q->where('rota_id', '!=', $rotaId))
            ->pluck('aluno_id');

        return [
            'alunos' => Aluno::query()->with('pessoa')
                ->whereNotIn('id', $alunosComTransporte)
                ->orderBy('id')
                ->get()
                ->mapWithKeys(fn ($a) => [$a->id => ($a->pessoa->nome ?? 'Aluno #'.$a->id).' — '.$a->registro_academico])
                ->all(),
            'rotas' => RotaTransporte::query()->where('ativo', 'S')->orderBy('nome')
                ->get(['id', 'nome'])
                ->mapWithKeys(fn ($r) => [$r->id => $r->nome])->all(),
            'paradasPorRota' => ParadaTransporte::query()
                ->orderBy('ordem')->get(['id', 'rota_id', 'nome'])
                ->groupBy('rota_id')
                ->map(fn ($lista) => $lista->mapWithKeys(fn ($p) => [$p->id => $p->nome])->all())
                ->toJson(),
        ];
    }
}
