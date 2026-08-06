<?php

namespace App\Http\Controllers;

use App\Models\ItemManutencaoPreventiva;
use App\Support\EmpresaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ItemManutencaoPreventivaController extends Controller
{
    public function index()
    {
        $q = trim((string) request()->input('q', ''));

        $query = ItemManutencaoPreventiva::query();
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nome', 'like', '%'.$q.'%')
                    ->orWhere('descricao', 'like', '%'.$q.'%');
            });
        }

        $items = $query
            ->orderBy('nome')
            ->paginate(session('itens_manutencao_preventiva.perPage', 15))
            ->withQueryString();

        return view('manutencao.itens.index', compact('items', 'q'))
            ->with('titulo', 'Itens de Manutencao Preventiva')
            ->with('breadcrumb', [
                ['name' => 'Planos de manutencao', 'url' => route('manutencao.planos.index')],
                ['name' => 'Itens de manutencao'],
            ]);
    }

    public function create()
    {
        return view('manutencao.itens.create')
            ->with('titulo', 'Novo Item de Manutencao Preventiva')
            ->with('breadcrumb', []);
    }

    public function store(Request $request)
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;

        $uniqueNome = Rule::unique('itens_manutencao_preventiva', 'nome');
        if ($empresaId !== null) {
            $uniqueNome = $uniqueNome->where(fn ($query) => $query->where('empresa_id', $empresaId));
        }

        $dados = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                $uniqueNome,
            ],
            'descricao' => 'nullable|string',
        ], [
            'nome.unique' => 'Já existe um item de manutenção com esse nome nesta empresa.',
        ]);

        $item = ItemManutencaoPreventiva::create([
            'empresa_id' => $empresaId,
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Item criado com sucesso.',
                'item' => $item->only(['id', 'nome', 'descricao']),
            ]);
        }

        return redirect()
            ->route('manutencao.itens.index')
            ->with('success', 'Item criado com sucesso.');
    }

    public function edit(ItemManutencaoPreventiva $item_manutencao)
    {
        return view('manutencao.itens.edit', compact('item_manutencao'))
            ->with('titulo', 'Editar Item de Manutencao Preventiva')
            ->with('breadcrumb', []);
    }

    public function update(Request $request, ItemManutencaoPreventiva $item_manutencao)
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;

        $uniqueNome = Rule::unique('itens_manutencao_preventiva', 'nome')->ignore($item_manutencao->id);
        if ($empresaId !== null) {
            $uniqueNome = $uniqueNome->where(fn ($query) => $query->where('empresa_id', $empresaId));
        }

        $dados = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                $uniqueNome,
            ],
            'descricao' => 'nullable|string',
        ], [
            'nome.unique' => 'Já existe um item de manutenção com esse nome nesta empresa.',
        ]);

        $item_manutencao->update([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
        ]);

        return redirect()
            ->route('manutencao.itens.index')
            ->with('success', 'Item atualizado com sucesso.');
    }

    public function destroy(ItemManutencaoPreventiva $item_manutencao)
    {
        $item_manutencao->delete();

        return redirect()
            ->route('manutencao.itens.index')
            ->with('success', 'Item excluido com sucesso.');
    }

    public function setPerPage(Request $request)
    {
        $request->session()->put('itens_manutencao_preventiva.perPage', (int) $request->input('perPage', 15));

        return redirect()->route('manutencao.itens.index');
    }
}
