<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Regra;
use App\Models\User;
use App\Services\BreadcrumbService;
use App\Services\UsuarioService;
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
        private readonly EspelhamentoContext $espelhamento,
        private readonly UsuarioService $usuarioService,
        private readonly BreadcrumbService $breadcrumbService
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $filtros = [
            'q' => trim((string) $request->query('q', '')),
            'email' => trim((string) $request->query('email', '')),
            'empresa_id' => (string) $request->query('empresa_id', ''),
            'tipo_conta' => (string) $request->query('tipo_conta', ''),
            'role_id' => (string) $request->query('role_id', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $scopeTipo = $this->usuarioService->scopeTipoConta($filtros['tipo_conta'] ?: null);

        $usuarios = User::query()
            ->with(['regra', 'empresa'])
            ->when($filtros['q'] !== '', function ($query) use ($filtros) {
                $busca = $filtros['q'];
                $query->where(function ($w) use ($busca) {
                    $w->where('name', 'like', "%{$busca}%")
                        ->orWhere('nome', 'like', "%{$busca}%")
                        ->orWhere('usuario', 'like', "%{$busca}%");
                });
            })
            ->when($filtros['email'] !== '', fn ($q) => $q->where('email', 'like', '%'.$filtros['email'].'%'))
            ->when($filtros['empresa_id'] !== '', fn ($q) => $q->where('empresa_id', $filtros['empresa_id']))
            ->when($filtros['role_id'] !== '', fn ($q) => $q->where('role_id', $filtros['role_id']))
            ->when($filtros['status'] === 'ativo', fn ($q) => $q->where('ativo', true))
            ->when($filtros['status'] === 'inativo', fn ($q) => $q->where('ativo', false))
            ->when($scopeTipo, fn ($q) => $q->where($scopeTipo))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $empresas = Empresa::query()->orderBy('nome')->pluck('nome', 'id');
        $regras = Regra::query()->orderBy('name')->pluck('name', 'id');
        $tiposConta = User::TIPOS_CONTA;

        $chips = [];
        if ($filtros['q'] !== '') {
            $chips[] = ['param' => 'q', 'label' => 'Busca', 'valor' => $filtros['q']];
        }
        if ($filtros['email'] !== '') {
            $chips[] = ['param' => 'email', 'label' => 'E-mail', 'valor' => $filtros['email']];
        }
        if ($filtros['empresa_id'] !== '' && isset($empresas[$filtros['empresa_id']])) {
            $chips[] = ['param' => 'empresa_id', 'label' => 'Empresa', 'valor' => $empresas[$filtros['empresa_id']]];
        }
        if ($filtros['tipo_conta'] !== '' && isset($tiposConta[$filtros['tipo_conta']])) {
            $chips[] = ['param' => 'tipo_conta', 'label' => 'Tipo', 'valor' => $tiposConta[$filtros['tipo_conta']]];
        }
        if ($filtros['role_id'] !== '' && isset($regras[$filtros['role_id']])) {
            $chips[] = ['param' => 'role_id', 'label' => 'Perfil', 'valor' => $regras[$filtros['role_id']]];
        }
        if ($filtros['status'] !== '') {
            $chips[] = ['param' => 'status', 'label' => 'Status', 'valor' => $filtros['status'] === 'ativo' ? 'Ativo' : 'Inativo'];
        }

        return view('espelhamento.index', [
            'titulo' => 'Espelhar acesso',
            'usuarios' => $usuarios,
            'filtros' => $filtros,
            'empresas' => $empresas,
            'regras' => $regras,
            'tiposConta' => $tiposConta,
            'chips' => $chips,
            'alvo' => $this->espelhamento->alvo(),
            'breadcrumb' => $this->breadcrumbService->prepararBreadcrumbCustom([
                ['Home', route('home')],
                ['Configurações', null],
                ['Espelhar acesso', null],
            ]),
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
