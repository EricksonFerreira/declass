<?php

namespace App\Http\Controllers;

use App\Models\ChamadoCategoria;
use App\Models\ChamadoSubcategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChamadoSubcategoriaController extends Controller
{
    public function index()
    {
        $subcategorias = ChamadoSubcategoria::query()
            ->with('categoria')
            ->where(function ($q) {
                $q->where('empresa_id', Auth::user()?->empresa_id)
                    ->orWhereNull('empresa_id');
            })
            ->orderBy('nome')
            ->paginate(15);

        return view('config-chamados.subcategorias.index', compact('subcategorias'));
    }

    public function create()
    {
        $categorias = ChamadoCategoria::query()
            ->where(function ($q) {
                $q->where('empresa_id', Auth::user()?->empresa_id)
                    ->orWhereNull('empresa_id');
            })
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('config-chamados.subcategorias.form', [
            'subcategoria' => new ChamadoSubcategoria(),
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'chamado_categoria_id' => ['required', 'integer', 'exists:chamado_categorias,id'],
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'prazo_horas' => ['nullable', 'integer', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $data['empresa_id'] = Auth::user()?->empresa_id;
        $data['ativo'] = $request->boolean('ativo', true);

        ChamadoSubcategoria::create($data);

        return redirect()->route('config_chamados.subcategorias.index')->with('success', 'Subcategoria criada com sucesso.');
    }

    public function edit(ChamadoSubcategoria $subcategoria)
    {
        $categorias = ChamadoCategoria::query()
            ->where(function ($q) {
                $q->where('empresa_id', Auth::user()?->empresa_id)
                    ->orWhereNull('empresa_id');
            })
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('config-chamados.subcategorias.form', compact('subcategoria', 'categorias'));
    }

    public function update(Request $request, ChamadoSubcategoria $subcategoria)
    {
        $data = $request->validate([
            'chamado_categoria_id' => ['required', 'integer', 'exists:chamado_categorias,id'],
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'prazo_horas' => ['nullable', 'integer', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $subcategoria->update([
            'chamado_categoria_id' => $data['chamado_categoria_id'],
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'prazo_horas' => $data['prazo_horas'] ?? 0,
            'ativo' => $request->boolean('ativo', false),
        ]);

        return redirect()->route('config_chamados.subcategorias.index')->with('success', 'Subcategoria atualizada com sucesso.');
    }

    public function destroy(ChamadoSubcategoria $subcategoria)
    {
        $subcategoria->delete();

        return redirect()->route('config_chamados.subcategorias.index')->with('success', 'Subcategoria removida com sucesso.');
    }
}
