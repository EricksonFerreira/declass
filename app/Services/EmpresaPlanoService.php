<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\EmpresaAlerta;
use App\Models\EmpresaPlano;
use App\Models\EmpresaPlanoPagamento;
use App\Models\OnboardingStep;
use App\Models\Plano;
use App\Models\Regra;
use App\Models\Veiculo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmpresaPlanoService
{
    public function valorTotalPlano(Plano $plano, string $periodicidade): float
    {
        return $periodicidade === 'anual'
            ? (float) $plano->valor_anual * 12
            : (float) $plano->valor_mensal;
    }

    /**
     * Cria uma cobrança de upgrade com proration (padrão SaaS).
     *
     * Regras:
     * - Upgrade no mesmo ciclo: cobra a diferença proporcional pelos dias restantes (não reinicia vencimento).
     * - Mensal -> Anual: cobra o anual (à vista) menos o crédito dos dias restantes e reinicia vigência por 12 meses.
     */
    public function criarCobrancaUpgrade(
        EmpresaPlano $assinatura,
        Plano $novoPlano,
        string $novaPeriodicidade,
        string $metodoPagamento,
        ?int $criadoPorUserId,
        bool $reiniciarVigenciaAoTrocarParaAnual = true,
    ): EmpresaPlanoPagamento {
        $periodicidadeAtual = (string) $assinatura->periodicidade;
        $dataInicio = Carbon::parse($assinatura->data_inicio)->startOfDay();
        $dataFim    = Carbon::parse($assinatura->data_vencimento)->startOfDay();
        $hoje       = now()->startOfDay();

        $diasTotal = max(1, $dataInicio->diffInDays($dataFim));
        $diasRestantes = max(0, $hoje->diffInDays($dataFim, false));

        $valorAtualTotal = (float) $assinatura->valor_cobrado;
        $valorNovoTotal  = $this->valorTotalPlano($novoPlano, $novaPeriodicidade);

        $reiniciaPeriodo = false;
        $valorCobrar = 0.0;

        if ($periodicidadeAtual === $novaPeriodicidade) {
            $diffTotal = $valorNovoTotal - $valorAtualTotal;
            $valorCobrar = ($diasRestantes / $diasTotal) * $diffTotal;
        } elseif ($periodicidadeAtual === 'mensal' && $novaPeriodicidade === 'anual' && $reiniciarVigenciaAoTrocarParaAnual) {
            $reiniciaPeriodo = true;
            $credito = ($diasRestantes / $diasTotal) * $valorAtualTotal;
            $valorCobrar = $valorNovoTotal - $credito;
        } else {
            throw new \RuntimeException('Troca de periodicidade não suportada para upgrade imediato neste fluxo.');
        }

        $valorCobrar = round(max(0, $valorCobrar), 2);
        if ($valorCobrar < 0.01) {
            throw new \RuntimeException('Não há valor a cobrar para este upgrade.');
        }

        $vencimentoPagamento = now()->addDays(3)->toDateString();

        return EmpresaPlanoPagamento::create([
            'empresa_plano_id'      => $assinatura->id,
            'empresa_id'            => $assinatura->empresa_id,
            'valor'                 => $valorCobrar,
            'periodicidade'         => $novaPeriodicidade,
            'metodo'                => $metodoPagamento,
            'status'                => 'pendente',
            'data_vencimento'       => $vencimentoPagamento,
            'referencia_interna'    => 'UPG-' . strtoupper(substr($novoPlano->slug, 0, 4)) . '-' . now()->format('YmdHis'),
            'instrucoes_pagamento'  => 'Upgrade de plano: após confirmar o pagamento, os recursos do novo plano são liberados.',
            'pix_chave'             => $metodoPagamento === 'pix' ? env('PIX_CHAVE') : null,
            'metadados'             => [
                'acao'               => 'upgrade',
                'novo_plano_id'      => $novoPlano->id,
                'nova_periodicidade' => $novaPeriodicidade,
                'reinicia_periodo'   => $reiniciaPeriodo,
                'dias_total'         => $diasTotal,
                'dias_restantes'     => $diasRestantes,
                'valor_atual_total'  => $valorAtualTotal,
                'valor_novo_total'   => $valorNovoTotal,
            ],
            'registrado_por_user_id'=> $criadoPorUserId,
        ]);
    }

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

            // Observação: `valor_anual` é o valor mensal equivalente do plano anual (com desconto).
            // Na cobrança anual, o total é `valor_anual * 12`.
            $valor = $periodicidade === 'anual'
                ? (float) $plano->valor_anual * 12
                : (float) $plano->valor_mensal;

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
                'metadados'             => ['acao' => 'contratacao'],
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
            $acao = (string) (($pagamento->metadados['acao'] ?? null) ?: 'contratacao');
            $empresa = $pagamento->empresa;

            $pagamento->update([
                'status'  => 'pago',
                'pago_em' => $pagamento->pago_em ?? now(),
            ]);

            $assinatura = $pagamento->empresaPlano;

            if ($acao === 'upgrade') {
                $novoPlanoId = (int) ($pagamento->metadados['novo_plano_id'] ?? 0);
                $novaPeriodicidade = (string) ($pagamento->metadados['nova_periodicidade'] ?? $assinatura->periodicidade);
                $reiniciaPeriodo = (bool) ($pagamento->metadados['reinicia_periodo'] ?? false);

                $novoPlano = Plano::findOrFail($novoPlanoId);
                $valorNovoTotal = $this->valorTotalPlano($novoPlano, $novaPeriodicidade);

                $updates = [
                    'plano_id'             => $novoPlano->id,
                    'periodicidade'        => $novaPeriodicidade,
                    'valor_cobrado'        => $valorNovoTotal,
                    // Se estava agendado um downgrade/cancelamento, o upgrade prevalece.
                    'proximo_plano_id'      => null,
                    'proxima_periodicidade' => null,
                    'mudanca_tipo'          => null,
                    'mudanca_efetiva_em'    => null,
                    'mudanca_solicitada_em' => null,
                ];

                if ($reiniciaPeriodo) {
                    $updates['data_inicio']     = now()->toDateString();
                    $updates['data_vencimento'] = now()->addYear()->toDateString();
                }

                $assinatura->update($updates);
            } else {
                $assinatura->update(['status' => 'ativo']);
            }

            // Cancela outros pagamentos pendentes da mesma assinatura (escopo por ação).
            $pendentes = EmpresaPlanoPagamento::where('empresa_plano_id', $assinatura->id)
                ->where('id', '!=', $pagamento->id)
                ->where('status', 'pendente');

            if ($acao === 'upgrade') {
                $pendentes->where('metadados->acao', 'upgrade');
            }

            $pendentes->update(['status' => 'cancelado']);

            if ($acao === 'contratacao') {
                $this->marcarOnboardingPagamentoRealizado($empresa);
            }

            // Executa checks pós-pagamento e gera alertas
            $assinatura->load('plano');
            app(EmpresaAlertaService::class)->executarChecksPosPagemento($empresa, $assinatura);
        });

        // Fora da transação: sync de permissões é pesado e não deve bloquear rollback
        $this->garantirPermissoesEmpresa($pagamento->empresa);
    }

    /**
     * Garante que os usuários da empresa têm o role correto após ativação/mudança de plano.
     *
     * - Sincroniza permissões globais (idempotente)
     * - Garante que o admin original (primeiro usuário) sempre tem o role 'administrador'
     * - Atribui 'administrador' como fallback a qualquer usuário sem role definido
     *
     * O plano em si restringe o acesso via middleware e filtros — não via roles diferentes.
     * Portanto o role 'administrador' é sempre o mesmo; o que muda entre planos é o que
     * esse role pode acessar, controlado por CheckPermissaoPorRota e PermissaoService.
     */
    private function garantirPermissoesEmpresa(Empresa $empresa): void
    {
        try {
            app(PermissaoSyncService::class)->syncIfNeeded();

            $roleAdminId = Regra::where('guard_name', 'web')
                ->whereRaw('LOWER(name) = ?', ['administrador'])
                ->value('id');

            if (!$roleAdminId) {
                return;
            }

            // O admin original é o usuário mais antigo da empresa (menor id).
            $adminOriginal = $empresa->users()->orderBy('id')->first();
            if ($adminOriginal && ((int) $adminOriginal->role_id !== (int) $roleAdminId)) {
                $adminOriginal->update(['role_id' => $roleAdminId]);
            }

            // Demais usuários sem role recebem 'administrador' como fallback.
            $empresa->users()
                ->where(fn ($q) => $q->whereNull('role_id')->orWhere('role_id', 0))
                ->update(['role_id' => $roleAdminId]);
        } catch (\Throwable) {
            // Nunca bloqueia a ativação do plano por falha de permissão
        }
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
