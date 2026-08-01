<?php

namespace App\Http\Controllers;

use App\Models\ChamadoStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChamadoStatusController extends Controller
{
    public function index()
    {
        $statuses = ChamadoStatus::query()
            ->where(function ($q) {
                $q->where('empresa_id', Auth::user()?->empresa_id)
                    ->orWhereNull('empresa_id');
            })
            ->orderBy('nome')
            ->paginate(15);

        return view('config-chamados.status.index', compact('statuses'));
    }

    public function create()
    {
        return view('config-chamados.status.form', [
            'status' => new ChamadoStatus(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'cor' => ['required', 'string', 'max:50'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $data['empresa_id'] = Auth::user()?->empresa_id;
        $data['ativo'] = $request->boolean('ativo', true);

        ChamadoStatus::create($data);

        return redirect()->route('config_chamados.status.index')->with('success', 'Status criado com sucesso.');
    }

    public function edit(ChamadoStatus $status)
    {
        return view('config-chamados.status.form', compact('status'));
    }

    public function update(Request $request, ChamadoStatus $status)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'cor' => ['required', 'string', 'max:50'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $status->update([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'cor' => $data['cor'],
            'ativo' => $request->boolean('ativo', false),
        ]);

        return redirect()->route('config_chamados.status.index')->with('success', 'Status atualizado com sucesso.');
    }

    public function destroy(ChamadoStatus $status)
    {
        $status->delete();

        return redirect()->route('config_chamados.status.index')->with('success', 'Status removido com sucesso.');
    }
}
