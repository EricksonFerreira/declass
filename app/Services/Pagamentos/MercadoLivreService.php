<?php

namespace App\Services\Pagamentos;
use App\Models\Transacao;
use Exception;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\CardToken\CardTokenClient;
use Ramsey\Uuid\Uuid;
use Request;

// use MercadoPago\Client\Payment\PaymentClient;
// use MercadoPago\MercadoPagoConfig;



class MercadoLivreService implements IPagamento
{


    public function teste($request){
        MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));

        $client = new PaymentClient();

        $request_options = new RequestOptions();
        $uniqueValue = Uuid::uuid4()->toString();
        // $request_options->setAccessToken(env('MP_PUBLIC_KEY'));
        $request_options->setCustomHeaders(["X-Idempotency-Key: $uniqueValue"]);

        $dataPayment = [
            "token" => $request['token'],
            "issuer_id" => $request['issuer_id'],
            "payment_method_id" => $request['payment_method_id'],
            "transaction_amount" => (float) $request['transaction_amount'],
            "installments" => $request['installments'],
            "payer" => [
                "email" => $request['payer']['email'],
                "identification" => [
                    "type" => $request['payer']['identification']['type'],
                    "number" => $request['payer']['identification']['number']
                ]
            ]
        ];

        try {
            $payment = $client->create($dataPayment, $request_options);
            // dd($payment);
        } catch (MPApiException $e) {
            // Exibe detalhes do erro
            echo "Erro na API: " . $e->getMessage() . "\n";
            echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
            echo "Conteúdo: ";
            var_dump($e->getApiResponse()->getContent());
            dd();
        }

        //
        if(isset($payment) && $payment->status === 'approved'){
            // quero salvar isso no meu banco de dados
            Transacao::create([
                'reserva_id' => $request['reserva_id'],
                'valor' => (float) $request['transaction_amount'],
                'metodo_pagamento_id' => $payment->payment_method_id ?? '',
                'tipo_pagamento_id' =>$payment->payment_type_id ?? '',
                'status' => 'Completa',
            ]);

            return ['status' => 'success'];
        }

        return ['status' => 'error'];
    }

    public function cobrarPagamento(array $dados): array
    {

        MercadoPagoConfig::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));
        // TESTE - MercadoPagoConfig::LOCAL
        // PRODUCAO - MercadoPagoConfig::SERVER
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

        $client = new PaymentClient();
        try {

            // Step 4: Create the request array
            $request = [
                "transaction_amount" => 100,
                "token" => "YOUR_CARD_TOKEN",
                "description" => "description",
                "installments" => 1,
                "payment_method_id" => "visa",
                "payer" => [
                    "email" => "user@test.com",
                ]
            ];

            // Step 5: Create the request options, setting X-Idempotency-Key
            $request_options = new RequestOptions();

            $uniqueValue = Uuid::uuid4()->toString();
            $request_options->setCustomHeaders(["X-Idempotency-Key: $uniqueValue"]);

            // Step 6: Make the request
            $payment = $client->create($request, $request_options);
            echo $payment->id;

            return [$payment];


        // Step 7: Handle exceptions
        } catch (MPApiException $e) {
            echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
            echo "Content: ";
            var_dump($e->getApiResponse()->getContent());
            echo "\n";
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return [];
    }

    public function cancelarPagamento(string $id): array
    {
        return [];
    }

    public function reembolsarPagamento(string $id): array
    {
        return [];
    }

    private function gerarTokenCartao(
        $numero,
        $anoExpiracao,
        $mesExpiracao,
        $codigoSeguranca,
        $nomeUsuario = null,
        $tipoIdentificador = null,
        $valorIdentificador = null
    ) {
        // Verifica se os parâmetros obrigatórios estão preenchidos
        if (empty($numero) || empty($anoExpiracao) || empty($mesExpiracao) || empty($codigoSeguranca)) {
            throw new Exception(message: "Todos os campos obrigatórios devem ser preenchidos.");
        }

        $cardData = [
            'card_number' => $numero, // Número do cartão (exemplo de teste)
            // 'card_number' => str_replace(' ', '', $numero), // Número do cartão (exemplo de teste)
            'expiration_month' => $mesExpiracao,          // Mês de validade
            'expiration_year' => $anoExpiracao,         // Ano de validade
            'security_code' => $codigoSeguranca,            // Código de segurança
            'cardholder' => [
                'name' => $nomeUsuario, // Nome do titular do cartão
                'identification' => [
                    'type' => $tipoIdentificador,         // Tipo de documento
                    'number' => $valorIdentificador // Número do documento
                ]
            ]
        ];

        $uniqueValue = Uuid::uuid4()->toString();
        $request_options = new RequestOptions();
        $request_options->setAccessToken(env('MP_ACCESS_TOKEN'));
        $request_options->setCustomHeaders(["X-Idempotency-Key: $uniqueValue"]);
        $request_options->setConnectionTimeout(20);

        $client = new CardTokenClient();
        dump($client,$cardData,$request_options);
        try {
            $token = $client->create($cardData, $request_options);
            if (isset($token->id)) {
                echo "Token gerado com sucesso: " . $token->id;
            } else {
                echo "Erro ao gerar token.";
            }
        } catch (MPApiException $e) {
            // Verifica se a resposta é um array antes de usá-la
            $content = $e->getApiResponse()->getContent();
            if (is_array($content)) {
                echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
                echo "Content: ";
                var_dump($content);
            } else {
                echo "Erro: Resposta da API não é um array.";
            }
        } catch (Exception $e) {
            echo "Erro: " . $e->getMessage();
        }

        return [];
    }
}
