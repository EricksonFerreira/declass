<?php

namespace App\Services;

use App\Exceptions\ClienteNaoEncontradoException;
use App\Exceptions\JaExisteClienteComEmailException;
use App\Exceptions\StripeConexaoException;
use Stripe\Customer;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\StripeClient;
use Stripe\Token;

class ClienteStripeService
{
    private function client(?string $secretOverride = null): StripeClient
    {
        $secret = $secretOverride ?: config('services.stripe.secret');

        if (! $secret) {
            throw new StripeConexaoException('Chave Stripe nao configurada para a empresa.');
        }

        try {
            return new StripeClient($secret);
        } catch (\Exception $e) {
            throw new StripeConexaoException('Erro ao conectar com a API do Stripe.', $e);
        }
    }

    /**
     * @throws StripeConexaoException
     */
    public function __construct()
    {
        // Mantido para compatibilidade por DI.
    }

    // -------------------------------------------------------------------------
    // Clientes
    // -------------------------------------------------------------------------

    /**
     * Busca um cliente no Stripe pelo e-mail.
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function buscarClientePorEmail(string $email, ?string $stripeSecret = null): \Stripe\SearchResult
    {
        return $this->client($stripeSecret)->customers->search([
            'query' => "email:'{$email}'",
        ]);
    }

    /**
     * Cria um novo cliente no Stripe.
     *
     * @throws JaExisteClienteComEmailException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function criarCliente(string $email, string $nome, ?string $stripeSecret = null): Customer
    {
        $resultado = $this->buscarClientePorEmail($email, $stripeSecret);

        if (! empty($resultado->data)) {
            throw new JaExisteClienteComEmailException();
        }

        return $this->client($stripeSecret)->customers->create([
            'email' => $email,
            'name' => $nome,
            'description' => 'Cliente do sistema de reservas',
        ]);
    }

    /**
     * Atualiza os dados de um cliente no Stripe.
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function atualizarCliente(string $id, string $email, string $nome, ?string $stripeSecret = null): Customer
    {
        return $this->client($stripeSecret)->customers->update($id, [
            'email' => $email,
            'name' => $nome,
        ]);
    }

    /**
     * Remove um cliente do Stripe.
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function deletarCliente(string $id, ?string $stripeSecret = null): \Stripe\Customer
    {
        return $this->client($stripeSecret)->customers->delete($id);
    }

    // -------------------------------------------------------------------------
    // Métodos de pagamento
    // -------------------------------------------------------------------------

    /**
     * Retorna os métodos de pagamento de um cliente.
     *
     * @return array<int, array{id: string, card: \Stripe\StripeObject}>
     *
     * @throws ClienteNaoEncontradoException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function listarMetodosPagamento(string $customerId, ?string $stripeSecret = null): array
    {
        $resultado = $this->client($stripeSecret)->paymentMethods->all([
            'customer' => $customerId,
        ]);

        return array_map(
            fn (PaymentMethod $item) => [
                'id' => $item->id,
                'card' => $item->card,
            ],
            $resultado->data
        );
    }

    /**
     * Cria e vincula um método de pagamento ao cliente a partir de um token.
     *
     * @throws ClienteNaoEncontradoException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function criarMetodoPagamento(string $customerId, string $token, ?string $stripeSecret = null): PaymentMethod
    {
        $paymentMethod = $this->client($stripeSecret)->paymentMethods->create([
            'type' => 'card',
            'card' => ['token' => $token],
        ]);

        $this->vincularMetodoPagamentoAoCliente($paymentMethod->id, $customerId, $stripeSecret);

        return $paymentMethod;
    }

    /**
     * Vincula um PaymentMethod já existente (criado via Stripe Elements) ao cliente.
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function attachPaymentMethod(string $paymentMethodId, string $customerId, ?string $stripeSecret = null): PaymentMethod
    {
        $pm = $this->client($stripeSecret)->paymentMethods->retrieve($paymentMethodId);
        if (empty($pm->customer)) {
            $this->vincularMetodoPagamentoAoCliente($paymentMethodId, $customerId, $stripeSecret);
            $pm = $this->client($stripeSecret)->paymentMethods->retrieve($paymentMethodId);
        }

        return $pm;
    }

    /**
     * Desvincula (remove) um método de pagamento.
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function deletarMetodoPagamento(string $paymentMethodId, ?string $stripeSecret = null): void
    {
        $this->client($stripeSecret)->paymentMethods->detach($paymentMethodId);
    }

    // -------------------------------------------------------------------------
    // Cobranças
    // -------------------------------------------------------------------------

    /**
     * Cria uma cobrança imediata via PaymentIntent.
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function criarCobranca(string $paymentMethodId, float $valorTotal, string $customerId, ?string $stripeSecret = null, string $currency = 'brl'): PaymentIntent
    {
        $valorEmCentavos = intval($valorTotal * 100);

        return $this->client($stripeSecret)->paymentIntents->create([
            'amount' => $valorEmCentavos,
            'currency' => strtolower($currency),
            'payment_method' => $paymentMethodId,
            'customer' => $customerId,
            'confirm' => true,
            'return_url' => config('services.stripe.return_url'),
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);
    }

    /**
     * Recupera um PaymentIntent pelo ID.
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function buscarCobranca(string $id, ?string $stripeSecret = null): PaymentIntent
    {
        return $this->client($stripeSecret)->paymentIntents->retrieve($id);
    }

    /**
     * Alias para compatibilidade legada.
     *
     * @return array<int, array{id: string, card: \Stripe\StripeObject}>
     */
    public function getPaymentMethods(string $customerId, ?string $stripeSecret = null): array
    {
        return $this->listarMetodosPagamento($customerId, $stripeSecret);
    }

