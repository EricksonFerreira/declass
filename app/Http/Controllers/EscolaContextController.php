<?php

namespace App\Http\Controllers;

use App\Models\Escola;
use App\Support\EmpresaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EscolaContextController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'escola_id' => ['required', 'integer'],
        ]);

        $escolaId = (int) $data['escola_id'];

        if ($escolaId !== 0) {
            $empresaId = app(EmpresaContext::class)->empresaId();
            $user = $request->user();

            $valida = $empresaId !== null && Escola::query()
                ->whereKey($escolaId)
                ->where('empresa_id', $empresaId)
                ->exists();

            $permitidas = $user?->escolasContextoIds();
            if ($permitidas !== null && ! in_array($escolaId, $permitidas, true)) {
                $valida = false;
            }

            if (! $valida) {
                return redirect()->back()->with('error', 'Escola selecionada é inválida.');
            }
        }

        $request->session()->put('escola_contexto_id', $escolaId);

        return redirect()->back()->with(
            'success',
            $escolaId === 0 ? 'Exibindo dados de todas as escolas.' : 'Escola ativa alterada com sucesso.'
        );
    }
}
