<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use App\Support\EmpresaDominio;
use App\Support\SchemaCache;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View|RedirectResponse
    {
        // Cadastro so existe no dominio central; em dominio de empresa,
        // o acesso e via convite/usuarios criados pelo admin.
        if (EmpresaDominio::porHost(request()->getHost())) {
            return redirect()->route('login');
        }

        $planos = \App\Models\Plano::ativos()->with('itens')->get();
        $empresa = $this->resolveEmpresaAtivaByHost(request());

        return view('auth.register', compact('planos', 'empresa'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(EmpresaDominio::porHost($request->getHost()), 404);

        $dataLimite = Carbon::now()->subYears(16)->format('Y-m-d');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'data_nascimento' => ['required', 'date', 'before_or_equal:'.$dataLimite],
            'celular' => ['required', 'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/'],
            'codigo_postal' => ['nullable', 'regex:/^\d{5}-\d{3}$/'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'pais' => ['nullable', 'size:2'],
            'sexo' => ['required', 'max:2', 'in:M,F,NI'],
            'endereco1' => ['nullable', 'string', 'max:255'],
            'endereco2' => ['nullable', 'string', 'max:255'],
            'password' => [
                'required', 'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers(),
            ],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'data_nascimento.before_or_equal' => 'É necessário ter pelo menos 16 anos para se cadastrar.',
            'celular.required' => 'O celular é obrigatório.',
            'celular.regex' => 'Informe um celular válido no formato (00) 00000-0000.',
            'sexo.required' => 'O sexo é obrigatório.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.letters' => 'A senha deve conter pelo menos uma letra.',
            'password.mixed' => 'A senha deve conter letras maiúsculas e minúsculas.',
            'password.numbers' => 'A senha deve conter pelo menos um número.',
        ]);

        $user = DB::transaction(function () use ($request) {
            $empresa = $this->resolveEmpresaAtivaByHost($request);

            $userData = [
                'nome' => $request->name,
                'email' => $request->email,
                'data_nascimento' => $request->data_nascimento,
                'celular' => $request->celular,
                'sexo' => $request->sexo,
                'password' => $request->password,
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
                    ? $request->endereco1.', '.$request->endereco2
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

        return redirect()->route('home')
            ->with('status', 'Cadastro realizado com sucesso!');
    }

    private function resolveEmpresaAtivaByHost(Request $request): ?Empresa
    {
        $empresa = EmpresaDominio::porHost($request->getHost());
        if ($empresa) {
            return $empresa;
        }

        if (! SchemaCache::hasTable('empresas')) {
            return null;
        }

        return Empresa::query()
            ->withoutGlobalScopes()
            ->where('ativo', 'S')
            ->orderBy('id')
            ->first();
    }
}
