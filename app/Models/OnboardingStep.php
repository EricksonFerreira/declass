<?php

namespace App\Models;

use App\Models\Plano;
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
        'step_empresa_configurada'  => 'boolean',
        'step_plano_contratado'     => 'boolean',
        'step_pagamento_realizado'  => 'boolean',
        'step_frota_cadastrada'     => 'boolean',
        'step_estacao_cadastrada'   => 'boolean',
        'step_usuario_criado'       => 'boolean',
        'step_site_configurado'     => 'boolean',
        'concluido'                 => 'boolean',
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
        $temSite     = $plano?->site_vendas ?? true;
        $limiteUsers = $plano?->limite_usuarios ?? null;
        // Só exibe "convidar colaborador" se o plano permite mais de 1 usuário.
        $podeConvidar = $limiteUsers === null || $limiteUsers > 1;

        $steps = [
            [
                'key'      => 'step_empresa_configurada',
                'label'    => 'Configurar dados da empresa',
                'descricao'=> 'Nome, CNPJ, logo e informações de contato.',
                'rota'     => 'empresa.edit',
                'params'   => [$this->empresa_id],
                'icone'    => 'building',
                'feito'    => $this->step_empresa_configurada,
                'visivel'  => true,
            ],
            [
                'key'      => 'step_plano_contratado',
                'label'    => 'Contratar um plano',
                'descricao'=> 'Escolha o plano ideal para o tamanho da sua operação.',
                'rota'     => 'assinatura.meu-plano',
                'icone'    => 'tags',
                'feito'    => $this->step_plano_contratado,
                'visivel'  => true,
            ],
            [
                'key'      => 'step_pagamento_realizado',
                'label'    => 'Realizar o primeiro pagamento',
                'descricao'=> 'Efetue o pagamento para liberar todos os recursos.',
                'rota'     => 'assinatura.onboarding-pagamento',
                'icone'    => 'credit-card',
                'feito'    => $this->step_pagamento_realizado,
                'visivel'  => true,
            ],
            [
                'key'      => 'step_estacao_cadastrada',
                'label'    => 'Cadastrar uma estação / filial',
                'descricao'=> 'Registre ao menos um ponto de retirada e devolução.',
                'rota'     => 'estacao.index',
                'params'   => [],
                'query'    => ['tour' => '1'],
                'icone'    => 'map-marker-alt',
                'feito'    => $this->step_estacao_cadastrada,
                'visivel'  => true,
            ],
            [
                'key'      => 'step_frota_cadastrada',
                'label'    => 'Cadastrar seu primeiro veículo',
                'descricao'=> 'Adicione os veículos disponíveis para locação.',
                'rota'     => 'veiculo.create',
                'icone'    => 'car',
                'feito'    => $this->step_frota_cadastrada,
                'visivel'  => true,
            ],
            [
                'key'      => 'step_usuario_criado',
                'label'    => 'Convidar um colaborador',
                'descricao'=> 'Crie usuários para sua equipe acessar o sistema.',
                'rota'     => 'usuario.index',
                'icone'    => 'users',
                'feito'    => $this->step_usuario_criado,
                'visivel'  => $podeConvidar,
            ],
            [
                'key'      => 'step_site_configurado',
                'label'    => 'Personalizar o site de reservas',
                'descricao'=> 'Configure cores, textos e imagens do seu site de vendas.',
                'rota'     => 'empresa.site-apresentacao.edit',
                'params'   => [$this->empresa_id],
                'icone'    => 'globe',
                'feito'    => $this->step_site_configurado,
                'visivel'  => $temSite,
            ],
        ];

        return array_values(array_filter($steps, fn ($s) => $s['visivel']));
    }
}
