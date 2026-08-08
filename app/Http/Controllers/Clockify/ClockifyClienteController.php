<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Actions\Clockify\Cliente\ListarClientesAction;
use App\Actions\Clockify\Cliente\SaveClienteAction;
use App\Actions\Clockify\Cliente\UpdateClienteAction;
use App\Actions\Clockify\Cliente\DeleteClienteAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClockifyClienteController extends Controller
{
    public function index(Request $request, ListarClientesAction $action): View
    {
        $clientes = $action->handle($request->search);

        return view('clockify.clientes', [
            'titulo' => 'Clientes',
            'clientes' => $clientes,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Clientes', 'active' => true],
            ],
        ]);
    }

    public function store(Request $request, SaveClienteAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:50',
            'endereco' => 'nullable|string',
        ]);

        $action->handle($validated);

        return redirect()->route('clockify.clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function update(Request $request, $id, UpdateClienteAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:50',
            'endereco' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        $action->handle((int) $id, array_merge($validated, [
            'ativo' => $request->has('ativo') ? (bool) $request->ativo : true,
        ]));

        return redirect()->route('clockify.clientes.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($id, DeleteClienteAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.clientes.index')
            ->with('success', 'Cliente removido com sucesso!');
    }
}
