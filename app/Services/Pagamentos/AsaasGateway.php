<?php

namespace App\Services\Pagamentos;

use App\Contracts\GatewayPagamento;
use Illuminate\Support\Facades\Http;

/**
 * Integracao Asaas (https://asaas.com/api/v3).
 * Boleto e Pix: cria/atualiza o customer pelo CPF e gera o payment;
 * a baixa chega pelo webhook ou pela sincronizacao (consultar).
 */
class AsaasGateway implements GatewayPagamento
{
    private const STATUS_PAGOS = ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'];
    private const STATUS_VENCIDOS = ['OVERDUE'];
    private const STATUS_CANCELADOS = ['DELETED', 'REFUNDED', 'REFUND_REQUESTED'];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $ambiente = 'sandbox'
    ) {}

    public function cobrar(array $dados): array
    {
        $valor = (float) ($dados['valor'] ?? 0);
        $customer = $dados['customer'] ?? [];
        $metodo = strtoupper((string) ($dados['metodo'] ?? 'BOLETO'));

        if ($valor <= 0 || empty($customer['cpf_cnpj'])) {
            return ['ok' => false, 'erro' => 'Valor ou CPF/CNPJ do pagador invalido.'];
        }

        $customerId = $this->upsertCustomer($customer);
        if (! $customerId) {
            return ['ok' => false, 'erro' => 'Falha ao cadastrar o pagador no Asaas.'];
        }

        $payload = [
            'customer' => $customerId,
            'billingType' => $metodo,
            'value' => $valor,
            'dueDate' => $dados['vencimento'] ?? now()->addDays(1)->toDateString(),
            'description' => $dados['descricao'] ?? 'Mensalidade escolar',
            'externalReference' => (string) ($dados['referencia'] ?? ''),
            'postalService' => false,
        ];

        $r = $this->http()->post('/payments', $payload);
        if (! $r->successful()) {
            return ['ok' => false, 'erro' => $this->erro($r)];
        }

        $p = $r->json();
        $saida = [
            'ok' => true,
            'payment_id' => $p['id'],
            'status' => $p['status'] ?? 'PENDING',
            'boleto_url' => $p['bankSlipUrl'] ?? $p['invoiceUrl'] ?? null,
            'linha_digitavel' => $p['identificationField'] ?? null,
            'pix_copia_cola' => null,
        ];

        if ($metodo === 'PIX') {
            $qr = $this->http()->get("/payments/{$p['id']}/pixQrCode");
            if ($qr->successful()) {
                $saida['pix_copia_cola'] = $qr->json('payload') ?? null;
            }
        }

        return $saida;
    }

    public function consultar(string $paymentId): array
    {
        $r = $this->http()->get("/payments/{$paymentId}");
        if (! $r->successful()) {
            return ['ok' => false, 'erro' => $this->erro($r)];
        }

        $p = $r->json();
        return [
            'ok' => true,
            'status' => $p['status'] ?? null,
            'pago_em' => $p['paymentDate'] ?? $p['confirmedDate'] ?? $p['clientPaymentDate'] ?? null,
            'valor_recebido' => $p['netValue'] ?? $p['value'] ?? null,
            'boleto_url' => $p['bankSlipUrl'] ?? $p['invoiceUrl'] ?? null,
            'linha_digitavel' => $p['identificationField'] ?? null,
        ];
    }

    public function cancelar(string $paymentId): array
    {
        $r = $this->http()->delete("/payments/{$paymentId}");

        return $r->successful()
            ? ['ok' => true]
            : ['ok' => false, 'erro' => $this->erro($r)];
    }

    /** Status interno traduzido para o fluxo do Declass. */
    public static function traduzStatus(?string $status): ?string
    {
        return match (true) {
            in_array($status, self::STATUS_PAGOS, true) => 'recebida',
            in_array($status, self::STATUS_VENCIDOS, true) => 'vencida',
            in_array($status, self::STATUS_CANCELADOS, true) => 'cancelada',
            default => 'pendente',
        };
    }

    private function upsertCustomer(array $c): ?string
    {
        $cpf = preg_replace('/\D/', '', (string) ($c['cpf_cnpj'] ?? ''));

        $busca = $this->http()->get('/customers', ['cpfCnpj' => $cpf]);
        if ($busca->successful() && ($busca->json('totalCount') ?? 0) > 0) {
            return $busca->json('data.0.id');
        }

        $r = $this->http()->post('/customers', [
            'name' => $c['nome'] ?? 'Responsável',
            'cpfCnpj' => $cpf,
            'email' => $c['email'] ?? null,
            'phone' => $c['telefone'] ?? null,
            'notificationDisabled' => false,
        ]);

        return $r->successful() ? $r->json('id') : null;
    }

    private function http()
    {
        return Http::withHeaders(['access_token' => $this->apiKey])
            ->timeout(20)
            ->baseUrl($this->ambiente === 'producao'
                ? 'https://api.asaas.com/v3'
                : 'https://sandbox.asaas.com/api/v3');
    }

    private function erro($r): string
    {
        return $r->json('errors.0.description') ?? ('Asaas HTTP '.$r->status());
    }
}
