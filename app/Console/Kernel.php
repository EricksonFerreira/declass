<?php

namespace App\Console;

use App\Console\Commands\SincronizarCatalogoFipeCommand;
use App\Console\Commands\EnviarNotificacoesLocatariosCommand;
use App\Console\Commands\ReplicacaoDiaria;
use App\Services\ReplicacaoBaseService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        ReplicacaoDiaria::class,
        SincronizarCatalogoFipeCommand::class,
        EnviarNotificacoesLocatariosCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Agendamento dinâmico baseado na configuração `replicacao.horarios_diarios`
        try {
            $service = $this->app->make(ReplicacaoBaseService::class);
            $horarios = $service->horariosDiarios();
            foreach ($horarios as $horario) {
                // dailyAt espera formato HH:MM
            $schedule->command('replicacao:diaria')->dailyAt($horario)->withoutOverlapping();
        }

        $schedule->command('fipe:sincronizar-catalogo --refresh-existing')->weeklyOn(0, '03:00')->withoutOverlapping();
        $schedule->command('notificacoes:locatarios-enviar')->dailyAt('08:00')->withoutOverlapping();
    } catch (\Throwable $e) {
            // Em caso de erro ao carregar serviço/config, não interrompe a aplicação.
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
