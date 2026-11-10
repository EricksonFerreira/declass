<?php

namespace App\Services\Pagamentos;

use App\Models\Mensalidade;
use DomainException;
use RuntimeException;

/**
 * Orquestra a cobranca de mensalidades em qualquer gateway configurado:
 * monta o pagador a partir do responsavel financeiro da matricula,
 * chama o driver e persiste os retornos na mensalidade.
 */
class MensalidadeCobrancaService
{
    public function __construct(private readonly GatewayPagamentoManager $manager) {}

    /** Gera boleto ou Pix para a mensalidade no gateway configurado. */
    public function gerar(Mensalidade $mensalidade, string $metodo = 'BOLETO'): Mensalidade
    {
        abort_if($mensalidade->status !== 'pendente', 422, 'Apenas mensalidades pendentes podem gerar cobrança.');

        $empresa = $mensalidade->empresa;
        if (! $this->manager->configurado($empresa)) {
            throw new RuntimeException('Configure o gateway de pagamento em Empresa → Integrações antes de gerar cobranças.');
        }

        $responsavel = $this->responsavelFinanceiro($mensalidade);
        if (! $responsavel?->pessoa?->cpf) {
            throw new DomainException('O responsável da matrícula não tem CPF cadastrado — necessário para gerar a cobrança.');
        }

        $retorno = $this->manager->for($empresa)->cobrar([
            'valor' => $mensalidade->valor,
            'vencimento' => $mensalidade->vencimento->toDateString(),
            'descricao' => "Mensalidade {$mensalidade->competencia} — {$responsavel->aluno->pessoa->nome ?? ''}",
            'referencia' => "mensalidade:{$mensalidade->id}",
            'metodo' => $metodo,
            'customer' => [
                'nome' => $responsavel->pessoa->nome,
                'cpf_cnpj' => $responsavel->pessoa->cpf,
                'email' => $responsavel->pessoa->email,
                'telefone' => $responsavel->pessoa->telefone,
            ],
        ]);

        if (! $retorno['ok']) {
            throw new RuntimeException($retorno['erro'] ?? 'Falha ao gerar a cobrança no gateway.');
        }

        $mensalidade->update([
            'gateway' => $empresa->gateway_pagamento,
            'gateway_payment_id' => $retorno['payment_id'],
            'gateway_status' => AsaasGateway::traduzStatus($retorno['status'] ?? null),
            'boleto_url' => $retorno['boleto_url'],
            'linha_digitavel' => $retorno['linha_digitavel'],
            'pix_copia_cola' => $retorno['pix_copia_cola'],
            'gateway_synced_at' => now(),
        ]);

        return $mensalidade->refresh();
    }

    /** Consulta o gateway e aplica o status (baixa se recebida). */
    public function sincronizar(Mensalidade $mensalidade): string
    {
        if (! $mensalidade->gateway_payment_id) {
            throw new DomainException('Mensalidade sem cobrança gerada no gateway.');
        }

        $r = $this->manager->for($mensalidade->empresa)->consultar($mensalidade->gateway_payment_id);
        if (! $r['ok']) {
            throw new RuntimeException($r['erro'] ?? 'Falha ao consultar o gateway.');
        }

        $status = AsaasGateway::traduzStatus($r['status']);
        $mensalidade->update(array_filter([
            'gateway_status' => $status,
            'boleto_url' => $r['boleto_url'] ?? $mensalidade->boleto_url,
            'linha_digitavel' => $r['linha_digitavel'] ?? $mensalidade->linha_digitavel,
            'gateway_synced_at' => now(),
        ], fn ($v) => $v !== null));

        if ($status === 'recebida' && $mensalidade->status === 'pendente') {
            $this->baixar($mensalidade, $r['pago_em']);
        }

        return $status;
    }

    /** Baixa a mensalidade com a data real do recebimento vinda do gateway. */
    public function baixar(Mensalidade $mensalidade, ?string $pagoEm = null): void
    {
        $mensalidade->update([
            'status' => 'paga',
            'data_pagamento' => $pagoEm ?? now()->toDateString(),
            'forma_pagamento' => $mensalidade->gateway === 'asaas' ? 'gateway' : $mensalidade->forma_pagamento,
        ]);
    }

    /** Responsavel financeiro da matricula (primeiro vinculado). */
    private function responsavelFinanceiro(Mensalidade $mensalidade)
    {
        $aluno = $mensalidade->matricula?->aluno;

        return $aluno?->responsaveis()->first() ? tap($aluno->responsaveis->first(), fn ($r) => $r->aluno = $aluno) : null;
    }
}
