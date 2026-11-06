<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmpresaContextController extends Controller
{
    public function edit(Request $request)
    {
        $empresas = ['0' => 'Todas as empresas'] + Empresa::query()
            ->orderBy('nome')
            ->pluck('nome', 'id')
            ->toArray();

        $empresaAtual = (int) $request->session()->get('empresa_context_id', 0);

        return view('empresa.contexto', compact('empresas', 'empresaAtual'))
            ->with('titulo', 'Trocar Empresa');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'empresa_id' => ['required', 'integer'],
        ]);

        $empresaId = (int) $data['empresa_id'];
        if ($empresaId !== 0 && ! Empresa::query()->whereKey($empresaId)->exists()) {
            return redirect()->back()->with('error', 'Empresa selecionada é inválida.');
        }

        $request->session()->put('empresa_context_id', $empresaId);

        if ($empresaId === 0) {
            return redirect()->route('home')->with('success', 'Exibindo dados de todas as empresas.');
        }

        return redirect()->route('home')->with('success', 'Empresa ativa alterada com sucesso.');
    }
}
