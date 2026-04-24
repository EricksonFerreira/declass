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

    public function percentualConcluido(): int
    {
        $steps = [
            'step_empresa_configurada',
            'step_plano_contratado',
            'step_pagamento_realizado',
            'step_estacao_cadastrada',
            'step_frota_cadastrada',
            'step_usuario_criado',
            'step_site_configurado',
        ];

        $total    = count($steps);
        $feitos   = collect($steps)->filter(fn ($s) => (bool) $this->$s)->count();

        return (int) round(($feitos / $total) * 100);
    }

    public function stepsParaExibir(): array
    {
        return [
            [
                'key'      => 'step_empresa_configurada',
                'label'    => 'Configurar dados da empresa',
                'descricao'=> 'Nome, CNPJ, logo e informações de contato.',
                'rota'     => 'empresa.edit',
                'icone'    => 'building',
                'feito'    => $this->step_empresa_configurada,
            ],
            [
                'key'      => 'step_plano_contratado',
                'label'    => 'Contratar um plano',
                'descricao'=> 'Escolha o plano ideal para o tamanho da sua operação.',
                'rota'     => 'assinatura.meu-plano',
                'icone'    => 'tags',
                'feito'    => $this->step_plano_contratado,
            ],
            [
                'key'      => 'step_pagamento_realizado',
                'label'    => 'Realizar o primeiro pagamento',
                'descricao'=> 'Efetue o pagamento para liberar todos os recursos.',
                'rota'     => 'assinatura.meu-plano',
                'icone'    => 'credit-card',
                'feito'    => $this->step_pagamento_realizado,
            ],
            [
                'key'      => 'step_estacao_cadastrada',
                'label'    => 'Cadastrar uma estação / filial',
                'descricao'=> 'Registre ao menos um ponto de retirada e devolução.',
                'rota'     => 'estacao.index',
                'icone'    => 'map-marker-alt',
                'feito'    => $this->step_estacao_cadastrada,
            ],
            [
                'key'      => 'step_frota_cadastrada',
                'label'    => 'Cadastrar sua frota',
                'descricao'=> 'Adicione os veículos disponíveis para locação.',
                'rota'     => 'frota.index',
                'icone'    => 'car',
                'feito'    => $this->step_frota_cadastrada,
            ],
            [
                'key'      => 'step_usuario_criado',
                'label'    => 'Convidar um colaborador',
                'descricao'=> 'Crie usuários para sua equipe acessar o sistema.',
                'rota'     => 'usuario.index',
                'icone'    => 'users',
                'feito'    => $this->step_usuario_criado,
            ],
            [
                'key'      => 'step_site_configurado',
                'label'    => 'Personalizar o site de reservas',
                'descricao'=> 'Configure cores, textos e imagens do seu site de vendas.',
                'rota'     => 'empresa.site-apresentacao.edit',
                'icone'    => 'globe',
                'feito'    => $this->step_site_configurado,
            ],
        ];
    }
}
