<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\OnboardingStep;
use App\Models\Plano;
use App\Models\User;
use App\Services\EmpresaPlanoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(private EmpresaPlanoService $planoService) {}

    /**
     * Etapa 1 – Dados da empresa.
     */
    public function empresa(): View|RedirectResponse
    {
        $this->garantirEmpresaDoUsuario();

        if ($this->onboardingConcluido()) {
            return redirect()->route('home');
        }

        return view('onboarding.empresa');
    }

    public function salvarEmpresa(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome'     => 'required|string|max:200',
            'cnpj'     => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:200',
            'telefone' => 'nullable|string|max:30',
        ], [
            'nome.required' => 'O nome da empresa é obrigatório.',
        ]);

        $empresa = Auth::user()->empresa;
        $empresa->update([
            'nome'     => $data['nome'],
            'slug'     => Str::slug($data['nome']),
            'cnpj'     => $data['cnpj'] ?? null,
            'email'    => $data['email'] ?? null,
            'telefone' => $data['telefone'] ?? null,
        ]);

        OnboardingStep::updateOrCreate(
            ['empresa_id' => $empresa->id],
            ['step_empresa_configurada' => true]
        );

        return redirect()->route('onboarding.plano');
    }

    /**
     * Etapa 2 – Escolha do plano.
     */
    public function plano(): View|RedirectResponse
    {
        if ($this->onboardingConcluido()) {
            return redirect()->route('home');
        }

        $planos = Plano::ativos()->with('itens')->get();

        return view('onboarding.plano', compact('planos'));
    }

    /**
     * Etapa 3 – Configurar pagamento (exibida após escolha do plano).
     */
    public function pagamento(Request $request): View|RedirectResponse
    {
        $planoId       = $request->query('plano_id') ?? session('onboarding_plano_id');
        $periodicidade = $request->query('periodicidade', 'mensal');

        if (!$planoId) {
            return redirect()->route('onboarding.plano');
        }

        $plano = Plano::findOrFail($planoId);
        session(['onboarding_plano_id' => $planoId, 'onboarding_periodicidade' => $periodicidade]);

        return view('onboarding.pagamento', compact('plano', 'periodicidade'));
    }

    /**
     * Processa a escolha do plano + forma de pagamento.
     * Se houver downgrade, redireciona para seleção de veículos.
     */
    public function confirmarPlano(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plano_id'        => 'required|exists:planos,id',
            'periodicidade'   => 'required|in:mensal,anual',
            'metodo_pagamento'=> 'required|in:boleto,pix,automatico',
            'dia_vencimento'  => 'nullable|integer|min:1|max:28',
        ]);

        $empresa = Auth::user()->empresa;
        $plano   = Plano::findOrFail($data['plano_id']);

        $excedentes = $this->planoService->veiculosExcedentes($empresa, $plano);

        if ($excedentes->isNotEmpty()) {
            session([
                'downgrade_plano_id'        => $data['plano_id'],
                'downgrade_periodicidade'   => $data['periodicidade'],
                'downgrade_metodo'          => $data['metodo_pagamento'],
                'downgrade_dia_vencimento'  => $data['dia_vencimento'],
            ]);

            return redirect()->route('onboarding.downgrade');
        }

        $this->planoService->contratar(
            empresa:          $empresa,
            plano:            $plano,
            periodicidade:    $data['periodicidade'],
            metodoPagamento:  $data['metodo_pagamento'],
            diaVencimento:    $data['dia_vencimento'] ?? null,
            criadoPorUserId:  Auth::id(),
        );

        return redirect()->route('onboarding.orientacoes')
            ->with('success', 'Plano contratado com sucesso!');
    }

    /**
     * Etapa de downgrade: usuário escolhe quais veículos desativar.
     */
    public function downgrade(): View|RedirectResponse
    {
        $planoId = session('downgrade_plano_id');

        if (!$planoId) {
            return redirect()->route('onboarding.plano');
        }

        $empresa    = Auth::user()->empresa;
        $plano      = Plano::findOrFail($planoId);
        $excedentes = $this->planoService->veiculosExcedentes($empresa, $plano);
        $todos      = $this->planoService->veiculosAtivos($empresa);

        return view('onboarding.downgrade', compact('plano', 'excedentes', 'todos'));
    }

    public function confirmarDowngrade(Request $request): RedirectResponse
    {
        $planoId = session('downgrade_plano_id');

        if (!$planoId) {
            return redirect()->route('onboarding.plano');
        }

        $plano   = Plano::findOrFail($planoId);
        $empresa = Auth::user()->empresa;

        $data = $request->validate([
            'veiculos_desativar'   => 'required|array|min:1',
            'veiculos_desativar.*' => 'integer|exists:veiculos,id',
        ], [
            'veiculos_desativar.required' => 'Selecione ao menos um veículo para desativar.',
            'veiculos_desativar.min'      => 'Selecione ao menos um veículo para desativar.',
        ]);

        $ainda = $this->planoService->veiculosAtivos($empresa)
            ->whereNotIn('id', $data['veiculos_desativar'])
            ->count();

        if ($plano->limite_veiculos !== null && $ainda > $plano->limite_veiculos) {
            return back()->withErrors([
                'veiculos_desativar' => "Você precisa desativar pelo menos " .
                    ($this->planoService->veiculosAtivos($empresa)->count() - $plano->limite_veiculos) .
                    " veículo(s) para se enquadrar no limite do plano {$plano->nome}.",
            ]);
        }

        $this->planoService->contratar(
            empresa:          $empresa,
            plano:            $plano,
            periodicidade:    session('downgrade_periodicidade', 'mensal'),
            metodoPagamento:  session('downgrade_metodo', 'boleto'),
            diaVencimento:    session('downgrade_dia_vencimento'),
            criadoPorUserId:  Auth::id(),
            veiculosDesativar:$data['veiculos_desativar'],
        );

        session()->forget(['downgrade_plano_id', 'downgrade_periodicidade', 'downgrade_metodo', 'downgrade_dia_vencimento']);

        return redirect()->route('onboarding.orientacoes')
            ->with('success', 'Plano alterado e veículos ajustados com sucesso.');
    }

    /**
     * Etapa final – Orientações de configuração.
     */
    public function orientacoes(): View
    {
        $empresa = Auth::user()->empresa;
        $step    = OnboardingStep::firstOrCreate(
            ['empresa_id' => $empresa->id],
            ['step_empresa_configurada' => true, 'step_plano_contratado' => true]
        );

        return view('onboarding.orientacoes', compact('step', 'empresa'));
    }

    public function concluir(): RedirectResponse
    {
        $empresa = Auth::user()->empresa;

        OnboardingStep::where('empresa_id', $empresa->id)
            ->update(['concluido' => true]);

        return redirect()->route('home')
            ->with('success', 'Bem-vindo! Sua conta está configurada e pronta para uso.');
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function garantirEmpresaDoUsuario(): void
    {
        $user = Auth::user();

        if ($user->empresa_id) {
            return;
        }

        $empresa = Empresa::create([
            'nome' => 'Minha Empresa',
            'slug' => 'empresa-' . $user->id . '-' . Str::random(4),
            'ativo'=> 'S',
        ]);

        $user->update(['empresa_id' => $empresa->id]);

        OnboardingStep::firstOrCreate(['empresa_id' => $empresa->id]);
    }

    private function onboardingConcluido(): bool
    {
        $empresaId = Auth::user()->empresa_id;

        if (!$empresaId) {
            return false;
        }

        return OnboardingStep::where('empresa_id', $empresaId)
            ->where('concluido', true)
            ->exists();
    }
}
