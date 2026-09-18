<?php

namespace App\Console\Commands;

use App\Models\ClockifyTimeOff;
use App\Models\ClockifyTimeTracker;
use App\Models\User;
use App\Notifications\AvisoSistemaNotification;
use Illuminate\Console\Command;

class LembreteHorasCommand extends Command
{
    protected $signature = 'clockify:lembrete-horas';

    protected $description = 'Notifica colaboradores que ainda não lançaram horas hoje';

    public function handle(): int
    {
        $hoje = today();

        if ($hoje->isWeekend()) {
            $this->info('Fim de semana — lembrete ignorado.');

            return self::SUCCESS;
        }

        $notificados = 0;

        $chave = 'lembrete-horas-'.$hoje->toDateString();

        User::whereNotNull('empresa_id')
            ->where('tipo_usuario_id', 8)
            ->where('ativo', 1)
            ->chunkById(100, function ($users) use ($hoje, $chave, &$notificados) {
                foreach ($users as $user) {
                    $jaNotificado = $user->notifications()
                        ->where('data->chave', $chave)
                        ->exists();

                    if ($jaNotificado) {
                        continue;
                    }

                    $lancou = ClockifyTimeTracker::withoutGlobalScopes()
                        ->where('user_id', $user->id)
                        ->whereDate('inicio', $hoje)
                        ->exists();

                    if ($lancou) {
                        continue;
                    }

                    $deFolga = ClockifyTimeOff::withoutGlobalScopes()
                        ->where('user_id', $user->id)
                        ->whereIn('status', ['aprovado', 'Aprovado'])
                        ->whereDate('data_inicio', '<=', $hoje)
                        ->whereDate('data_fim', '>=', $hoje)
                        ->exists();

                    if ($deFolga) {
                        continue;
                    }

                    $user->notify(new AvisoSistemaNotification(
                        titulo: 'Horas não lançadas',
                        mensagem: "Você ainda não lançou horas hoje ({$hoje->format('d/m/Y')}). Abra o rastreador para registrar.",
                        url: route('clockify.tracker.index'),
                        icone: 'fas fa-clock',
                        cor: 'warning',
                        chave: $chave,
                        categoria: 'clockify',
                    ));

                    $notificados++;
                }
            });

        $this->info("{$notificados} colaborador(es) notificado(s).");

        return self::SUCCESS;
    }
}
