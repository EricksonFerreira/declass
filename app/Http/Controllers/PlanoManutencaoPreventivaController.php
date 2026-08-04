<?php

namespace App\Http\Controllers;

use App\Models\GrupoFrota;
use App\Models\ItemManutencaoPreventiva;
use App\Models\PlanoManutencaoPreventiva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;

class PlanoManutencaoPreventivaController extends Controller
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    protected $nomeFiltro = 'manutencao_planos';

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $query = PlanoManutencaoPreventiva::query()->with(['grupoFrota', 'veiculo', 'itens.item', 'usuarioInclusao', 'usuarioAtualizacao']);
        if (!empty($itensFiltered)) {
            foreach ($itensFiltered as $filter) {
                if ($filter['typeWhere'] === 'like') {
                    $query->where($filter['name'], 'like', "%{$filter['valor']}%");
                } elseif ($filter['typeWhere'] === 'where') {
                    $query->where($filter['name'], $filter['valor']);
                }
            }
        }

        $planos = $query->paginate($perPage);

        $filters = $this->obterFiltrosPrincipais();
        $this->atualizarFiltros($itensFiltered, $filters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('manutencao.planos.index', compact('planos', 'filters', 'itensFiltered', 'perPages', 'perPage'))
            ->with('titulo', 'Planos de Manutenção Preventiva')
            ->with('breadcrumb', []);
    }

    public function create()
    {
        $grupos = GrupoFrota::all();

        return view('manutencao.planos.create', compact('grupos'))
            ->with('titulo', 'Novo Plano de Manutenção')
            ->with('breadcrumb', []);
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'veiculo_id' => 'nullable|integer|exists:veiculos,id',
            'grupo_frota_id' => 'nullable|exists:grupos_frota,id',
            'itens' => 'nullable|array',
            'itens.*.id' => 'required|exists:itens_manutencao_preventiva,id',
            'itens.*.meses' => 'nullable|integer',
            'itens.*.km' => 'nullable|integer',
        ]);

        $plano = PlanoManutencaoPreventiva::create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'],
            'grupo_frota_id' => $dados['grupo_frota_id'] ?? null,
            'veiculo_id' => null,
        ]);

        if (!empty($dados['itens'])) {
            foreach ($dados['itens'] as $item) {
                if ($item['meses'] || $item['km']) {
                    $plano->itens()->create([
                        'item_id' => $item['id'],
                        'frequencia_meses' => $item['meses'],
                        'frequencia_km' => $item['km'],
                    ]);
                }
            }
        }

        return redirect()->route('manutencao.planos.index')->with('success', 'Plano criado com sucesso.');
    }

    public function edit(PlanoManutencaoPreventiva $plano)
    {
        $grupos = GrupoFrota::all();
        $plano->load('itens.item', 'veiculo');

        return view('manutencao.planos.edit', compact('plano', 'grupos'))
            ->with('titulo', 'Editar Plano de Manutenção')
            ->with('breadcrumb', []);
    }

    public function buscarItens(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));

        $query = ItemManutencaoPreventiva::query()->orderBy('nome');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nome', 'like', '%'.$q.'%')
                    ->orWhere('descricao', 'like', '%'.$q.'%');
            });
        }

        $items = $query->limit(30)->get()->map(fn ($item) => [
            'id' => $item->id,
            'nome' => $item->nome,
            'descricao' => $item->descricao,
        ])->values();

        return response()->json(['items' => $items]);
    }

    public function update(Request $request, PlanoManutencaoPreventiva $plano)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'veiculo_id' => 'nullable|integer|exists:veiculos,id',
            'grupo_frota_id' => 'nullable|exists:grupos_frota,id',
            'itens' => 'nullable|array',
            'itens.*.id' => 'required|exists:itens_manutencao_preventiva,id',
            'itens.*.meses' => 'nullable|integer',
            'itens.*.km' => 'nullable|integer',
        ]);

        $plano->update([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'],
            'grupo_frota_id' => $dados['grupo_frota_id'] ?? null,
            'veiculo_id' => null,
        ]);

        $plano->itens()->delete();

        if (!empty($dados['itens'])) {
            foreach ($dados['itens'] as $item) {
                if ($item['meses'] || $item['km']) {
                    $plano->itens()->create([
                        'item_id' => $item['id'],
                        'frequencia_meses' => $item['meses'],
                        'frequencia_km' => $item['km'],
                    ]);
                }
            }
        }

        return redirect()->route('manutencao.planos.index')->with('success', 'Plano atualizado com sucesso.');
    }

    public function destroy(PlanoManutencaoPreventiva $plano)
    {
        $plano->delete();

        return redirect()->route('manutencao.planos.index')->with('success', 'Plano excluído com sucesso.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'planos-manutencao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'manutencao.planos.index');
    }

    public function obterFiltrosPrincipais()
    {
        $grupos = GrupoFrota::pluck('nome', 'id')->toArray();
        $gruposOptions = FiltroTrait::formatOptions($grupos, 'Selecione o grupo');

        return [
            ['name' => 'nome_like', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'grupo_frota_id', 'label' => 'Grupo de Frota', 'type' => 'select', 'typeWhere' => 'where', 'options' => $gruposOptions],
        ];
    }
}
