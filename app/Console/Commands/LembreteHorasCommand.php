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

    protected $description = 'Notifica colaboradores que ainda não completaram a jornada de horas hoje';

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

                    $segundosLancados = ClockifyTimeTracker::withoutGlobalScopes()
                        ->where('user_id', $user->id)
                        ->whereDate('inicio', $hoje)
                        ->selectRaw('SUM(GREATEST(TIMESTAMPDIFF(SECOND, inicio, COALESCE(fim, NOW())), 0)) as segundos')
                        ->value('segundos') ?? 0;

                    $jornada = (float) ($user->jornada_diaria ?? 8.0);
                    $horasLancadas = round(((float) $segundosLancados) / 3600, 2);

                    if ($jornada <= 0 || $horasLancadas >= $jornada) {
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

                    $faltam = round($jornada - $horasLancadas, 2);
                    $resumo = $horasLancadas > 0
                        ? "Você lançou {$horasLancadas}h de {$jornada}h hoje — faltam {$faltam}h."
                        : "Você ainda não lançou horas hoje (sua jornada é de {$jornada}h).";

                    $user->notify(new AvisoSistemaNotification(
                        titulo: 'Horas faltantes',
                        mensagem: "{$resumo} ({$hoje->format('d/m/Y')}). Abra o rastreador para registrar.",
                        url: route('operacao.tracker.index'),
                        icone: 'fas fa-clock',
                        cor: 'warning',
                        chave: $chave,
                        categoria: 'operacao',
                    ));

                    $notificados++;
                }
            });

        $this->info("{$notificados} colaborador(es) notificado(s).");

        return self::SUCCESS;
    }
}
