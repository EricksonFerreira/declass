<?php

namespace App\Http\Controllers;

use App\Models\OsSubcategoria;
use App\Models\OsCategoria;
use App\Http\Requests\StoreOsSubcategoriaRequest;
use App\Http\Requests\UpdateOsSubcategoriaRequest;
use Illuminate\Http\Request;

use App\Traits\FiltroTrait;
use App\Traits\AtualizaFiltrosTrait;

class OsSubcategoriaController extends Controller
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    protected $nomeFiltro = 'os_subcategorias';

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $query = OsSubcategoria::query()->with('categoria');

        if (!empty($itensFiltered)) {
            foreach ($itensFiltered as $filter) {
                if ($filter['typeWhere'] === 'like') {
                    $query->where($filter['name'], 'like', "%{$filter['valor']}%");
                } elseif ($filter['typeWhere'] === 'where') {
                    $query->where($filter['name'], $filter['valor']);
                }
            }
        }

        $subcategorias = $query->paginate($perPage);

        $filters = $this->obterFiltrosPrincipais();
        $this->atualizarFiltros($itensFiltered, $filters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('os-subcategorias.index', compact('subcategorias', 'filters', 'itensFiltered', 'perPages', 'perPage'))
            ->with('titulo', 'Subcategorias de OS')
            ->with('breadcrumb', []);
    }

    public function create()
    {
        $categorias = OsCategoria::where('is_active', true)->get();
        return view('os-subcategorias.create', compact('categorias'));
    }

    public function store(StoreOsSubcategoriaRequest $request)
    {
        $data = $request->validated();
        $data['show_sinistro'] = $request->has('show_sinistro');
        $data['show_reserva'] = $request->has('show_reserva');
        $data['show_avarias'] = $request->has('show_avarias');
        $data['show_checklist_prevencao'] = $request->has('show_checklist_prevencao');
        $data['is_active'] = $request->has('is_active');

        $osSubcategoria = OsSubcategoria::create($data);

        if ($request->has('campos') && is_array($request->campos)) {
            foreach ($request->campos as $campo) {
                if (!empty($campo['nome']) && !empty($campo['tipo'])) {
                    $osSubcategoria->campos()->create([
                        'nome' => $campo['nome'],
                        'tipo' => $campo['tipo'],
                        'is_required' => !empty($campo['is_required']),
                        'ordem' => $campo['ordem'] ?? 0,
                        'opcoes' => !empty($campo['opcoes']) ? array_map('trim', explode(',', $campo['opcoes'])) : null,
                    ]);
                }
            }
        }

        return redirect()->route('os-subcategorias.index')
            ->with('success', 'Subcategoria de OS criada com sucesso!');
    }

    public function edit(OsSubcategoria $osSubcategoria)
    {
        $categorias = OsCategoria::where('is_active', true)->get();
        $osSubcategoria->load('campos');
        return view('os-subcategorias.edit', compact('osSubcategoria', 'categorias'));
    }

    public function update(UpdateOsSubcategoriaRequest $request, OsSubcategoria $osSubcategoria)
    {
        $data = $request->validated();
        $data['show_sinistro'] = $request->has('show_sinistro');
        $data['show_reserva'] = $request->has('show_reserva');
        $data['show_avarias'] = $request->has('show_avarias');
        $data['show_checklist_prevencao'] = $request->has('show_checklist_prevencao');
        $data['is_active'] = $request->has('is_active');

        $osSubcategoria->update($data);

        $osSubcategoria->campos()->delete();
        if ($request->has('campos') && is_array($request->campos)) {
            foreach ($request->campos as $campo) {
                if (!empty($campo['nome']) && !empty($campo['tipo'])) {
                    $osSubcategoria->campos()->create([
                        'nome' => $campo['nome'],
                        'tipo' => $campo['tipo'],
                        'is_required' => !empty($campo['is_required']),
                        'ordem' => $campo['ordem'] ?? 0,
                        'opcoes' => !empty($campo['opcoes']) ? array_map('trim', explode(',', $campo['opcoes'])) : null,
                    ]);
                }
            }
        }

        return redirect()->route('os-subcategorias.index')
            ->with('success', 'Subcategoria de OS atualizada com sucesso!');
    }

    public function destroy(OsSubcategoria $osSubcategoria)
    {
        $osSubcategoria->delete();

        return redirect()->route('os-subcategorias.index')
            ->with('success', 'Subcategoria de OS excluída com sucesso!');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'os-subcategorias');
        session()->put("$this->nomeFiltro.rotaRedirect", 'os-subcategorias.index');
    }

    public function obterFiltrosPrincipais()
    {
        $categorias = OsCategoria::pluck('nome', 'id')->toArray();
        $categoriasOptions = FiltroTrait::formatOptions($categorias, 'Selecione a categoria');

        return [
            ['name' => 'nome_like', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'os_categoria_id', 'label' => 'Categoria', 'type' => 'select', 'typeWhere' => 'where', 'options' => $categoriasOptions],
            ['name' => 'is_active', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Todos os status', 'selected' => false],
                ['value' => '1', 'label' => 'Ativo', 'selected' => false],
                ['value' => '0', 'label' => 'Inativo', 'selected' => false]
            ]]
        ];
    }
}