    /**
     * Alias para compatibilidade legada.
     */
    public function createCharge(string $paymentMethodId, float $valorTotal, string $customerId, ?string $stripeSecret = null, string $currency = 'brl'): PaymentIntent
    {
        return $this->criarCobranca($paymentMethodId, $valorTotal, $customerId, $stripeSecret, $currency);
    }

    /**
     * Garante que exista um cliente válido no Stripe para os dados fornecidos.
     *
     * - Se o `stripeId` atual existir no Stripe, retorna-o.
     * - Caso não exista (InvalidRequestException), faz uma busca por e-mail;
     *   se encontrar, retorna o id encontrado.
     * - Se não encontrar, cria um novo cliente e retorna o id.
     *
     * Não atualiza o usuário local — o chamador deve persistir `stripe_id` no banco.
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function garantirClienteValido(?string $stripeId, string $email, string $nome, ?string $stripeSecret = null): string
    {
        // Se já tem um stripe id, tenta recuperar
        if (! empty($stripeId)) {
            try {
                $this->client($stripeSecret)->customers->retrieve($stripeId);

                return (string) $stripeId;
            } catch (InvalidRequestException $e) {
                // Cliente nao encontrado no Stripe — prossegue para buscar por email/criar
            }
        }

        // Busca por email
        $resultado = $this->buscarClientePorEmail($email, $stripeSecret);
        if (! empty($resultado->data)) {
            return (string) ($resultado->data[0]->id ?? '');
        }

        // Cria novo cliente
        $cliente = $this->criarCliente($email, $nome, $stripeSecret);

        return (string) ($cliente->id ?? '');
    }

    // -------------------------------------------------------------------------
    // Tokens
    // -------------------------------------------------------------------------

    /**
     * Gera um token de cartão no Stripe (útil em testes/ambientes server-side).
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function criarTokenCartao(string $numeroCartao, string $mes, string $ano, string $cvv, ?string $stripeSecret = null): Token
    {
        return $this->client($stripeSecret)->tokens->create([
            'card' => [
                'number' => $numeroCartao,
                'exp_month' => $mes,
                'exp_year' => $ano,
                'cvc' => $cvv,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Métodos privados
    // -------------------------------------------------------------------------

    /**
     * Vincula um método de pagamento a um cliente existente no Stripe.
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function vincularMetodoPagamentoAoCliente(string $paymentMethodId, string $customerId, ?string $stripeSecret = null): void
    {
        $this->client($stripeSecret)->paymentMethods->attach(
            $paymentMethodId,
            ['customer' => $customerId]
        );
    }
}
