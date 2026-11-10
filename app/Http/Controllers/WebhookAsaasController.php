<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Mensalidade;
use App\Services\Pagamentos\AsaasGateway;
use App\Services\Pagamentos\MensalidadeCobrancaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Recebe eventos do Asaas (PAYMENT_RECEIVED, PAYMENT_CONFIRMED,
 * PAYMENT_OVERDUE, PAYMENT_DELETED) e aplica na mensalidade.
 * URL publica protegida pelo token da empresa: ?token={gateway_webhook_token}
 */
class WebhookAsaasController extends Controller
{
    public function handle(Request $request, MensalidadeCobrancaService $cobranca)
    {
        $empresa = Empresa::where('gateway_webhook_token', $request->query('token'))
            ->whereNotNull('gateway_webhook_token')
            ->first();

        abort_unless($empresa, 403, 'Token de webhook inválido.');

        $evento = $request->input('event');
        $payment = $request->input('payment', []);
        $ref = $payment['externalReference'] ?? '';

        if (! str_starts_with($ref, 'mensalidade:')) {
            return response()->json(['ignorado' => 'referência desconhecida']);
        }

        $mensalidade = Mensalidade::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id)
            ->where('gateway_payment_id', $payment['id'] ?? '')
            ->first()
            ?? Mensalidade::withoutGlobalScopes()
                ->where('empresa_id', $empresa->id)
                ->find((int) str_replace('mensalidade:', '', $ref));

        if (! $mensalidade) {
            return response()->json(['ignorado' => 'mensalidade não encontrada']);
        }

        $status = AsaasGateway::traduzStatus($payment['status'] ?? null);
        $mensalidade->update([
            'gateway_status' => $status,
            'gateway_synced_at' => now(),
        ]);

        match ($evento) {
            'PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED' => $this->recebida($mensalidade, $cobranca, $payment),
            'PAYMENT_DELETED' => $mensalidade->update(['gateway_status' => 'cancelada']),
            default => null,
        };

        Log::info('webhook.asaas', ['evento' => $evento, 'mensalidade' => $mensalidade->id]);

        return response()->json(['ok' => true]);
    }

    private function recebida(Mensalidade $mensalidade, MensalidadeCobrancaService $cobranca, array $payment): void
    {
        if ($mensalidade->status === 'pendente') {
            $cobranca->baixar($mensalidade, $payment['paymentDate'] ?? $payment['clientPaymentDate'] ?? null);
        }
    }
}
