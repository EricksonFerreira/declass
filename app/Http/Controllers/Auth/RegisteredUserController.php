<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use App\Support\EmpresaDominio;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View|RedirectResponse
    {
        // Cadastro publico de usuario so existe em dominio de empresa
        // (subdominio/dominio proprio do tenant). No dominio central o
        // correto e o cadastro de tenant em /comecar.
        $empresa = $this->resolveEmpresaAtivaByHost(request());
        if (! $empresa) {
            return redirect()->route('cadastro.empresa.create');
        }

        return view('auth.register', ['empresa' => $empresa]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(EmpresaDominio::porHost($request->getHost()) === null && ! $this->resolveEmpresaAtivaByHost($request), 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'celular' => ['nullable', 'string', 'max:20'],
            'password' => [
                'required', 'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers(),
            ],
        ]);

        $empresa = $this->resolveEmpresaAtivaByHost($request);
        abort_if(! $empresa, 404);

        $user = DB::transaction(function () use ($request, $empresa) {
            // Perfil baseline 'membro' do tenant.
            app(\App\Services\PermissaoSyncService::class)->garantirPapeisParaEmpresa((int) $empresa->id);
            $roleId = \App\Models\Regra::where('name', 'membro')
                ->where('guard_name', 'web')
                ->where('empresa_id', $empresa->id)
                ->value('id');

            return User::create([
                'nome' => $request->name,
                'name' => $request->name,
                'email' => $request->email,
                'celular' => $request->celular,
                'password' => $request->password,
                'empresa_id' => $empresa->id,
                'role_id' => $roleId,
            ]);
        });

        event(new Registered($user));

        Auth::login($user);

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('home')
            ->with('status', 'Cadastro realizado com sucesso!');
    }

    /**
     * Empresa resolvida pelo host da requisicao — somente dominio de
     * tenant (subdominio/dominio proprio). Sem fallback para a primeira
     * empresa: no dominio central nao existe auto-vinculo.
     */
    private function resolveEmpresaAtivaByHost(Request $request): ?Empresa
    {
        return EmpresaDominio::porHost($request->getHost());
    }
}
