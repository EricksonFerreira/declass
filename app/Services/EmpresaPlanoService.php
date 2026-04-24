<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\EmpresaPlanoPagamento;
use App\Models\OnboardingStep;
use App\Models\Plano;
use App\Models\Veiculo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmpresaPlanoService
{
    /**
     * Verifica se o novo plano é um downgrade em relação ao atual.
     */
    public function isDowngrade(Empresa $empresa, Plano $novoPlano): bool
    {
        $assinaturaAtual = $empresa->planoAtivo()->with('plano')->first();

        if (!$assinaturaAtual) {
            return false;
        }

        $planoAtual = $assinaturaAtual->plano;

        // É downgrade se o limite de veículos diminui.
        if ($novoPlano->limite_veiculos !== null) {
            $limiteAtual = $planoAtual->limite_veiculos;

            // Plano atual ilimitado → novo com limite = downgrade de frota.
            if ($limiteAtual === null) {
                return true;
            }

            if ($novoPlano->limite_veiculos < $limiteAtual) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retorna os veículos ativos que excedem o limite do novo plano.
     */
    public function veiculosExcedentes(Empresa $empresa, Plano $novoPlano): Collection
    {
        if ($novoPlano->limite_veiculos === null) {
            return collect();
        }

        $veiculos = $this->veiculosAtivos($empresa);

        if ($veiculos->count() <= $novoPlano->limite_veiculos) {
            return collect();
        }

        // Retorna os que excedem (os mais recentes primeiro para facilitar a escolha de desativar).
        return $veiculos->sortByDesc('created_at')
            ->slice($novoPlano->limite_veiculos)
            ->values();
    }

    /**
     * Conta veículos ativos da empresa.
     */
    public function veiculosAtivos(Empresa $empresa): Collection
    {
        return Veiculo::where('empresa_id', $empresa->id)
            ->where('ativo', true)
            ->where('vendido', false)
            ->with(['grupoFrota', 'modelo.marca'])
            ->orderBy('nome')
            ->get();
    }

    /**
     * Contrata ou troca o plano da empresa, criando a assinatura e o primeiro pagamento.
     * Retorna a nova EmpresaPlano.
     */
    public function contratar(
        Empresa $empresa,
        Plano $plano,
        string $periodicidade,
        string $metodoPagamento,
        ?int $diaVencimento,
        ?int $criadoPorUserId,
        array $veiculosDesativar = [],
        bool $renovacaoAutomatica = false,
    ): EmpresaPlano {
        return DB::transaction(function () use (
            $empresa, $plano, $periodicidade, $metodoPagamento,
            $diaVencimento, $criadoPorUserId, $veiculosDesativar, $renovacaoAutomatica
        ) {
            // Desativa veículos excedentes escolhidos pelo usuário.
            if (!empty($veiculosDesativar)) {
                Veiculo::whereIn('id', $veiculosDesativar)
                    ->where('empresa_id', $empresa->id)
                    ->update(['ativo' => false]);
            }

            // Cancela assinaturas anteriores ativas.
            EmpresaPlano::where('empresa_id', $empresa->id)
                ->whereIn('status', ['ativo', 'trial', 'aguardando_pagamento', 'inadimplente'])
                ->update(['status' => 'cancelado']);

            $valor = $periodicidade === 'anual' ? $plano->valor_anual : $plano->valor_mensal;

            $dataInicio     = now()->toDateString();
            $dataVencimento = $periodicidade === 'anual'
                ? now()->addYear()->toDateString()
                : now()->addMonth()->toDateString();

            $metodoCobranca = match($metodoPagamento) {
                'automatico' => 'cartao',
                default      => $metodoPagamento,
            };

            $assinatura = EmpresaPlano::create([
                'empresa_id'          => $empresa->id,
                'plano_id'            => $plano->id,
                'periodicidade'       => $periodicidade,
                'status'              => 'aguardando_pagamento',
                'metodo_cobranca'     => $metodoCobranca,
                'data_inicio'         => $dataInicio,
                'data_vencimento'     => $dataVencimento,
                'dia_vencimento'      => $diaVencimento,
                'renovacao_automatica'=> $renovacaoAutomatica && $metodoCobranca === 'cartao',
                'valor_cobrado'       => $valor,
                'criado_por_user_id'  => $criadoPorUserId,
            ]);

            // Cria o primeiro pagamento pendente.
            $vencimentoPagamento = $diaVencimento
                ? now()->setDay(min($diaVencimento, now()->daysInMonth))->addDays(3)->toDateString()
                : now()->addDays(3)->toDateString();

            EmpresaPlanoPagamento::create([
                'empresa_plano_id'      => $assinatura->id,
                'empresa_id'            => $empresa->id,
                'valor'                 => $valor,
                'periodicidade'         => $periodicidade,
                'metodo'                => $metodoCobranca,
                'status'                => 'pendente',
                'data_vencimento'       => $vencimentoPagamento,
                'referencia_interna'    => 'INI-' . strtoupper(substr($plano->slug, 0, 4)) . '-' . now()->format('YmdHis'),
                'instrucoes_pagamento'  => $this->instrucoesPagamento($metodoCobranca, $valor, $renovacaoAutomatica),
                'pix_chave'             => $metodoCobranca === 'pix' ? env('PIX_CHAVE') : null,
                'registrado_por_user_id'=> $criadoPorUserId,
            ]);

            // Atualiza onboarding.
            $this->marcarOnboardingPlanoContratado($empresa);

            return $assinatura;
        });
    }

    /**
     * Ativa a assinatura após pagamento confirmado internamente.
     */
    public function ativarAposConfirmacao(EmpresaPlanoPagamento $pagamento): void
    {
        DB::transaction(function () use ($pagamento) {
            $pagamento->update([
                'status'  => 'pago',
                'pago_em' => now(),
            ]);

            $pagamento->empresaPlano()->update(['status' => 'ativo']);

            $this->marcarOnboardingPagamentoRealizado($pagamento->empresa);
        });
    }

    private function instrucoesPagamento(string $metodo, float $valor, bool $renovacaoAutomatica = false): string
    {
        $v = 'R$ ' . number_format($valor, 2, ',', '.');

        return match ($metodo) {
            'boleto' => "Boleto de {$v}. Você receberá o boleto por e-mail em até 24 horas. Compensação em 1 a 3 dias úteis após o pagamento.",
            'pix'    => "Pague {$v} via PIX usando a chave abaixo. O pagamento é instantâneo — após confirmar envie o comprovante.",
            'cartao' => $renovacaoAutomatica
                ? "Cartão de crédito {$v} com renovação automática ativa. Sua assinatura será renovada automaticamente a cada período sem ação necessária."
                : "Cartão de crédito {$v}. Nossa equipe entrará em contato para processar o pagamento com segurança.",
            default  => "Pagamento de {$v}. Entre em contato com o suporte para finalizar a ativação.",
        };
    }

    private function marcarOnboardingPlanoContratado(Empresa $empresa): void
    {
        OnboardingStep::updateOrCreate(
            ['empresa_id' => $empresa->id],
            ['step_plano_contratado' => true]
        );
    }

    private function marcarOnboardingPagamentoRealizado(Empresa $empresa): void
    {
        OnboardingStep::where('empresa_id', $empresa->id)
            ->update(['step_pagamento_realizado' => true]);
    }
}
