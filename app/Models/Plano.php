<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plano extends Model
{
    use HasFactory;
    protected $table = 'planos';

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'valor_mensal',
        'valor_anual',
        'limite_veiculos',
        'limite_usuarios',
        'site_vendas',
        'modulo_multas',
        'modulo_nfse',
        'multi_estacao',
        'api_integracao',
        'ativo',
        'destaque',
        'ordem',
    ];

    protected $casts = [
        'valor_mensal'   => 'decimal:2',
        'valor_anual'    => 'decimal:2',
        'site_vendas'    => 'boolean',
        'modulo_multas'  => 'boolean',
        'modulo_nfse'    => 'boolean',
        'multi_estacao'  => 'boolean',
        'api_integracao' => 'boolean',
        'ativo'          => 'boolean',
        'destaque'       => 'boolean',
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(ItemPlano::class, 'plano_id')->orderBy('ordem');
    }

    public function assinaturas(): HasMany
    {
        return $this->hasMany(EmpresaPlano::class, 'plano_id');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }

    public function getValorPorPeriodicidadeAttribute(): array
    {
        return [
            'mensal' => $this->valor_mensal,
            'anual'  => $this->valor_anual,
        ];
    }

    public function getDescontoAnualPercentualAttribute(): int
    {
        if ($this->valor_mensal <= 0) {
            return 0;
        }

        return (int) round((1 - ($this->valor_anual / $this->valor_mensal)) * 100);
    }

    public function getEconomiaAnualAttribute(): float
    {
        return round(($this->valor_mensal - $this->valor_anual) * 12, 2);
    }

    public function getLimiteVeiculosTextoAttribute(): string
    {
        return $this->limite_veiculos === null ? 'Ilimitado' : (string) $this->limite_veiculos;
    }

    public function getLimiteUsuariosTextoAttribute(): string
    {
        return $this->limite_usuarios === null ? 'Ilimitado' : (string) $this->limite_usuarios;
    }

    /**
     * Lista completa e detalhada de todos os recursos do plano para exibição ao cliente.
     * Cada item tem: label, descricao, incluso (bool), icone, destaque (bool).
     *
     * @return array<int, array{label:string, descricao:string, incluso:bool, icone:string, destaque:bool}>
     */
    public function featuresDetalhadas(): array
    {
        $limite = $this->limite_veiculos === null ? 'Ilimitado' : $this->limite_veiculos;

        return [
            // — Frota —
            [
                'grupo'     => 'Frota',
                'label'     => "Até {$limite} veículos",
                'descricao' => $this->limite_veiculos === null
                    ? 'Gerencie uma frota ilimitada de veículos sem restrições.'
                    : "Cadastre e gerencie até {$limite} veículos ativos simultaneamente.",
                'incluso'   => true,
                'icone'     => 'car',
                'destaque'  => true,
            ],
            [
                'grupo'     => 'Frota',
                'label'     => 'Cadastro de veículos',
                'descricao' => 'Ficha completa: marca, modelo, placa, cor, ano, foto e documentação.',
                'incluso'   => true,
                'icone'     => 'clipboard-list',
                'destaque'  => false,
            ],
            [
                'grupo'     => 'Frota',
                'label'     => 'Controle de danos e sinistros',
                'descricao' => 'Registre avarias, fotos e sinistros vinculados a cada veículo.',
                'incluso'   => true,
                'icone'     => 'car-crash',
                'destaque'  => false,
            ],
            [
                'grupo'     => 'Frota',
                'label'     => 'Grupos de frota',
                'descricao' => 'Organize os veículos em categorias para facilitar locações e relatórios.',
                'incluso'   => true,
                'icone'     => 'layer-group',
                'destaque'  => false,
            ],
            [
                'grupo'     => 'Frota',
                'label'     => 'Movimentação de frota',
                'descricao' => 'Histórico completo de entradas, saídas e transferências entre estações.',
                'incluso'   => true,
                'icone'     => 'exchange-alt',
                'destaque'  => false,
            ],
            // — Reservas —
            [
                'grupo'     => 'Reservas',
                'label'     => 'Reservas completas',
                'descricao' => 'Criação, edição, check-in, check-out e histórico de todas as locações.',
                'incluso'   => true,
                'icone'     => 'calendar-check',
                'destaque'  => true,
            ],
            [
                'grupo'     => 'Reservas',
                'label'     => 'Contrato de locação digital',
                'descricao' => 'Gere, assine digitalmente e armazene contratos de forma automática.',
                'incluso'   => true,
                'icone'     => 'file-signature',
                'destaque'  => false,
            ],
            [
                'grupo'     => 'Reservas',
                'label'     => 'Vistoria fotográfica (checklist)',
                'descricao' => 'Registro fotográfico e vistoria estruturada no check-in e check-out.',
                'incluso'   => true,
                'icone'     => 'camera',
                'destaque'  => false,
            ],
            [
                'grupo'     => 'Reservas',
                'label'     => 'Opcionais e adicionais',
                'descricao' => 'Seguros, cadeirinhas, GPS e outros adicionais vinculados à reserva.',
                'incluso'   => true,
                'icone'     => 'plus-circle',
                'destaque'  => false,
            ],
            [
                'grupo'     => 'Reservas',
                'label'     => 'Cupons de desconto',
                'descricao' => 'Crie e aplique cupons de desconto nas reservas.',
                'incluso'   => true,
                'icone'     => 'tag',
                'destaque'  => false,
            ],
            [
                'grupo'     => 'Reservas',
                'label'     => 'Caução / depósito de segurança',
                'descricao' => 'Controle de caução em dinheiro, cartão ou transferência por reserva.',
                'incluso'   => true,
                'icone'     => 'shield-alt',
                'destaque'  => false,
            ],
            // — Financeiro —
            [
                'grupo'     => 'Financeiro',
                'label'     => 'Controle financeiro',
                'descricao' => 'Lançamentos, receitas, despesas e fluxo de caixa da locadora.',
                'incluso'   => true,
                'icone'     => 'dollar-sign',
                'destaque'  => true,
            ],
            [
                'grupo'     => 'Financeiro',
                'label'     => 'Recebimento por PIX e cartão',
                'descricao' => 'Processe pagamentos via PIX ou cartão de crédito diretamente na plataforma.',
                'incluso'   => true,
                'icone'     => 'credit-card',
                'destaque'  => false,
            ],
            [
                'grupo'     => 'Financeiro',
                'label'     => 'Relatórios e exportação',
                'descricao' => 'Relatórios de receita, ocupação e exportação para Excel/PDF.',
                'incluso'   => true,
                'icone'     => 'chart-bar',
                'destaque'  => false,
            ],
            // — Estações —
            [
                'grupo'     => 'Estacoes',
                'label'     => $this->multi_estacao ? 'Multi-estações / filiais' : '1 estação / filial',
                'descricao' => $this->multi_estacao
                    ? 'Gerencie múltiplas estações de retirada e devolução com agendas independentes.'
                    : 'Gerencie uma estação principal de retirada e devolução.',
                'incluso'   => true,
                'icone'     => 'map-marker-alt',
                'destaque'  => $this->multi_estacao,
            ],
            [
                'grupo'     => 'Estacoes',
                'label'     => 'Horários e disponibilidade',
                'descricao' => 'Configure horários de funcionamento e bloqueios por estação.',
                'incluso'   => true,
                'icone'     => 'clock',
                'destaque'  => false,
            ],
            // — Site de Vendas —
            [
                'grupo'     => 'Site',
                'label'     => 'Site de vendas online',
                'descricao' => $this->site_vendas
                    ? 'Site público personalizado para receber reservas online direto pelo cliente.'
                    : 'Não incluso neste plano.',
                'incluso'   => $this->site_vendas,
                'icone'     => 'globe',
                'destaque'  => $this->site_vendas,
            ],
            [
                'grupo'     => 'Site',
                'label'     => 'Reservas online pelo cliente',
                'descricao' => $this->site_vendas
                    ? 'Clientes fazem reservas 24h pelo seu site com pagamento integrado.'
                    : 'Não incluso neste plano.',
                'incluso'   => $this->site_vendas,
                'icone'     => 'laptop',
                'destaque'  => false,
            ],
            // — Módulo Multas —
            [
                'grupo'     => 'Modulos',
                'label'     => 'Módulo de multas',
                'descricao' => $this->modulo_multas
                    ? 'Controle completo de multas: vinculação ao condutor, contestação e pagamento.'
                    : 'Não incluso neste plano.',
                'incluso'   => $this->modulo_multas,
                'icone'     => 'file-invoice',
                'destaque'  => $this->modulo_multas,
            ],
            // — NFS-e —
            [
                'grupo'     => 'Modulos',
                'label'     => 'Emissão de NFS-e',
                'descricao' => $this->modulo_nfse
                    ? 'Emita notas fiscais de serviço eletrônicas diretamente pela plataforma.'
                    : 'Não incluso neste plano.',
                'incluso'   => $this->modulo_nfse,
                'icone'     => 'receipt',
                'destaque'  => $this->modulo_nfse,
            ],
            // — API —
            [
                'grupo'     => 'Modulos',
                'label'     => 'API de integração',
                'descricao' => $this->api_integracao
                    ? 'Integre com sistemas externos via API REST documentada.'
                    : 'Não incluso neste plano.',
                'incluso'   => $this->api_integracao,
                'icone'     => 'plug',
                'destaque'  => $this->api_integracao,
            ],
            // — Gestão —
            [
                'grupo'     => 'Gestao',
                'label'     => $this->limite_usuarios === null
                    ? 'Usuários ilimitados'
                    : ($this->limite_usuarios === 1 ? '1 usuário' : "Até {$this->limite_usuarios} usuários"),
                'descricao' => $this->limite_usuarios === null
                    ? 'Crie quantos usuários precisar com perfis e permissões diferenciados.'
                    : ($this->limite_usuarios === 1
                        ? 'Acesso exclusivo para o proprietário. Faça upgrade para adicionar colaboradores.'
                        : "Adicione até {$this->limite_usuarios} usuários com perfis e permissões diferenciados."),
                'incluso'   => true,
                'icone'     => 'users',
                'destaque'  => true,
            ],
            [
                'grupo'     => 'Gestao',
                'label'     => 'Perfis de acesso (roles)',
                'descricao' => 'Defina o que cada colaborador pode ver e fazer: gerente, operador, financeiro.',
                'incluso'   => true,
                'icone'     => 'user-shield',
                'destaque'  => false,
            ],
            [
                'grupo'     => 'Gestao',
                'label'     => 'Suporte por e-mail e WhatsApp',
                'descricao' => 'Canal de suporte dedicado para tirar dúvidas e resolver problemas.',
                'incluso'   => true,
                'icone'     => 'headset',
                'destaque'  => false,
            ],
        ];
    }
}
