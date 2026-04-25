<?php

namespace App\Http\Controllers;

use App\Mail\BoasVindasAdminMail;
use App\Mail\BoasVindasEmpresaMail;
use App\Models\Empresa;
use App\Models\OnboardingStep;
use App\Models\User;
use App\Services\ReplicacaoBaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CadastroEmpresaController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.cadastro-empresa', [
            'planoPreferido' => (string) $request->query('plano', ''),
            'semSite'        => (bool) $request->boolean('sem_site', false),
        ]);
    }

    public function store(Request $request, ReplicacaoBaseService $replicacaoBaseService): RedirectResponse
    {
        $data = $request->validate([
            'empresa_nome'     => 'required|string|max:200',
            'empresa_cnpj'     => 'nullable|string|max:20',
            'empresa_email'    => 'nullable|email|max:200',
            'empresa_telefone' => 'nullable|string|max:30',
            'admin_nome'       => 'required|string|max:200',
            'admin_email'      => ['required', 'email', 'max:200', Rule::unique('users', 'email')],
            'plano'            => 'nullable|string|max:50',
            'sem_site'         => 'nullable|boolean',
        ], [
            'empresa_nome.required' => 'Informe o nome da empresa.',
            'admin_nome.required'   => 'Informe o nome do responsavel.',
            'admin_email.required'  => 'Informe o e-mail do responsavel.',
            'admin_email.unique'    => 'Este e-mail ja esta em uso.',
        ]);

        $senhaPadrao = 'Senh@123';

        $payload = DB::transaction(function () use ($data, $replicacaoBaseService, $senhaPadrao) {
            $slugBase = Str::slug((string) $data['empresa_nome']);
            $slug = $slugBase !== '' ? $slugBase : 'empresa';
            $slug .= '-' . Str::random(6);

            $empresa = Empresa::create([
                'nome'     => $data['empresa_nome'],
                'slug'     => $slug,
                'ativo'    => 'S',
                'cnpj'     => $data['empresa_cnpj'] ?? null,
                'email'    => $data['empresa_email'] ?? $data['admin_email'],
                'telefone' => $data['empresa_telefone'] ?? null,
            ]);

            // Replica regras/permissoes da empresa base (1), quando existir estrutura multi-tenant.
            try {
                $replicacaoBaseService->replicar(1, [$empresa->id], ['regras', 'permissoes', 'regras_permissoes']);
            } catch (\Throwable $e) {
                // Nao bloqueia o cadastro se a replicacao nao estiver disponivel.
            }

            $roleId = null;
            if (Schema::hasTable('roles') && Schema::hasColumn('users', 'role_id')) {
                $roles = DB::table('roles')->where('guard_name', 'web');
                if (Schema::hasColumn('roles', 'empresa_id')) {
                    $roles->where('empresa_id', $empresa->id);
                }
                $roleId = $roles
                    ->orderByRaw("CASE
                        WHEN LOWER(name) IN ('administrador','admin') THEN 0
                        WHEN LOWER(name) LIKE '%admin%' THEN 1
                        WHEN LOWER(name) LIKE '%gerente%' THEN 2
                        ELSE 99 END")
                    ->value('id');
            }

            $user = User::create([
                'nome'              => $data['admin_nome'],
                'email'             => $data['admin_email'],
                'password'          => $senhaPadrao,
                'empresa_id'        => $empresa->id,
                'role_id'           => $roleId,
                'forcar_troca_senha'=> true,
            ]);

            OnboardingStep::updateOrCreate(
                ['empresa_id' => $empresa->id],
                [
                    'step_empresa_configurada' => true,
                    'step_usuario_criado'      => true,
                    'concluido'                => false,
                ]
            );

            return [
                'empresa' => $empresa,
                'user'    => $user,
                'email'   => $user->email,
                'senha'   => $senhaPadrao,
            ];
        });

        // Envia e-mail para o administrador com as credenciais de acesso.
        try {
            Mail::to($payload['user']->email)
                ->send(new BoasVindasAdminMail($payload['empresa'], $payload['user'], $payload['senha']));
        } catch (\Throwable) {
            // Não bloqueia o cadastro se o envio de e-mail falhar.
        }

        // Envia e-mail para o e-mail da empresa (pode ser diferente do admin).
        try {
            $emailEmpresa = $payload['empresa']->email;
            if ($emailEmpresa && $emailEmpresa !== $payload['user']->email) {
                Mail::to($emailEmpresa)
                    ->send(new BoasVindasEmpresaMail($payload['empresa'], $payload['user'], $payload['senha']));
            }
        } catch (\Throwable) {
            // Não bloqueia o cadastro se o envio de e-mail falhar.
        }

        return redirect()->route('cadastro.empresa.success')
            ->with('cadastro_empresa_email', $payload['email'])
            ->with('cadastro_empresa_senha', $payload['senha']);
    }

    public function success(Request $request): View
    {
        $email = (string) session('cadastro_empresa_email', '');
        $senha = (string) session('cadastro_empresa_senha', '');

        return view('auth.cadastro-empresa-success', compact('email', 'senha'));
    }
}
