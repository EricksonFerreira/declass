<?php

namespace App\Services;

use App\Exceptions\ClienteNaoEncontradoException;
use App\Exceptions\JaExisteClienteComEmailException;
use App\Exceptions\MultiplosClientesComEmailException;

class ClienteStripeService
{
    protected $stripe;
    protected $stripeSecret;

    public function __construct()
    {
        $this->stripeSecret = env('STRIPE_SECRET');
        $this->stripe = new \Stripe\StripeClient($this->stripeSecret);
    }

    public function getClienteByEmail($email){
        $cliente = $this->stripe->customers->search([
            'query' => 'email:\''.$email.'\'',
        ]);

        return $cliente;
    }

    public function create($email, $name){
        $cliente = $this->getClienteByEmail($email);

        if($cliente->data != []){
            throw new JaExisteClienteComEmailException();
        }

        $cliente = $this->stripe->customers->create([
            'email' => $email,
            'name' => $name,
            'description' => 'Cliente do sistema de reservas',
        ]);

        return $cliente;
    }

    public function update($id, $email, $name){
        $cliente = $this->stripe->customers->update($id, [
            'email' => $email,
            'name' => $name,
        ]);
        return $cliente;
    }

    public function delete($id){
        $cliente = $this->stripe->customers->delete($id);
        return $cliente;
    }


    // Pagamentos
    public function getPaymentMethods($customerId){
        if($customerId == null){
            throw new ClienteNaoEncontradoException();
        }

        $paymentMethods = $this->stripe->paymentMethods->all([
            'customer' => $customerId,
        ]);

        $metodosPagamento = array_map(fn($item) => [
            'id' => $item->id, // Adiciona o ID do método de pagamento
            'card' => $item->card
        ], $paymentMethods->data);

        return $metodosPagamento;
    }

    public function createPaymentMethod($customerId, $token){
        // Verifica se o método de pagamento já existe
        $paymentMethods = $this->getPaymentMethods($customerId);

        foreach ($paymentMethods->data as $method) {
            if ($method->card->id === $token) {
                // Retorna o método de pagamento existente se já foi criado
                return $method;
            }
        }

        $data = [
            'type' => 'card',
            'card' => [
                'token' => $token, // Use o token em vez de enviar os dados do cartão diretamente
            ],
        ];

        $paymentMethod = $this->stripe->paymentMethods->create($data);

        $this->vinculaPaymentMethodComCliente($paymentMethod->id, $customerId);
        return $paymentMethod; // Retorna o método de pagamento criado
    }
    public function deletePaymentMethod($paymentMethodId){
        $this->stripe->paymentMethods->detach($paymentMethodId);
    }
    public function createCharge($paymentMethodId, $valorTotal, $customerId){
        $valorTotalCentavos = intval($valorTotal * 100); // Converte o valor para centavos
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $valorTotalCentavos,
            'currency' => 'brl',
            'payment_method' => $paymentMethodId,
            'customer' => $customerId,
            'confirm' => true, // Confirma o pagamento imediatamente
            'return_url' => env('RETURN_URL_PAGAMENTO'), // Adicione sua URL de retorno com esquema
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never', // Não permite redirecionamentos
            ],
        ]);
        return $paymentIntent; // Retorna o Payment Intent criado
    }


    public function getCharge($id){
        $charge = $this->stripe->paymentIntents->retrieve($id, []);
        return $charge;
    }

    private function vinculaPaymentMethodComCliente($paymentMethodId, $customerId){
        $this->stripe->paymentMethods->attach(
            $paymentMethodId,
            ['customer' => $customerId]
          );
    }

    public function creteTokenPaymentMethod($numeroCartao, $mes, $ano, $cvv){
        return  $this->stripe->tokens->create([
            'card' => [
                'number' => $numeroCartao,
                'exp_month' => $mes,
                'exp_year' => $ano,
                'cvc' => $cvv,
            ],
        ]);
    }

}
