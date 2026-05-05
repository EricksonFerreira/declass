<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\OnboardingStep;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $planos = \App\Models\Plano::ativos()->with('itens')->get();

        return view('auth.register', compact('planos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dataLimite = Carbon::now()->subYears(16)->format('Y-m-d');

        $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'data_nascimento' => ['required', 'date', 'before_or_equal:' . $dataLimite],
            'celular'         => ['required', 'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/'],
            'codigo_postal'   => ['nullable', 'regex:/^\d{5}-\d{3}$/'],
            'cidade'          => ['nullable', 'string', 'max:120'],
            'pais'            => ['nullable', 'size:2'],
            'sexo'            => ['required', 'max:2', 'in:M,F,NI'],
            'endereco1'       => ['nullable', 'string', 'max:255'],
            'endereco2'       => ['nullable', 'string', 'max:255'],
            'password'        => [
                'required', 'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers(),
            ],
        ], [
            'name.required'            => 'O nome é obrigatório.',
            'email.required'           => 'O e-mail é obrigatório.',
            'email.email'              => 'Informe um e-mail válido.',
            'email.unique'             => 'Este e-mail já está cadastrado.',
            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'data_nascimento.before_or_equal' => 'É necessário ter pelo menos 16 anos para se cadastrar.',
            'celular.required'         => 'O celular é obrigatório.',
            'celular.regex'            => 'Informe um celular válido no formato (00) 00000-0000.',
            'sexo.required'            => 'O sexo é obrigatório.',
            'password.required'        => 'A senha é obrigatória.',
            'password.confirmed'       => 'A confirmação da senha não confere.',
            'password.min'             => 'A senha deve ter pelo menos 8 caracteres.',
            'password.letters'         => 'A senha deve conter pelo menos uma letra.',
            'password.mixed'           => 'A senha deve conter letras maiúsculas e minúsculas.',
            'password.numbers'         => 'A senha deve conter pelo menos um número.',
        ]);

        $user = DB::transaction(function () use ($request) {
            // Cria uma empresa exclusiva para este novo usuário.
            $empresa = null;
            if (SchemaCache::hasTable('empresas')) {
                $slugBase = Str::slug($request->name);
                $slug     = $slugBase . '-' . Str::random(5);

                $empresa = Empresa::create([
                    'nome'  => $request->name . "'s Locadora",
                    'slug'  => $slug,
                    'ativo' => 'S',
                    'email' => $request->email,
                ]);

                OnboardingStep::create(['empresa_id' => $empresa->id]);
            }

            $userData = [
                'nome'            => $request->name,
                'email'           => $request->email,
                'data_nascimento' => $request->data_nascimento,
                'celular'         => $request->celular,
                'sexo'            => $request->sexo,
                'password'        => $request->password,
            ];

            if ($request->filled('codigo_postal') && SchemaCache::hasColumn('users', 'zip_code')) {
                $userData['zip_code'] = $request->codigo_postal;
            }
            if ($request->filled('cidade')) {
                $userData['cidade'] = $request->cidade;
            }
            if ($request->filled('pais')) {
                $userData['pais_sigla'] = $request->pais;
            }
            if ($request->filled('endereco1')) {
                $userData['endereco'] = $request->filled('endereco2')
                    ? $request->endereco1 . ', ' . $request->endereco2
                    : $request->endereco1;
            }

            if ($empresa && SchemaCache::hasColumn('users', 'empresa_id')) {
                $userData['empresa_id'] = $empresa->id;
            }

            return User::create($userData);
        });

        event(new Registered($user));

        Auth::login($user);

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        // Redireciona direto ao onboarding para configurar empresa e escolher plano.
        return redirect()->route('onboarding.empresa')
            ->with('status', 'Cadastro realizado! Configure sua locadora para começar.');
    }
}
