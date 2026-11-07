<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ((bool) ($request->user()?->forcar_troca_senha ?? false)) {
            return redirect()->route('profile.force-password.edit');
        }

        return redirect()->intended($this->redirectPosLogin($request->user()));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectPosLogin(?User $user): string
    {
        // Conta de portal (aluno/responsavel) vai direto ao Portal
        // Familiar quando ha alunos vinculados a ela.
        if ($user?->isPortalAccount() && $user->temAlunosVinculados()) {
            return route('portal.index');
        }

        return route('home');
    }
}
