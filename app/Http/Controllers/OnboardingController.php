<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\OnboardingStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
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
            'nome' => 'required|string|max:200',
            'cnpj' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:200',
            'telefone' => 'nullable|string|max:30',
        ], [
            'nome.required' => 'O nome da empresa é obrigatório.',
        ]);

        $empresa = Auth::user()->empresa;
        $empresa->update([
            'nome' => $data['nome'],
            'slug' => Str::slug($data['nome']),
            'cnpj' => $data['cnpj'] ?? null,
            'email' => $data['email'] ?? null,
            'telefone' => $data['telefone'] ?? null,
        ]);

        OnboardingStep::updateOrCreate(
            ['empresa_id' => $empresa->id],
            ['step_empresa_configurada' => true]
        );

        return redirect()->route('onboarding.orientacoes');
    }

    /**
     * Etapa de identidade visual da empresa.
     */
    public function cores(): View|RedirectResponse
    {
        $empresa = Auth::user()->empresa;
        if (! $empresa) {
            return redirect()->route('onboarding.empresa');
        }

        return view('onboarding.cores', compact('empresa'));
    }

    public function salvarCores(Request $request): RedirectResponse
    {
        $empresa = Auth::user()->empresa;
        if (! $empresa) {
            return redirect()->route('onboarding.empresa');
        }

        $data = $request->validate([
            'cor_primaria' => 'nullable|string|max:20',
            'cor_secundaria' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:2048',
        ]);

        $updateData = [];
        if (isset($data['cor_primaria'])) {
            $updateData['cor_primaria'] = $data['cor_primaria'];
        }
        if (isset($data['cor_secundaria'])) {
            $updateData['cor_secundaria'] = $data['cor_secundaria'];
        }

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            try {
                $path = $request->file('logo')->store('empresas/logo', 'public');
                if ($path) {
                    if ($empresa->logo_path) {
                        Storage::disk('public')->delete($empresa->logo_path);
                    }
                    $updateData['logo_path'] = $path;
                }
            } catch (\Throwable $e) {
                Log::warning('Falha ao salvar logo no onboarding.', [
                    'erro' => $e->getMessage(),
                    'empresa_id' => $empresa->id,
                ]);
            }
        }

        if (! empty($updateData)) {
            $empresa->update($updateData);
        }

        OnboardingStep::where('empresa_id', $empresa->id)
            ->update(['step_site_configurado' => true]);

        return redirect()->route('onboarding.orientacoes')
            ->with('success', 'Identidade visual definida com sucesso!');
    }

    /**
     * Etapa final – Orientações de configuração.
     */
    public function orientacoes(): View
    {
        $empresa = Auth::user()->empresa;
        $step = OnboardingStep::firstOrCreate(
            ['empresa_id' => $empresa->id],
            ['step_empresa_configurada' => true]
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
            'slug' => 'empresa-'.$user->id.'-'.Str::random(4),
            'ativo' => 'S',
        ]);

        $user->update(['empresa_id' => $empresa->id]);

        // Perfis padrao vinculados a empresa (administrador + usuario).
        app(\App\Services\PermissaoSyncService::class)
            ->garantirPapeisParaEmpresa((int) $empresa->id);

        OnboardingStep::firstOrCreate(['empresa_id' => $empresa->id]);
    }

    private function onboardingConcluido(): bool
    {
        $empresaId = Auth::user()->empresa_id;

        if (! $empresaId) {
            return false;
        }

        return OnboardingStep::where('empresa_id', $empresaId)
            ->where('concluido', true)
            ->exists();
    }
}
