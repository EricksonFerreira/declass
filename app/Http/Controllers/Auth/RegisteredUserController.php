<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $dataLimite = Carbon::now()->subYears(16)->format('Y-m-d');
        $empresaId = null;

        if (Schema::hasTable('empresas')) {
            $empresaId = Empresa::query()->value('id');

            if (!$empresaId) {
                $empresaId = Empresa::query()->create([
                    'nome' => 'Empresa Padrao',
                    'slug' => 'empresa-padrao',
                    'ativo' => 'S',
                ])->id;
            }
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'data_nascimento' => ['required', 'date', 'before_or_equal:' . $dataLimite],
            'celular' => ['required', 'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/'],
            'codigo_postal' => ['required', 'regex:/^\d{5}-\d{3}$/'],
            'cidade' => ['required', 'string', 'max:120'],
            'pais' => ['required', 'size:2'],
            'sexo' => ['required', 'max:2','in:M,F,NI'],
            'endereco1' => ['required', 'string', 'max:255'],
            'endereco2' => ['nullable', 'string', 'max:255'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome deve ter no máximo 255 caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail deve ter no máximo 255 caracteres.',
            'email.unique' => 'Este e-mail já está cadastrado.',

            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'data_nascimento.date' => 'Informe uma data de nascimento válida.',
            'data_nascimento.before_or_equal' => 'É necessário ter pelo menos 16 anos para se cadastrar.',

            'celular.required' => 'O celular é obrigatório.',
            'celular.regex' => 'Informe um celular válido no formato (00) 00000-0000.',

            'codigo_postal.required' => 'O CEP é obrigatório.',
            'codigo_postal.regex' => 'Informe um CEP válido no formato 00000-000.',

            'cidade.required' => 'A cidade é obrigatória.',
            'cidade.max' => 'A cidade deve ter no máximo 120 caracteres.',

            'pais.required' => 'O país é obrigatório.',
            'pais.size' => 'O país deve ter exatamente 2 caracteres.',

            'sexo.required' => 'O sexo é obrigatório.',
            'sexo.max' => 'O sexo deve ter no máximo 2 caracteres.',
            'sexo.in' => 'O sexo deve ser Masculino, Feminino ou  Não informado.',

            'endereco1.required' => 'O endereço é obrigatório.',
            'endereco1.max' => 'O endereço deve ter no máximo 255 caracteres.',

            'endereco2.max' => 'O complemento deve ter no máximo 255 caracteres.',

            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.letters' => 'A senha deve conter pelo menos uma letra.',
            'password.mixed' => 'A senha deve conter pelo menos uma letra maiúscula e uma minúscula.',
            'password.numbers' => 'A senha deve conter pelo menos um número.',
        ]);

        $userData = [
            'nome' => $request->name,
            'email' => $request->email,
            'data_nascimento' => $request->data_nascimento,
            'telefone' => $request->telefone,
            'sexo' => $request->sexo,
            'zip_code' => $request->codigo_postal,
            'cidade' => $request->cidade,
            'pais_sigla' => $request->pais,
            'endereco' => $request->filled('endereco2')
                ? $request->endereco1 . ', ' . $request->endereco2
                : $request->endereco1,
            'password' => $request->password,
        ];

        if (Schema::hasColumn((new User())->getTable(), 'empresa_id')) {
            $userData['empresa_id'] = $empresaId;
        }

        $user = User::create($userData);

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }


        return redirect()->route('register.success')
            ->with('status', 'Cadastro realizado com sucesso! Verifique seu e-mail para ativar a conta.');
    }
}
