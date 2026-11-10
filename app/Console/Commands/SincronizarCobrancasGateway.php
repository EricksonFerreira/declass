<?php

namespace App\Console\Commands;

use App\Models\Mensalidade;
use App\Services\Pagamentos\MensalidadeCobrancaService;
use Illuminate\Console\Command;

/**
 * Sincroniza mensalidades pendentes que ja tem cobranca no gateway:
 * da baixa nas recebidas e marca as vencidas. Roda a cada hora no schedule.
 */
class SincronizarCobrancasGateway extends Command
{
    protected $signature = 'cobrancas:sincronizar {--empresa= : Restringe a um tenant}';
    protected $description = 'Sincroniza o status das cobranças de mensalidades com o gateway de pagamento';

    public function handle(MensalidadeCobrancaService $service): int
    {
        $query = Mensalidade::withoutGlobalScopes()
            ->where('status', 'pendente')
            ->whereNotNull('gateway_payment_id')
            ->where(function ($q) {
                $q->whereNull('gateway_synced_at')
                    ->orWhere('gateway_synced_at', '<', now()->subMinutes(30));
            })
            ->when($this->option('empresa'), fn ($q) => $q->where('empresa_id', (int) $this->option('empresa')));

        $baixadas = 0;
        $vencidas = 0;
        $falhas = 0;

        foreach ($query->cursor() as $mensalidade) {
            try {
                $status = $service->sincronizar($mensalidade);
                $status === 'recebida' ? $baixadas++ : ($status === 'vencida' ? $vencidas++ : null);
            } catch (\Throwable $e) {
                $falhas++;
                $this->warn("Mensalidade {$mensalidade->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sincronização concluída: {$baixadas} baixadas, {$vencidas} vencidas, {$falhas} falhas.");

        return self::SUCCESS;
    }
}
