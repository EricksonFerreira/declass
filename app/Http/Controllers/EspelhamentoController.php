<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\EspelhamentoContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Espelhamento de acesso: o superadmin assume as permissoes de outro
 * usuario sem trocar de identidade — auth()->id() continua sendo o
 * dele, entao alteracoes seguem auditadas em seu nome.
 */
class EspelhamentoController extends Controller
{
    public function __construct(
        private readonly EspelhamentoContext $espelhamento
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $busca = trim((string) $request->query('q', ''));

        $usuarios = User::query()
            ->with(['regra', 'empresa'])
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($w) use ($busca) {
                    $w->where('name', 'like', "%{$busca}%")
                        ->orWhere('nome', 'like', "%{$busca}%")
                        ->orWhere('email', 'like', "%{$busca}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('espelhamento.index', [
            'titulo' => 'Espelhar acesso',
            'usuarios' => $usuarios,
            'busca' => $busca,
            'alvo' => $this->espelhamento->alvo(),
            'breadcrumb' => [
                ['label' => 'Configurações'],
                ['label' => 'Espelhar acesso'],
            ],
        ]);
    }

    public function store(Request $request, User $usuario): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        if ($usuario->isSuperAdmin()) {
            return back()->with('error', 'Não é possível espelhar outro superadmin.');
        }

        if ($usuario->id === $request->user()->id) {
            return back()->with('error', 'Você já está usando o seu próprio acesso.');
        }

        if (! $usuario->empresa_id) {
            return back()->with('error', 'Este usuário não está vinculado a uma empresa.');
        }

        $this->espelhamento->iniciar($usuario);

        Log::info('Espelhamento iniciado', [
            'superadmin_id' => $request->user()->id,
            'alvo_id' => $usuario->id,
            'alvo_email' => $usuario->email,
        ]);

        return redirect()
            ->route('home')
            ->with('success', "Você está vendo o sistema com os acessos de {$usuario->name}. Suas ações continuam registradas no seu usuário.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $alvo = $this->espelhamento->alvo();
        $this->espelhamento->parar();

        Log::info('Espelhamento encerrado', [
            'superadmin_id' => $request->user()->id,
            'alvo_id' => $alvo?->id,
        ]);

        return redirect()
            ->route('espelhamento.index')
            ->with('success', 'Espelhamento encerrado. Você voltou ao seu acesso de superadmin.');
    }
}
