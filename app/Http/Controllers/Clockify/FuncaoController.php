<?php

namespace App\Http\Controllers\Clockify;

use App\Actions\Clockify\Funcao\DeleteFuncaoAction;
use App\Actions\Clockify\Funcao\ListarFuncoesAction;
use App\Actions\Clockify\Funcao\SaveFuncaoAction;
use App\Actions\Clockify\Funcao\UpdateFuncaoAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FuncaoController extends Controller
{
    public function index(Request $request, ListarFuncoesAction $action): View
    {
        $funcoes = $action->handle($request->search);

        return view('clockify.funcoes', [
            'titulo' => 'Funções',
            'funcoes' => $funcoes,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Painel', 'url' => route('home')],
                ['name' => 'Funções', 'active' => true],
            ],
        ]);
    }

    public function store(Request $request, SaveFuncaoAction $action): RedirectResponse
    {
        $empresaId = $request->user()->empresa_id;

        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('funcoes', 'nome')->where('empresa_id', $empresaId),
            ],
            'custo_hora' => 'nullable|numeric|min:0|max:999999',
        ]);

        $action->handle($validated);

        return redirect()->route('clockify.funcoes.index')
            ->with('success', 'Função criada com sucesso!');
    }

    public function update(Request $request, $id, UpdateFuncaoAction $action): RedirectResponse
    {
        $empresaId = $request->user()->empresa_id;

        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('funcoes', 'nome')->where('empresa_id', $empresaId)->ignore($id),
            ],
            'custo_hora' => 'nullable|numeric|min:0|max:999999',
            'ativo' => 'nullable|boolean',
        ]);

        $action->handle((int) $id, array_merge($validated, [
            'ativo' => $request->has('ativo') ? (bool) $request->ativo : true,
        ]));

        return redirect()->route('clockify.funcoes.index')
            ->with('success', 'Função atualizada com sucesso!');
    }

    public function destroy($id, DeleteFuncaoAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.funcoes.index')
            ->with('success', 'Função removida com sucesso!');
    }
}
