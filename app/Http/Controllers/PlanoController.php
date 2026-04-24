<?php

namespace App\Http\Controllers;

use App\Models\ItemPlano;
use App\Models\Plano;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanoController extends Controller
{
    public function index(): View
    {
        $planos = Plano::withCount('assinaturas')->orderBy('ordem')->get();

        return view('plano.index', compact('planos'));
    }

    public function create(): View
    {
        return view('plano.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome'            => 'required|string|max:80',
            'descricao'       => 'nullable|string',
            'valor_mensal'    => 'required|numeric|min:0',
            'valor_anual'     => 'required|numeric|min:0',
            'limite_veiculos' => 'nullable|integer|min:1',
            'site_vendas'     => 'boolean',
            'modulo_multas'   => 'boolean',
            'modulo_nfse'     => 'boolean',
            'multi_estacao'   => 'boolean',
            'api_integracao'  => 'boolean',
            'ativo'           => 'boolean',
            'destaque'        => 'boolean',
            'ordem'           => 'integer|min:0',
            'itens'           => 'nullable|array',
            'itens.*.descricao' => 'required_with:itens|string|max:200',
            'itens.*.incluso'   => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['nome']);
        $itens = $data['itens'] ?? [];
        unset($data['itens']);

        $this->normalizarBooleans($data, $request);

        $plano = Plano::create($data);
        $this->sincronizarItens($plano, $itens);

        return redirect()->route('plano.index')
            ->with('success', "Plano \"{$plano->nome}\" criado com sucesso.");
    }

    public function edit(Plano $plano): View
    {
        $plano->load('itens');

        return view('plano.edit', compact('plano'));
    }

    public function update(Request $request, Plano $plano): RedirectResponse
    {
        $data = $request->validate([
            'nome'            => 'required|string|max:80',
            'descricao'       => 'nullable|string',
            'valor_mensal'    => 'required|numeric|min:0',
            'valor_anual'     => 'required|numeric|min:0',
            'limite_veiculos' => 'nullable|integer|min:1',
            'site_vendas'     => 'boolean',
            'modulo_multas'   => 'boolean',
            'modulo_nfse'     => 'boolean',
            'multi_estacao'   => 'boolean',
            'api_integracao'  => 'boolean',
            'ativo'           => 'boolean',
            'destaque'        => 'boolean',
            'ordem'           => 'integer|min:0',
            'itens'           => 'nullable|array',
            'itens.*.id'        => 'nullable|integer',
            'itens.*.descricao' => 'required_with:itens|string|max:200',
            'itens.*.incluso'   => 'boolean',
        ]);

        $itens = $data['itens'] ?? [];
        unset($data['itens']);

        $this->normalizarBooleans($data, $request);

        $plano->update($data);
        $this->sincronizarItens($plano, $itens);

        return redirect()->route('plano.index')
            ->with('success', "Plano \"{$plano->nome}\" atualizado com sucesso.");
    }

    public function destroy(Plano $plano): RedirectResponse
    {
        if ($plano->assinaturas()->whereIn('status', ['ativo', 'trial'])->exists()) {
            return back()->with('error', 'Não é possível excluir um plano com assinaturas ativas.');
        }

        $plano->delete();

        return redirect()->route('plano.index')
            ->with('success', "Plano \"{$plano->nome}\" removido.");
    }

    private function sincronizarItens(Plano $plano, array $itens): void
    {
        $idsRecebidos = collect($itens)->pluck('id')->filter()->all();

        $plano->itens()->whereNotIn('id', $idsRecebidos)->delete();

        foreach ($itens as $index => $itemData) {
            $payload = [
                'plano_id'  => $plano->id,
                'descricao' => $itemData['descricao'],
                'incluso'   => (bool) ($itemData['incluso'] ?? true),
                'ordem'     => $index + 1,
            ];

            if (!empty($itemData['id'])) {
                ItemPlano::where('id', $itemData['id'])->where('plano_id', $plano->id)->update($payload);
            } else {
                ItemPlano::create($payload);
            }
        }
    }

    private function normalizarBooleans(array &$data, Request $request): void
    {
        foreach (['site_vendas', 'modulo_multas', 'modulo_nfse', 'multi_estacao', 'api_integracao', 'ativo', 'destaque'] as $campo) {
            $data[$campo] = $request->boolean($campo);
        }
    }
}
