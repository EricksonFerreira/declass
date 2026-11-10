<?php

namespace App\Contracts;

/**
 * Contrato agnostico de gateway de cobranca.
 * Cada provedor (Asaas, Mercado Pago, etc.) implementa esta interface;
 * o restante do sistema nao depende de nenhum provedor especifico.
 */
interface GatewayPagamento
{
    /**
     * Gera uma cobranca no gateway.
     * $dados: valor, vencimento, descricao, referencia, customer{nome,cpf_cnpj,email,telefone}, metodo.
     * Retorna: ['ok'=>bool,'payment_id','status','boleto_url','linha_digitavel','pix_copia_cola','erro']
     */
    public function cobrar(array $dados): array;

    /**
     * Consulta o status atual de uma cobranca no gateway.
     * Retorna: ['ok'=>bool,'status','pago_em','valor_recebido','erro']
     */
    public function consultar(string $paymentId): array;

    /**
     * Cancela/remove uma cobranca pendente no gateway.
     * Retorna: ['ok'=>bool,'erro']
     */
    public function cancelar(string $paymentId): array;
}
