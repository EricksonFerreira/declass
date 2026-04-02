<?php

namespace App\Http\Controllers;

use App\Services\BreadcrumbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
}
