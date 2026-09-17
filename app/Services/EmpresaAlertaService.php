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

        // 1. Usuários excedentes
        if ($plano->limite_usuarios !== null) {
            $totalUsuarios = $empresa->users()->count();

            if ($totalUsuarios > $plano->limite_usuarios) {
                $excedente = $totalUsuarios - $plano->limite_usuarios;

                $this->criarOuAtualizar(
                    $empresa,
                    'usuarios_excedentes',
                    'critico',
                    'Equipe acima do limite do plano',
                    "Seu plano {$plano->nome} permite até {$plano->limite_usuarios} usuário(s). Você tem {$totalUsuarios}, excedendo em {$excedente}. Ajuste a equipe ou faça upgrade do plano.",
                    ['limite' => $plano->limite_usuarios, 'total' => $totalUsuarios, 'excedente' => $excedente]
                );
            } else {
                $this->resolver($empresa, 'usuarios_excedentes');
            }
        }

        // Estes avisos não devem mais aparecer no dashboard.
        $this->resolver($empresa, 'veiculos_excedentes');
        $this->resolver($empresa, 'modulo_multas_indisponivel');
        $this->resolver($empresa, 'modulo_nfse_indisponivel');
        $this->resolver($empresa, 'site_vendas_indisponivel');
    }

    /**
     * Verifica se a empresa pode contratar um plano menor no período atual.
     * Retorna null se permitido, ou mensagem de erro se bloqueado.
     */
    public function verificarDowngradeNoPeriodo(Empresa $empresa, \App\Models\Plano $novoPlano): ?string
    {
        $assinaturaAtual = $empresa->planoAtivo()->with('plano')->first();
        if (! $assinaturaAtual) {
            return null;
        }

        $planoAtual = $assinaturaAtual->plano;

        $valorAtual = (float) $assinaturaAtual->valor_cobrado;
        $valorNovo = $assinaturaAtual->periodicidade === 'anual'
            ? (float) $novoPlano->valor_anual * 12
            : (float) $novoPlano->valor_mensal;

        if ($valorNovo >= $valorAtual) {
            return null;
        }

        $pagamentoPagoNoPeriodo = \App\Models\EmpresaPlanoPagamento::where('empresa_id', $empresa->id)
            ->where('status', 'pago')
            ->whereYear('pago_em', now()->year)
            ->whereMonth('pago_em', now()->month)
            ->exists();

        if ($pagamentoPagoNoPeriodo) {
            return "Você já realizou um pagamento do plano {$planoAtual->nome} neste mês. O downgrade para {$novoPlano->nome} só poderá ser feito a partir do próximo período de cobrança (".now()->addMonth()->format('m/Y').').';
        }

        return null;
    }

    private function criarOuAtualizar(Empresa $empresa, string $tipo, string $nivel, string $titulo, string $mensagem, array $metadados): void
    {
        EmpresaAlerta::updateOrCreate(
            ['empresa_id' => $empresa->id, 'tipo' => $tipo, 'resolvido' => false],
            [
                'nivel' => $nivel,
                'titulo' => $titulo,
                'mensagem' => $mensagem,
                'metadados' => $metadados,
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
