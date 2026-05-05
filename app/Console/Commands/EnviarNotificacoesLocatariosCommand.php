<?php

namespace App\Console\Commands;

use App\Models\Multa;
use App\Models\Reserva;
use App\Models\User;
use App\Notifications\AvisoSistemaNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;

class EnviarNotificacoesLocatariosCommand extends Command
{
    protected $signature = 'notificacoes:locatarios-enviar {--data= : Data base no formato Y-m-d}';

    protected $description = 'Gera notificações para locatários sobre multas e vencimentos.';

    public function handle(): int
    {
        if (!SchemaCache::hasTable('notifications')) {
            $this->warn('Tabela de notificações não encontrada.');
            return self::SUCCESS;
        }

        $dataBase = $this->option('data')
            ? Carbon::parse((string) $this->option('data'))->startOfDay()
            : Carbon::now()->startOfDay();

        $this->gerarNotificacoesMultas($dataBase);
        $this->gerarNotificacoesReservas($dataBase);

        $this->info('Notificações de locatários geradas com sucesso.');

        return self::SUCCESS;
    }

    private function gerarNotificacoesMultas(Carbon $dataBase): void
    {
        if (!SchemaCache::hasTable('multas')) {
            return;
        }

        $multas = Multa::query()
            ->with(['reserva.usuario'])
            ->whereNotNull('data_vencimento')
            ->whereNull('pago_em')
            ->whereDate('data_vencimento', '<=', $dataBase->copy()->addDays(3)->toDateString())
            ->whereDate('data_vencimento', '>=', $dataBase->copy()->subDay()->toDateString())
            ->get();

        foreach ($multas as $multa) {
            $user = $multa->reserva?->usuario;
            if (!$user instanceof User) {
                continue;
            }

            $chave = 'multa:' . (int) $multa->id . ':vencimento:' . $multa->data_vencimento?->format('Y-m-d');
            if ($this->jaNotificada($user, AvisoSistemaNotification::class, $chave)) {
                continue;
            }

            $dias = $multa->data_vencimento
                ? max(0, $dataBase->diffInDays(Carbon::parse($multa->data_vencimento), false))
                : 0;

            $titulo = $dias <= 0 ? 'Multa vencida' : 'Multa com vencimento próximo';
            $mensagem = sprintf(
                'A multa #%d da reserva #%d vence em %s. Valor: %s.',
                (int) $multa->id,
                (int) $multa->reserva_id,
                $multa->data_vencimento?->format('d/m/Y') ?? 'sem data',
                number_format((float) ($multa->valor_final ?? $multa->valor_original ?? 0), 2, ',', '.')
            );

            $user->notify(new AvisoSistemaNotification(
                $titulo,
                $mensagem,
                route('site.multa.pagamento', ['multa' => $multa->id]),
                'fas fa-exclamation-triangle',
                $dias <= 0 ? 'danger' : 'warning',
                $chave,
                'multa_vencimento'
            ));
        }
    }

    private function gerarNotificacoesReservas(Carbon $dataBase): void
    {
        if (!SchemaCache::hasTable('reservas')) {
            return;
        }

        $reservas = Reserva::query()
            ->with('usuario')
            ->whereNull('data_cancelamento')
            ->whereDate('data_fim', '>=', $dataBase->copy()->toDateString())
            ->whereDate('data_fim', '<=', $dataBase->copy()->addDay()->toDateString())
            ->get();

        foreach ($reservas as $reserva) {
            $user = $reserva->usuario;
            if (!$user instanceof User) {
                continue;
            }

            $chave = 'reserva:' . (int) $reserva->id . ':vencimento:' . Carbon::parse($reserva->data_fim)->format('Y-m-d');
            if ($this->jaNotificada($user, AvisoSistemaNotification::class, $chave)) {
                continue;
            }

            $titulo = 'Reserva com devolução próxima';
            $mensagem = sprintf(
                'Sua reserva #%d termina em %s. Verifique a data de devolução.',
                (int) $reserva->id,
                Carbon::parse($reserva->data_fim)->format('d/m/Y')
            );

            $user->notify(new AvisoSistemaNotification(
                $titulo,
                $mensagem,
                route('site.reserva.index'),
                'fas fa-calendar-check',
                'info',
                $chave,
                'reserva_vencimento'
            ));
        }
    }

    private function jaNotificada(User $user, string $notificationClass, string $chave): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('type', $notificationClass)
            ->where('data->chave', $chave)
            ->exists();
    }
}
