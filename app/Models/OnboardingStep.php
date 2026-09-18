<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingStep extends Model
{
    protected $table = 'onboarding_steps';

    protected $fillable = [
        'empresa_id',
        'step_empresa_configurada',
        'step_plano_contratado',
        'step_pagamento_realizado',
        'step_frota_cadastrada',
        'step_estacao_cadastrada',
        'step_usuario_criado',
        'step_site_configurado',
        'concluido',
    ];

    protected $casts = [
        'step_empresa_configurada' => 'boolean',
        'step_plano_contratado' => 'boolean',
        'step_pagamento_realizado' => 'boolean',
        'step_frota_cadastrada' => 'boolean',
        'step_estacao_cadastrada' => 'boolean',
        'step_usuario_criado' => 'boolean',
        'step_site_configurado' => 'boolean',
        'concluido' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function percentualConcluido(?Plano $plano = null): int
    {
        $steps = $this->stepsParaExibir($plano);
        $total = count($steps);

        if ($total === 0) {
            return 100;
        }

        $feitos = collect($steps)->filter(fn ($s) => (bool) $s['feito'])->count();

        return (int) round(($feitos / $total) * 100);
    }

    public function stepsParaExibir(?Plano $plano = null): array
    {
        $limiteUsers = $plano?->limite_usuarios ?? null;
        // Só exibe "convidar colaborador" se o plano permite mais de 1 usuário.
        $podeConvidar = $limiteUsers === null || $limiteUsers > 1;

        $temProjeto = ClockifyProjeto::where('empresa_id', $this->empresa_id)->exists();
        $temRegistro = ClockifyTimeTracker::where('empresa_id', $this->empresa_id)->exists();
        $temColaborador = User::where('empresa_id', $this->empresa_id)->count() > 1;

        $steps = [
            [
                'key' => 'step_empresa_configurada',
                'label' => 'Configurar dados da empresa',
                'descricao' => 'Nome, logo e informações de contato.',
                'rota' => 'onboarding.empresa',
                'icone' => 'building',
                'feito' => $this->step_empresa_configurada,
                'visivel' => true,
            ],
            [
                'key' => 'step_plano_contratado',
                'label' => 'Ativar o teste grátis',
                'descricao' => '7 dias gratuitos para conhecer o DECLOCK completo.',
                'rota' => 'assinatura.meu-plano',
                'icone' => 'rocket',
                'feito' => $this->step_plano_contratado,
                'visivel' => true,
            ],
            [
                'key' => 'step_frota_cadastrada',
                'label' => 'Criar seu primeiro projeto',
                'descricao' => 'Organize o trabalho por projeto, cliente e tarefa.',
                'rota' => 'clockify.projetos.index',
                'icone' => 'folder-open',
                'feito' => $this->step_frota_cadastrada || $temProjeto,
                'visivel' => true,
            ],
            [
                'key' => 'step_estacao_cadastrada',
                'label' => 'Registrar suas primeiras horas',
                'descricao' => 'Use o rastreador de tempo ou o timesheet para lançar horas.',
                'rota' => 'clockify.tracker.index',
                'icone' => 'stopwatch',
                'feito' => $this->step_estacao_cadastrada || $temRegistro,
                'visivel' => true,
            ],
            [
                'key' => 'step_usuario_criado',
                'label' => 'Convidar um colaborador',
                'descricao' => 'Crie usuários para sua equipe registrar tempo junto com você.',
                'rota' => 'usuario.index',
                'icone' => 'users',
                'feito' => $this->step_usuario_criado || $temColaborador,
                'visivel' => $podeConvidar,
            ],
        ];

        return array_values(array_filter($steps, fn ($s) => $s['visivel']));
    }
}
