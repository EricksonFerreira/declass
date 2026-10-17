<?php

namespace App\Http\Controllers;

use App\Mail\BoasVindasAdminMail;
use App\Mail\BoasVindasEmpresaMail;
use App\Models\Empresa;
use App\Models\OnboardingStep;
use App\Models\Regra;
use App\Models\User;
use App\Services\PermissaoSyncService;
use App\Services\ReplicacaoBaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CadastroEmpresaController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.cadastro-empresa', [
            'planoPreferido' => (string) $request->query('plano', ''),
            'semSite' => (bool) $request->boolean('sem_site', false),
        ]);
    }

    public function store(Request $request, ReplicacaoBaseService $replicacaoBaseService, PermissaoSyncService $permissaoSyncService): RedirectResponse
    {
        $data = $request->validate([
            'empresa_nome' => 'required|string|max:200',
            'empresa_cnpj' => ['nullable', 'string', 'max:20', function ($attr, $value, $fail) {
                if ($value && ! self::validarCnpj($value)) {
                    $fail('CNPJ invalido.');
                }
            }],
            'empresa_email' => ['nullable', 'email', 'max:200', function ($attr, $value, $fail) {
                if ($value && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('E-mail da empresa invalido.');
                }
            }],
            'empresa_telefone' => ['nullable', 'string', 'max:30', function ($attr, $value, $fail) {
                if ($value && ! self::validarTelefone($value)) {
                    $fail('Telefone invalido. Use o formato (XX) XXXXX-XXXX.');
                }
            }],
            'admin_nome' => 'required|string|max:200',
            'admin_email' => ['required', 'email', 'max:200', Rule::unique('users', 'email')],
            'plano' => 'nullable|string|max:50',
            'sem_site' => 'nullable|boolean',
        ], [
            'empresa_nome.required' => 'Informe o nome da empresa.',
            'admin_nome.required' => 'Informe o nome do responsavel.',
            'admin_email.required' => 'Informe o e-mail do responsavel.',
            'admin_email.unique' => 'Este e-mail ja esta em uso.',
        ]);

        $senhaPadrao = 'Senh@123';

        // Garante que permissões existem antes da transação.
        $permissaoSyncService->syncIfNeeded();

        $payload = DB::transaction(function () use ($data, $replicacaoBaseService, $senhaPadrao, $permissaoSyncService) {
            $slugBase = Str::slug((string) $data['empresa_nome']);
            if ($slugBase === '') {
                $slugBase = 'empresa';
            }
            // Pega o primeiro nome (primeira palavra antes de hifen ou espaco)
            $slugPrimeira = explode('-', $slugBase)[0];
            // Verifica se o slug ja existe e adiciona numeros incrementais
            $slug = $slugPrimeira;
            $contador = 0;
            while (Empresa::where('slug', $slug)->exists()) {
                $contador++;
                $slug = $slugPrimeira.$contador;
            }

            $empresa = Empresa::create([
                'nome' => $data['empresa_nome'],
                'slug' => $slug,
                'ativo' => 'S',
                'cnpj' => $data['empresa_cnpj'] ?? null,
                'email' => $data['empresa_email'] ?? $data['admin_email'],
                'telefone' => $data['empresa_telefone'] ?? null,
            ]);

            // Replica cadastros base da empresa 1 para a nova empresa.
            try {
                $replicacaoBaseService->replicar(1, [$empresa->id], [
                    'horarios_diarios',
                    'dias_uteis',
                    'status_movimentacao',
                    'status_reserva',
                    'status_veiculos',
                    'tipo_frota',
                    'grupos_frota',
                    'catalogo_fipe',
                    'tipo_opcional',
                    'opcionais',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Replicacao parcial ao criar empresa.', [
                    'empresa_id' => $empresa->id,
                    'erro' => $e->getMessage(),
                ]);
            }

            // Perfis sao vinculados a empresa: cria 'administrador' e
            // 'usuario' ja com empresa_id da empresa recem-criada.
            $permissaoSyncService->garantirPapeisParaEmpresa((int) $empresa->id);

            $roleId = Regra::where('name', 'administrador')
                ->where('guard_name', 'web')
                ->where('empresa_id', $empresa->id)
                ->value('id');

            $user = User::create([
                'nome' => $data['admin_nome'],
                'email' => $data['admin_email'],
                'password' => $senhaPadrao,
                'empresa_id' => $empresa->id,
                'role_id' => $roleId,
                'forcar_troca_senha' => true,
            ]);

            // Trial de 7 dias: a primeira assinatura libera o acesso completo
            // (status 'trial' conta como plano ativo). Sem isso a empresa nova
            // nao tinha plano e todas as rotas ficavam bloqueadas.
            $planoSlug = trim((string) ($data['plano'] ?? ''));
            $plano = $planoSlug !== ''
                ? \App\Models\Plano::where('ativo', true)->where('slug', $planoSlug)->first()
                : null;
            // Sem escolha valida, a empresa entra no plano trial gratuito
            // (seed PlanosDeclockSeeder). Se o seed ainda nao rodou, cai
            // para o plano mais completo como seguranca.
            $plano ??= \App\Models\Plano::where('slug', \Database\Seeders\PlanosDeclockSeeder::SLUG_TRIAL)->first()
                ?? \App\Models\Plano::where('ativo', true)->orderByDesc('ordem')->first();

            if ($plano) {
                app(\App\Services\EmpresaPlanoService::class)->contratar(
                    empresa: $empresa,
                    plano: $plano,
                    periodicidade: 'mensal',
                    metodoPagamento: 'manual',
                    diaVencimento: null,
                    criadoPorUserId: $user->id,
                );
            }

            OnboardingStep::updateOrCreate(
                ['empresa_id' => $empresa->id],
                [
                    'step_empresa_configurada' => true,
                    'step_usuario_criado' => true,
                    'concluido' => false,
                ]
            );

            return [
                'empresa' => $empresa,
                'user' => $user,
                'email' => $user->email,
                'senha' => $senhaPadrao,
            ];
        });

        // Envia e-mail para o administrador com as credenciais de acesso.
        try {
            Mail::to($payload['user']->email)
                ->send(new BoasVindasAdminMail($payload['empresa'], $payload['user'], $payload['senha']));
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar e-mail de boas-vindas ao admin.', [
                'email' => $payload['user']->email,
                'empresa' => $payload['empresa']->nome,
                'exception' => $e->getMessage(),
            ]);
        }

        // Envia e-mail para o e-mail da empresa (pode ser diferente do admin).
        try {
            $emailEmpresa = $payload['empresa']->email;
            if ($emailEmpresa && $emailEmpresa !== $payload['user']->email) {
                Mail::to($emailEmpresa)
                    ->send(new BoasVindasEmpresaMail($payload['empresa'], $payload['user'], $payload['senha']));
            }
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar e-mail de boas-vindas a empresa.', [
                'email' => $payload['empresa']->email,
                'empresa' => $payload['empresa']->nome,
                'exception' => $e->getMessage(),
            ]);
        }

        return redirect()->route('cadastro.empresa.success')
            ->with('cadastro_empresa_email', $payload['email'])
            ->with('cadastro_empresa_senha', $payload['senha'])
            ->with('cadastro_empresa_slug', $payload['empresa']->slug);
    }

    public static function validarCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) !== 14) {
            return false;
        }
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $soma1 = 0;
        $peso1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $soma1 += (int) $cnpj[$i] * $peso1[$i];
        }
        $dig1 = $soma1 % 11 < 2 ? 0 : 11 - ($soma1 % 11);
        if ((int) $cnpj[12] !== $dig1) {
            return false;
        }

        $soma2 = 0;
        $peso2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $soma2 += (int) $cnpj[$i] * $peso2[$i];
        }
        $dig2 = $soma2 % 11 < 2 ? 0 : 11 - ($soma2 % 11);
        if ((int) $cnpj[13] !== $dig2) {
            return false;
        }

        return true;
    }

    public static function validarTelefone(string $telefone): bool
    {
        $telefone = preg_replace('/[^0-9]/', '', $telefone);

        return in_array(strlen($telefone), [10, 11], true);
    }

    public function success(Request $request): View
    {
        $email = (string) session('cadastro_empresa_email', '');
        $senha = (string) session('cadastro_empresa_senha', '');
        $slug = (string) session('cadastro_empresa_slug', '');

        return view('auth.cadastro-empresa-success', compact('email', 'senha', 'slug'));
    }
}
