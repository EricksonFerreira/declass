<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\EmpresaAlerta;
use App\Models\EmpresaPlano;

class EmpresaAlertaService
{
    /**
     * Executa todos os checks pós-pagamento e gera alertas necessários.
     */
    public function executarChecksPosPagemento(Empresa $empresa, EmpresaPlano $assinatura): void
    {
        $plano = $assinatura->plano;

        // 1. Veículos excedentes
        if ($plano->limite_veiculos !== null) {
            $totalVeiculos = \App\Models\Veiculo::where('empresa_id', $empresa->id)->where('ativo', true)->where('vendido', false)->count();
            if ($totalVeiculos > $plano->limite_veiculos) {
                $excedente = $totalVeiculos - $plano->limite_veiculos;
                $this->criarOuAtualizar($empresa, 'veiculos_excedentes', 'critico',
                    'Frota acima do limite do plano',
                    "Seu plano {$plano->nome} permite até {$plano->limite_veiculos} veículo(s). Você tem {$totalVeiculos} ativos, excedendo em {$excedente}. Desative os veículos excedentes para manter o sistema funcionando normalmente.",
                    ['limite' => $plano->limite_veiculos, 'total' => $totalVeiculos, 'excedente' => $excedente]
                );
            } else {
                $this->resolver($empresa, 'veiculos_excedentes');
            }
        }

        // 2. Módulo multas indisponível
        if (!$plano->modulo_multas) {
            $temMultas = \App\Models\Multa::where('empresa_id', $empresa->id)->exists()
                ?? false;
            if (class_exists(\App\Models\Multa::class) && $temMultas) {
                $this->criarOuAtualizar($empresa, 'modulo_multas_indisponivel', 'aviso',
                    'Módulo de multas não incluso no plano',
                    "Seu plano {$plano->nome} não inclui o módulo de multas. Os registros existentes foram mantidos, mas não será possível criar novas multas. Faça upgrade do plano para continuar usando este módulo.",
                    []
                );
            }
        } else {
            $this->resolver($empresa, 'modulo_multas_indisponivel');
        }

        // 3. NFS-e indisponível
        if (!$plano->modulo_nfse) {
            if ($empresa->nfse_ativo) {
                $this->criarOuAtualizar($empresa, 'modulo_nfse_indisponivel', 'aviso',
                    'Módulo NFS-e não incluso no plano',
                    "Seu plano {$plano->nome} não inclui o módulo de NFS-e. A emissão de notas fiscais foi desabilitada. Faça upgrade para reativar.",
                    []
                );
            }
        } else {
            $this->resolver($empresa, 'modulo_nfse_indisponivel');
        }

        // 4. Site de vendas indisponível
        if (!$plano->site_vendas) {
            if ($empresa->site_apresentacao_ativo) {
                $this->criarOuAtualizar($empresa, 'site_vendas_indisponivel', 'aviso',
                    'Site de vendas não incluso no plano',
                    "Seu plano {$plano->nome} não inclui o site de vendas. Seu site foi desativado automaticamente. Faça upgrade para reativá-lo.",
                    []
                );
            }
        } else {
            $this->resolver($empresa, 'site_vendas_indisponivel');
        }
    }

    /**
     * Verifica se a empresa pode contratar um plano menor no período atual.
     * Retorna null se permitido, ou mensagem de erro se bloqueado.
     */
    public function verificarDowngradeNoPeriodo(Empresa $empresa, \App\Models\Plano $novoPlano): ?string
    {
        $assinaturaAtual = $empresa->planoAtivo()->with('plano')->first();
        if (!$assinaturaAtual) return null;

        $planoAtual = $assinaturaAtual->plano;

        // Só bloqueia se for downgrade de valor
        $valorAtual = (float) $assinaturaAtual->valor_cobrado;
        $valorNovo  = $assinaturaAtual->periodicidade === 'anual'
            ? (float) $novoPlano->valor_anual
            : (float) $novoPlano->valor_mensal;

        if ($valorNovo >= $valorAtual) return null;

        // Verifica se há pagamento pago no mês atual
        $pagamentoPagoNoPeriodo = \App\Models\EmpresaPlanoPagamento::where('empresa_id', $empresa->id)
            ->where('status', 'pago')
            ->whereYear('pago_em', now()->year)
            ->whereMonth('pago_em', now()->month)
            ->exists();

        if ($pagamentoPagoNoPeriodo) {
            return "Você já realizou um pagamento do plano {$planoAtual->nome} neste mês. O downgrade para {$novoPlano->nome} só poderá ser feito a partir do próximo período de cobrança (" . now()->addMonth()->format('m/Y') . ").";
        }

        return null;
    }

    private function criarOuAtualizar(Empresa $empresa, string $tipo, string $nivel, string $titulo, string $mensagem, array $metadados): void
    {
        EmpresaAlerta::updateOrCreate(
            ['empresa_id' => $empresa->id, 'tipo' => $tipo, 'resolvido' => false],
            [
                'nivel'      => $nivel,
                'titulo'     => $titulo,
                'mensagem'   => $mensagem,
                'metadados'  => $metadados,
            ]
        );
    }

    private function resolver(Empresa $empresa, string $tipo): void
    {
        EmpresaAlerta::where('empresa_id', $empresa->id)
            ->where('tipo', $tipo)
            ->where('resolvido', false)
            ->update(['resolvido' => true, 'resolvido_em' => now()]);
    }
}
