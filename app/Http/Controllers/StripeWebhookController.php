<?php

namespace App\Http\Controllers;

use App\Models\EmpresaPlanoPagamento;
use App\Services\EmpresaPlanoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(private EmpresaPlanoService $planoService) {}

    public function handle(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            return response('Assinatura inválida.', 400);
        } catch (\Exception $e) {
            return response('Erro ao processar evento.', 400);
        }

        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePixPago($event->data->object),
            'payment_intent.payment_failed',
            'payment_intent.canceled'  => $this->handlePixFalhou($event->data->object),
            default                    => null,
        };

        return response('OK', 200);
    }

    private function handlePixPago(object $intent): void
    {
        $pagamento = EmpresaPlanoPagamento::where('stripe_payment_intent_id', $intent->id)
            ->where('status', 'pendente')
            ->first();

        if (!$pagamento) return;

        $this->planoService->ativarAposConfirmacao($pagamento);

        $pagamento->update([
            'gateway_payment_id' => $intent->id,
            'metodo'             => 'pix',
        ]);
    }

    private function handlePixFalhou(object $intent): void
    {
        EmpresaPlanoPagamento::where('stripe_payment_intent_id', $intent->id)
            ->where('status', 'pendente')
            ->update(['status' => 'cancelado']);
    }
}
