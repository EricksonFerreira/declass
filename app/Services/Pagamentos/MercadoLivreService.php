<?php

namespace App\Services\Pagamentos;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use Ramsey\Uuid\Uuid;

class MercadoLivreService implements IPagamento
{
    public function teste($request)
    {
        return ['status' => 'error', 'message' => 'Metodo de teste legado descontinuado.'];
    }

    public function cobrarPagamento(array $dados): array
    {
        $accessToken = (string) ($dados['mercado_livre_access_token'] ?? env('MERCADOPAGO_ACCESS_TOKEN'));
        if ($accessToken === '') {
            return ['status' => 'error', 'message' => 'Access token do Mercado Livre nao informado.'];
        }

        MercadoPagoConfig::setAccessToken($accessToken);
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

        $client = new PaymentClient();
        try {
            $request = [
                'transaction_amount' => (float) ($dados['valor'] ?? 0),
                'token' => (string) ($dados['token'] ?? ''),
                'description' => (string) ($dados['descricao'] ?? 'Pagamento de reserva'),
                'installments' => (int) ($dados['installments'] ?? 1),
                'payment_method_id' => (string) ($dados['payment_method_id'] ?? ''),
                'payer' => [
                    'email' => (string) ($dados['payer_email'] ?? ''),
                ],
            ];

            $requestOptions = new RequestOptions();
            $requestOptions->setCustomHeaders(['X-Idempotency-Key: ' . Uuid::uuid4()->toString()]);

            $payment = $client->create($request, $requestOptions);
            $status = isset($payment->status) && $payment->status === 'approved' ? 'success' : 'error';

            return [
                'status' => $status,
                'transaction_id' => $payment->id ?? null,
                'raw_status' => $payment->status ?? null,
                'currency' => $dados['currency'] ?? 'BRL',
            ];
        } catch (MPApiException $e) {
            return [
                'status' => 'error',
                'message' => 'Erro ao processar pagamento no Mercado Livre.',
                'details' => $e->getApiResponse()->getContent(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function cancelarPagamento(string $id): array
    {
        return [
            'status' => 'error',
            'message' => 'Cancelamento nao implementado para Mercado Livre neste fluxo.',
            'transaction_id' => $id,
        ];
    }

    public function reembolsarPagamento(string $id): array
    {
        return [
            'status' => 'error',
            'message' => 'Reembolso nao implementado para Mercado Livre neste fluxo.',
            'transaction_id' => $id,
        ];
    }
}
