<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plano extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'planos';

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'valor_mensal',
        'valor_anual',
        'valor_veiculo_adicional',
        'quantidade_filiais',
        'valor_filial_adicional',
        'valor_armazenamento_adicional',
        'quantidade_armazenamento_adicional',
        'limite_veiculos',
        'limite_armazenamento_mb',
        'limite_usuarios',
        'limite_permissoes_por_regra',
        'site_vendas',
        'modulo_multas',
        'modulo_nfse',
        'api_integracao',
        'ativo',
        'destaque',
        'ordem',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'valor_mensal' => 'decimal:2',
        'valor_anual' => 'decimal:2',
        'valor_veiculo_adicional' => 'decimal:2',
        'quantidade_filiais' => 'integer',
        'valor_filial_adicional' => 'decimal:2',
        'valor_armazenamento_adicional' => 'decimal:2',
        'quantidade_armazenamento_adicional' => 'integer',
        'site_vendas' => 'boolean',
        'modulo_multas' => 'boolean',
        'modulo_nfse' => 'boolean',
        'api_integracao' => 'boolean',
        'ativo' => 'boolean',
        'destaque' => 'boolean',
        'limite_permissoes_por_regra' => 'integer',
        'limite_armazenamento_mb' => 'integer',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(ItemPlano::class, 'plano_id')->orderBy('ordem');
    }

    public function assinaturas(): HasMany
    {
        return $this->hasMany(EmpresaPlano::class, 'plano_id');
    }

    public function permissoes(): BelongsToMany
    {
        return $this->belongsToMany(Permissao::class, 'plano_permissao', 'plano_id', 'permission_id')
            ->withTimestamps();
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }

    public function getValorPorPeriodicidadeAttribute(): array
    {
        return [
            'mensal' => $this->valor_mensal,
            'anual' => $this->valor_anual,
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

    public function getLimiteArmazenamentoTextoAttribute(): string
    {
        return $this->limite_armazenamento_mb === null ? 'Ilimitado' : (string) $this->limite_armazenamento_mb.' MB';
    }

    /**
     * Lista completa e detalhada de todos os recursos do plano para exibição ao cliente.
     * Cada item tem: label, descricao, incluso (bool), icone, destaque (bool).
     *
     * @return array<int, array{label:string, descricao:string, incluso:bool, icone:string, destaque:bool}>
     */
    public function featuresDetalhadas(): array
    {
        return [
            // — Registro de Tempo —
            [
                'grupo' => 'Tempo',
                'label' => 'Rastreador de tempo',
                'descricao' => 'Inicie, pause e pare o timer em um clique, direto na tarefa em que está trabalhando.',
                'incluso' => true,
                'icone' => 'stopwatch',
                'destaque' => true,
            ],
            [
                'grupo' => 'Tempo',
                'label' => 'Timesheet semanal',
                'descricao' => 'Grade semanal para revisar, completar e ajustar as horas de cada dia.',
                'incluso' => true,
                'icone' => 'calendar-week',
                'destaque' => false,
            ],
            [
                'grupo' => 'Tempo',
                'label' => 'Registro manual de horas',
                'descricao' => 'Lance horas retroativas com data, duração e descrição da atividade.',
                'incluso' => true,
                'icone' => 'edit',
                'destaque' => false,
            ],
            // — Projetos —
            [
                'grupo' => 'Projetos',
                'label' => 'Projetos e tarefas',
                'descricao' => 'Organize o trabalho por projeto, cliente e tarefa, com responsáveis definidos.',
                'incluso' => true,
                'icone' => 'folder-open',
                'destaque' => true,
            ],
            [
                'grupo' => 'Projetos',
                'label' => 'Calendário e alocações',
                'descricao' => 'Visualize a agenda da equipe e aloque pessoas nos projetos por período.',
                'incluso' => true,
                'icone' => 'calendar-alt',
                'destaque' => false,
            ],
            [
                'grupo' => 'Projetos',
                'label' => 'Férias e folgas',
                'descricao' => 'Solicitação e aprovação de ausências, com visão da disponibilidade da equipe.',
                'incluso' => true,
                'icone' => 'umbrella-beach',
                'destaque' => false,
            ],
            // — Equipe —
            [
                'grupo' => 'Equipe',
                'label' => $this->limite_usuarios === null
                    ? 'Usuários ilimitados'
                    : ($this->limite_usuarios === 1 ? '1 usuário' : "Até {$this->limite_usuarios} usuários"),
                'descricao' => $this->limite_usuarios === null
                    ? 'Crie quantos usuários precisar, cada um com seu perfil de acesso.'
                    : ($this->limite_usuarios === 1
                        ? 'Acesso individual. Faça upgrade para adicionar colaboradores.'
                        : "Adicione até {$this->limite_usuarios} usuários com perfis e permissões diferenciados."),
                'incluso' => true,
                'icone' => 'users',
                'destaque' => true,
            ],
            [
                'grupo' => 'Equipe',
                'label' => 'Perfis e permissões',
                'descricao' => 'Defina o que cada colaborador pode ver e fazer dentro do sistema.',
                'incluso' => true,
                'icone' => 'user-shield',
                'destaque' => false,
            ],
            // — Relatórios —
            [
                'grupo' => 'Relatorios',
                'label' => 'Relatórios de produtividade',
                'descricao' => 'Horas por pessoa, projeto e período, com filtros rápidos.',
                'incluso' => true,
                'icone' => 'chart-bar',
                'destaque' => true,
            ],
            [
                'grupo' => 'Relatorios',
                'label' => 'Exportação de dados',
                'descricao' => 'Exporte timesheets e relatórios para compartilhar com clientes e diretoria.',
                'incluso' => true,
                'icone' => 'file-export',
                'destaque' => false,
            ],
            // — Extras —
            [
                'grupo' => 'Extras',
                'label' => 'API de integração',
                'descricao' => $this->api_integracao
                    ? 'Integre com sistemas externos via API REST documentada.'
                    : 'Não incluso neste plano.',
                'incluso' => $this->api_integracao,
                'icone' => 'plug',
                'destaque' => (bool) $this->api_integracao,
            ],
            [
                'grupo' => 'Extras',
                'label' => 'Suporte por e-mail',
                'descricao' => 'Canal de suporte para tirar dúvidas e resolver problemas.',
                'incluso' => true,
                'icone' => 'headset',
                'destaque' => false,
            ],
        ];
    }
}
