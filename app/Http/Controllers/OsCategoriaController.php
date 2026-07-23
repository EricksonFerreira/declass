<?php

namespace App\Http\Controllers;

use App\Models\OsCategoria;
use App\Http\Requests\StoreOsCategoriaRequest;
use App\Http\Requests\UpdateOsCategoriaRequest;
use Illuminate\Http\Request;

use App\Traits\FiltroTrait;
use App\Traits\AtualizaFiltrosTrait;

class OsCategoriaController extends Controller
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    protected $nomeFiltro = 'os_categorias';

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $query = OsCategoria::query();

        if (!empty($itensFiltered)) {
            foreach ($itensFiltered as $filter) {
                if ($filter['typeWhere'] === 'like') {
                    $query->where($filter['name'], 'like', "%{$filter['valor']}%");
                } elseif ($filter['typeWhere'] === 'where') {
                    $query->where($filter['name'], $filter['valor']);
                }
            }
        }

        $categorias = $query->paginate($perPage);

        $filters = $this->obterFiltrosPrincipais();
        $this->atualizarFiltros($itensFiltered, $filters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('os-categorias.index', compact('categorias', 'filters', 'itensFiltered', 'perPages', 'perPage'))
            ->with('titulo', 'Categorias de OS')
            ->with('breadcrumb', []);
    }

    public function create()
    {
        return view('os-categorias.create');
    }

    public function store(StoreOsCategoriaRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');
        
        OsCategoria::create($data);

        return redirect()->route('os-categorias.index')
            ->with('success', 'Categoria de OS criada com sucesso!');
    }

    public function edit(OsCategoria $osCategoria)
    {
        return view('os-categorias.edit', compact('osCategoria'));
    }

    public function update(UpdateOsCategoriaRequest $request, OsCategoria $osCategoria)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');

        $osCategoria->update($data);

        return redirect()->route('os-categorias.index')
            ->with('success', 'Categoria de OS atualizada com sucesso!');
    }

    public function destroy(OsCategoria $osCategoria)
    {
        $osCategoria->delete();

        return redirect()->route('os-categorias.index')
            ->with('success', 'Categoria de OS excluída com sucesso!');
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $atributo = $request->input('atributo') ?? null;
        $this->removeSessao($this->nomeFiltro, $atributo);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'os-categorias');
        session()->put("$this->nomeFiltro.rotaRedirect", 'os-categorias.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome_like', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'is_active', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Todos os status', 'selected' => false],
                ['value' => '1', 'label' => 'Ativo', 'selected' => false],
                ['value' => '0', 'label' => 'Inativo', 'selected' => false]
            ]]
        ];
    }
}
