<?php

namespace App\Http\Controllers;

use App\Models\ItemManutencaoPreventiva;
use Illuminate\Http\Request;

class ItemManutencaoPreventivaController extends Controller
{
    public function index()
    {
        $items = ItemManutencaoPreventiva::query()
            ->orderBy('nome')
            ->paginate(session('itens_manutencao_preventiva.perPage', 15));

        return view('manutencao.itens.index', compact('items'))
            ->with('titulo', 'Itens de Manutenção Preventiva')
            ->with('breadcrumb', []);
    }

    public function create()
    {
        return view('manutencao.itens.create')
            ->with('titulo', 'Novo Item de Manutenção Preventiva')
            ->with('breadcrumb', []);
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        $item = ItemManutencaoPreventiva::create([
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
            ->with('titulo', 'Editar Item de Manutenção Preventiva')
            ->with('breadcrumb', []);
    }

    public function update(Request $request, ItemManutencaoPreventiva $item_manutencao)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
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
            ->with('success', 'Item excluído com sucesso.');
    }
}
