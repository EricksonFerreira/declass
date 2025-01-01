<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;
class StripeController extends Controller
{

    public static function processaPagamento($numeroCartao, $dataVencimento, $cvv,$valorTotal,$parcelas){

        // $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));;


        // verifica se existe um cliente com o email
        $customer = \Stripe\Customer::search([
            'query' => 'email:\'cliente@exemplo.com\'',
        ]);
        dd($customer);
        if(isset($customer->data[0])){
            $customer = $customer->data[0];
        }else{
            $customer = \Stripe\Customer::create([
                'email' => 'cliente@exemplo.com',
                'name' => 'Nome do Cliente',
                'description' => 'Cliente de exemplo',
                'payment_method' => 'pm_card_visa',  // ID do método de pagamento (como cartão)
                'invoice_settings' => [
                    'default_payment_method' => 'pm_card_visa',
                ],
            ]);
        }
        dd($customer);


        dump($stripe);
        $token = $stripe->tokens->create([
            'card' => [
                'number' => $numeroCartao,
                'exp_month' => $dataVencimento,
                'exp_year' => $cvv,
                'cvc' => $valorTotal,
            ],
        ]);

        dd($token);
    }

    public function payment(Request $request){
        return view('stripe.payment');
    }
    public function processPayment(Request $request){

        // Salva o request em um arquivo JSON
        dd($request->all());
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        // $customer = $stripe->customers->create([
        //     'description' => 'example customer',
        //     'email' => 'email@example.com',
        //     'payment_method' => 'pm_card_visa',
        // ]);

        $email = $stripe->customers->search(['query' => 'email:\'email@example2.com\'']);
        if(isset($email->data[0])){
            $customer = $email->data[0];
        }else{
            // dd($stripe);
            $customer = $stripe->customers->create([
                'description' => 'example2 customer',
                'email' => 'email@example2.com',
                // 'payment_method' => 'pm_card_visa',
            ]);
        }


        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Verifica se o cliente já possui um método de pagamento
            if (!$customer->default_source) {
                // Adiciona um novo método de pagamento ao cliente
                $stripe->customers->update($customer->id, [
                    'source' => $request->stripeToken,
                ]);
            }
        }catch(\Exception $e){
            echo "Não foi possível adicionar o método de pagamento ao cliente".PHP_EOL;
            echo "$customer->id".PHP_EOL;
            echo $request->stripeToken.PHP_EOL;
            dd($e);
        }


        try{
            $amount = 51; // Valor total em centavos
            $installments = 3; // Número de parcelas
            $amountPerInstallment = $amount / $installments; // Valor de cada parcela

            // Obtém o método de pagamento do cliente
            $paymentMethod = $stripe->paymentMethods->retrieve($customer->default_source);

            // Verifica a moeda do cartão
            $currency = $paymentMethod->card->currency; // A moeda do cartão

            // Cria a cobrança com parcelamento
            $charge = Charge::create([
                'amount' => $amount, // Valor total em centavos
                'currency' => $currency,
                'source' => $customer->default_source,
                'description' => 'Reserva de Veículo - Parcelado em ' . $installments . 'x',
                'customer' => $customer->id,
                'metadata' => [
                    'installments' => $installments,
                    'amount_per_installment' => $amountPerInstallment,
                ],
                'payment_method_options' => [ // Adicionando opções de método de pagamento
                    'card' => [
                        'request_three_d_secure' => 'any', // Exemplo de configuração
                        'capture_method' => 'automatic', // Captura automática
                        'payment_method_types' => ['card'], // Tipos de método de pagamento
                        'payment_method' => $request->payment_method, // Método de pagamento enviado na requisição
                    ],
                ],
            ]);

            // Registra a transação
            \Log::info('Transação realizada com sucesso', [
                'customer_id' => $customer->id,
                'amount' => 10,
                'charge_id' => $charge->id,
            ]);

            // Payment successful; store a success message in the session
            $request->session()->flash('success', 'Payment successful!');
            dd($request->session()->get('success'));
            return redirect()->route('stripe.success')->with('success', 'Payment successful!');
        } catch (\Exception $e) {
            // Payment failed; store an error message in the session
            $request->session()->flash('error', $e->getMessage());
            dd($e);

            return redirect()->route('stripe.failure');
        }
    }

    public function failure(){
        return view('stripe.failure');
    }

    public function success2(){
        return view('stripe.success');
    }

    public function checkout(Request $request)
    {
        $user = $request->user();

        // Redirect user to a new Stripe Checkout Session
        return $user->checkout(
            'price_1QSUyhI8GjEr2tfYdxaPi6U7',
            // Replace the above Price ID with the one you created
            [
                'success_url' => route('success'),
                'cancel_url' => route('cancel'),
                'payment_method_types' => ['card'], // Adicionando tipos de método de pagamento
                'payment_method' => $user->defaultPaymentMethod()->id,
            ]
        );
    }

    public function success()
    {
        return 'Payment successful!';
    }

    public function cancel()
    {
        return 'Payment canceled!';
    }

    public function subscriptionCheckout(Request $request)
    {
        $user = $request->user();

        return $user->newSubscription('default', 'price_monthly')
            ->checkout([
                'success_url' => route('success'),
                'cancel_url' => route('cancel'),
            ]);
    }
}
