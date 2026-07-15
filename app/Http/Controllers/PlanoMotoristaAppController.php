<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\PlanoMotoristaApp\StorePlanoMotoristaAppRequest;
use App\Http\Requests\PlanoMotoristaApp\UpdatePlanoMotoristaAppRequest;
use App\Models\Opcional;
use App\Services\BreadcrumbService;
use App\Services\PlanoMotoristaAppService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class PlanoMotoristaAppController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    public function __construct(
        protected PlanoMotoristaAppService $planoMotoristaAppService,
        protected BreadcrumbService $breadcrumbService
    ) {
        $this->titulo = 'Planos para Motorista de App';
        $this->nomeFiltro = 'plano_motorista_app';
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);
        $planos = $this->planoMotoristaAppService->getAllWithFiltersAndPagination($itensFiltered, $perPage, ['opcionais'], 'ordem', 'asc');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Planos Motorista App');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();
        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        return view('plano_motorista_app.index', compact('planos', 'breadcrumb', 'filters', 'itensFiltered', 'additionalFilters', 'perPage'))
            ->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Planos Motorista App', 'plano_motorista_app.index', 'Criar');
        [$metodo, $rota] = $this->getFormMethodAndRoute();

        $opcionais = $this->getOpcionaisParaSelecao((array) old('opcionais', []));
        $status = ['S' => 'Ativo', 'N' => 'Inativo'];
        $periodos = [
            'semana' => 'Semana',
            'quinze' => 'Quinze dias',
            'mes' => 'Mês',
            'trimestre' => 'Trimestre',
            'semestre' => 'Semestre',
            'ano' => 'Ano',
        ];

        return view('plano_motorista_app.add-edit', compact('breadcrumb', 'metodo', 'rota', 'status', 'periodos', 'opcionais'))
            ->with('titulo', $this->titulo);
    }

    public function store(StorePlanoMotoristaAppRequest $request)
    {
        $data = $request->validated();
        $opcionais = $data['opcionais'] ?? [];
        unset($data['opcionais']);

        $plano = $this->planoMotoristaAppService->create($data);
        $plano->opcionais()->sync($opcionais);

        return redirect()->route('plano_motorista_app.index')
            ->with('success', 'Plano para motorista de app criado com sucesso.');
    }

    public function edit($id)
    {
        $plano = $this->planoMotoristaAppService->getById($id, ['opcionais']);
        abort_unless($plano, 404);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Planos Motorista App', 'plano_motorista_app.index', "Editar: {$plano->nome}");
        [$metodo, $rota] = $this->getFormMethodAndRoute($plano);
        $opcionais = $this->getOpcionaisParaSelecao((array) old('opcionais', $plano->opcionais->pluck('id')->toArray()));
        $status = ['S' => 'Ativo', 'N' => 'Inativo'];
        $periodos = [
            'semana' => 'Semana',
            'quinze' => 'Quinze dias',
            'mes' => 'Mês',
            'trimestre' => 'Trimestre',
            'semestre' => 'Semestre',
            'ano' => 'Ano',
        ];

        return view('plano_motorista_app.add-edit', compact('plano', 'breadcrumb', 'metodo', 'rota', 'status', 'periodos', 'opcionais'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdatePlanoMotoristaAppRequest $request, $id)
    {
        $data = $request->validated();
        $opcionais = $data['opcionais'] ?? [];
        unset($data['opcionais']);

        $plano = $this->planoMotoristaAppService->update($id, $data);
        if ($plano) {
            $plano->opcionais()->sync($opcionais);
        }

        return redirect()->route('plano_motorista_app.index')
            ->with('success', 'Plano para motorista de app atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $this->planoMotoristaAppService->delete($id);

        return redirect()->route('plano_motorista_app.index')
            ->with('success', 'Plano para motorista de app removido.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'plano_motorista_app');
        session()->put("$this->nomeFiltro.rotaRedirect", 'plano_motorista_app.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'periodo', 'label' => 'Período', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Todos', 'selected' => false],
                ['value' => 'semana', 'label' => 'Semana', 'selected' => false],
                ['value' => 'quinze', 'label' => 'Quinze dias', 'selected' => false],
                ['value' => 'mes', 'label' => 'Mês', 'selected' => false],
                ['value' => 'trimestre', 'label' => 'Trimestre', 'selected' => false],
                ['value' => 'semestre', 'label' => 'Semestre', 'selected' => false],
                ['value' => 'ano', 'label' => 'Ano', 'selected' => false],
            ]],
            ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Todos', 'selected' => false],
                ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                ['value' => 'N', 'label' => 'Inativo', 'selected' => false],
            ]],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }

    private function getFormMethodAndRoute($plano = null): array
    {
        return $plano
            ? ['PUT', route('plano_motorista_app.update', $plano->id)]
            : ['POST', route('plano_motorista_app.store')];
    }

    private function getOpcionaisParaSelecao(array $idsSelecionados = [])
    {
        $idsSelecionados = collect($idsSelecionados)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $opcionais = Opcional::query()
            ->with('tipoOpcional')
            ->where('ativo', 'S')
            ->orderBy('nome')
            ->get()
            ->keyBy('id');

        if (! empty($idsSelecionados)) {
            $opcionaisSelecionadosInativos = Opcional::query()
                ->with('tipoOpcional')
                ->whereIn('id', $idsSelecionados)
                ->where('ativo', '!=', 'S')
                ->orderBy('nome')
                ->get();

            foreach ($opcionaisSelecionadosInativos as $opcional) {
                $opcionais->put($opcional->id, $opcional);
            }
        }

        return $opcionais->sortBy('nome')->values();
    }
}
