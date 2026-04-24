<?php

namespace App\Services;

use App\Models\EmpresaPlanoPagamento;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;

class StripePixService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Cria um PaymentIntent PIX no Stripe e salva os dados do QR Code no pagamento.
     */
    public function criarIntentPix(EmpresaPlanoPagamento $pagamento): EmpresaPlanoPagamento
    {
        $empresa = $pagamento->empresa;

        // Cria ou recupera o customer Stripe da empresa
        $customerId = $empresa->gateway_customer_id ?? null;

        if (!$customerId) {
            // Usa email do usuário admin da empresa se o email da empresa for inválido
            $emailEmpresa = filter_var($empresa->email ?? '', FILTER_VALIDATE_EMAIL)
                ? $empresa->email
                : null;

            $emailUsuario = $empresa->users()
                ->whereNotNull('email')
                ->get()
                ->first(fn ($u) => filter_var($u->email, FILTER_VALIDATE_EMAIL))
                ?->email;

            $emailFinal = $emailEmpresa ?? $emailUsuario ?? null;

            $customerData = [
                'name'     => $empresa->nome,
                'metadata' => ['empresa_id' => $empresa->id],
            ];

            if ($emailFinal) {
                $customerData['email'] = $emailFinal;
            }

            $customer = Customer::create($customerData);
            $customerId = $customer->id;
            $empresa->update(['gateway_customer_id' => $customerId]);
        }

        // Converte valor para centavos (Stripe usa a menor unidade)
        $valorCentavos = (int) round((float) $pagamento->valor * 100);

        $intent = PaymentIntent::create([
            'amount'               => $valorCentavos,
            'currency'             => 'brl',
            'payment_method_types' => ['pix'],
            'customer'             => $customerId,
            'description'          => 'Plano ' . ($pagamento->empresaPlano?->plano?->nome ?? 'DeCars'),
            'metadata'             => [
                'pagamento_id'  => $pagamento->id,
                'empresa_id'    => $pagamento->empresa_id,
                'referencia'    => $pagamento->referencia_interna ?? '',
            ],
            'payment_method_options' => [
                'pix' => [
                    'expires_after_seconds' => 86400, // 24 horas
                ],
            ],
        ]);

        $pixData = $intent->next_action?->pix_display_qr_code ?? null;

        $pagamento->update([
            'stripe_payment_intent_id' => $intent->id,
            'pix_qr_code_image'        => $pixData?->image_url_png ?? null,
            'pix_qr_code_text'         => $pixData?->data ?? null,
            'pix_expira_em'            => $pixData?->expires_at
                                            ? \Carbon\Carbon::createFromTimestamp($pixData->expires_at)
                                            : now()->addHours(24),
        ]);

        return $pagamento->fresh();
    }

    /**
     * Verifica o status atual do PaymentIntent no Stripe.
     */
    public function verificarStatus(EmpresaPlanoPagamento $pagamento): string
    {
        if (!$pagamento->stripe_payment_intent_id) {
            return 'sem_intent';
        }

        $intent = PaymentIntent::retrieve($pagamento->stripe_payment_intent_id);

        return $intent->status; // requires_payment_method | requires_action | processing | succeeded | canceled
    }
}
