<?php

namespace App\Http\Controllers;

use App\Models\ChamadoCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChamadoCategoriaController extends Controller
{
    public function index()
    {
        $categorias = ChamadoCategoria::query()
            ->withCount('subcategorias')
            ->where(function ($q) {
                $q->where('empresa_id', Auth::user()?->empresa_id)
                    ->orWhereNull('empresa_id');
            })
            ->orderBy('nome')
            ->paginate(15);

        return view('config-chamados.categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('config-chamados.categorias.form', [
            'categoria' => new ChamadoCategoria(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $data['empresa_id'] = Auth::user()?->empresa_id;
        $data['ativo'] = $request->boolean('ativo', true);

        ChamadoCategoria::create($data);

        return redirect()->route('config_chamados.categorias.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(ChamadoCategoria $categoria)
    {
        return view('config-chamados.categorias.form', compact('categoria'));
    }

    public function update(Request $request, ChamadoCategoria $categoria)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $categoria->update([
            'nome' => $data['nome'],
            'ativo' => $request->boolean('ativo', false),
        ]);

        return redirect()->route('config_chamados.categorias.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(ChamadoCategoria $categoria)
    {
        $categoria->delete();

        return redirect()->route('config_chamados.categorias.index')->with('success', 'Categoria removida com sucesso.');
    }
}
