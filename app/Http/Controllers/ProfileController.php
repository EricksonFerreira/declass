<?php

namespace App\Http\Controllers;

use App\Services\BreadcrumbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct(private BreadcrumbService $breadcrumbService)
    {
    }

    public function edit(Request $request)
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Meu Perfil');

        return view('perfil.edit', [
            'user' => $request->user(),
            'titulo' => 'Meu Perfil',
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function editPassword(Request $request)
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Meu Perfil', 'profile.edit', 'Alterar Senha');

        return view('perfil.alterar-senha', [
            'titulo' => 'Alterar Senha',
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Senha alterada com sucesso.');
    }

    public function forcePasswordEdit(Request $request)
    {
        if (!(bool) ($request->user()?->forcar_troca_senha ?? false)) {
            return redirect()->route('home');
        }

        return view('perfil.forcar-nova-senha', [
            'titulo' => 'Definir Nova Senha',
        ]);
    }

    public function forcePasswordUpdate(Request $request): RedirectResponse
    {
        if (!(bool) ($request->user()?->forcar_troca_senha ?? false)) {
            return redirect()->route('home');
        }

        $data = $request->validate(
            [
                'password' => [
                    'required',
                    'string',
                    Password::min(8)->mixedCase()->numbers()->symbols(),
                    'confirmed',
                ],
            ],
            [
                'password.required' => 'A nova senha e obrigatoria.',
                'password.confirmed' => 'A confirmacao da nova senha nao corresponde.',
                'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
                'password.mixed' => 'A senha deve conter letra maiuscula e minuscula.',
                'password.numbers' => 'A senha deve conter pelo menos um numero.',
                'password.symbols' => 'A senha deve conter pelo menos um caractere especial.',
            ]
        );

        $user = $request->user();
        $user->password = Hash::make($data['password']);
        $user->forcar_troca_senha = false;
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Senha atualizada com sucesso.');
    }
}
