<?php

namespace App\Actions\Reserva\Checkin\Contrato\Assinar;

use App\DTO\BreadCrumbItem;
use App\Services\BreadcrumbService;
use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssinarCheckinMinhaReservaAction
{
    public function __construct(
        private readonly BreadcrumbService $breadcrumbService
    ) {}

    public function handle(Request $request, string $codigo, ReservaResolver $reservaResolver)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        Log::info('reserva.minhas.assinar-checkin: entrada', [
            'codigo_param' => $codigo,
            'user_id' => $user->id,
            'empresa_id' => $user->empresa_id ?? null,
        ]);

        try {
            $reservaResolvida = $reservaResolver->resolver($codigo);
        } catch (\Throwable $e) {
            Log::warning('reserva.minhas.assinar-checkin: erro ao resolver reserva', [
                'codigo_param' => $codigo,
                'user_id' => $user->id,
                'erro' => $e->getMessage(),
            ]);

            return redirect()
                ->route('reserva.minhas')
                ->with('error', 'Reserva não encontrada.');
        }

        Log::info('reserva.minhas.assinar-checkin: reserva resolvida', [
            'codigo_param' => $codigo,
            'codigo_resolvido' => $reservaResolvida->codigo ?? null,
            'tipo' => $reservaResolvida->tipo->value ?? null,
            'registro_id' => $reservaResolvida->registro->id ?? null,
        ]);

        if (! $reservaResolvida->ehComum()) {
            return redirect()
                ->route('reserva.minhas')
                ->with('error', 'Essa assinatura não está disponível para esta reserva.');
        }

        /** @var \App\Models\Reserva $reserva */
        $reserva = $reservaResolvida->registro;
        $reserva->loadMissing(['contratoCheckin', 'codigoReserva', 'empresa', 'usuario', 'veiculo.marca', 'veiculo.modelo', 'estacaoRetirada', 'estacaoDevolucao', 'calcao', 'calcao.configuracao']);

        $ehTitularDaReserva = in_array((int) $user->id, [
            (int) ($reserva->user_id ?? 0),
            (int) ($reserva->criado_por_user_id ?? 0),
            (int) ($reserva->usuario?->id ?? 0),
        ], true);

        if (! $ehTitularDaReserva) {
            Log::warning('reserva.minhas.assinar-checkin: acesso negado', [
                'codigo_param' => $codigo,
                'user_id' => $user->id,
                'registro_id' => $reserva->id ?? null,
            ]);

            return redirect()
                ->route('reserva.minhas.show', ['id' => $reserva->codigoReserva?->codigo ?? $reserva->id])
                ->with('error', 'Você não tem permissão para acessar esta assinatura.');
        }

        if (! $reserva->contratoCheckin) {
            return redirect()
                ->route('reserva.minhas.show', ['id' => $reserva->codigoReserva?->codigo ?? $reserva->id])
                ->with('error', 'O check-in ainda não foi criado para esta reserva.');
        }

        if (($reserva->contratoCheckin->assinado ?? '') === 'S') {
            return redirect()
                ->route('reserva.minhas.show', ['id' => $reserva->codigoReserva?->codigo ?? $reserva->id])
                ->with('info', 'O contrato de check-in já foi assinado.');
        }

        $calcaoRecord = $reserva->calcao;
        $valorCalculado = 0.0;
        if ($calcaoRecord) {
            $valorCalculado = (float) (
                $calcaoRecord->valor_capturado
                ?? $calcaoRecord->valor_autorizado
                ?? $calcaoRecord->valor_calculado
                ?? 0
            );
        } else {
            try {
                $calcaoService = app(\App\Services\CalcaoService::class);
                $calculo = $calcaoService->calcularValor($reserva);
                $valorCalculado = (float) ($calculo['valor_calculado'] ?? 0);
            } catch (\Throwable $e) {
                // fallback silencioso
            }
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Minhas Reservas', route('reserva.minhas')),
            new BreadCrumbItem('Minha Reserva Nº '.($reserva->codigoReserva?->codigo ?? $reserva->id), route('reserva.minhas.show', ['id' => $reserva->codigoReserva?->codigo ?? $reserva->id])),
            new BreadCrumbItem('Assinar Check-in'),
        ]);

        $contextoAssinatura = [
            'codigo' => $reserva->codigoReserva?->codigo ?? (string) $codigo,
            'tipo' => 'RES',
            'empresa' => $reserva->empresa,
            'usuario' => $reserva->usuario,
            'data_inicio' => $reserva->data_inicio,
            'data_fim' => $reserva->data_fim,
            'estacao_retirada' => $reserva->estacaoRetirada?->nome,
            'estacao_devolucao' => $reserva->estacaoDevolucao?->nome,
            'quantidade_dias' => $reserva->quantidade_dias,
            'tarifa_label' => 'Tarifa diaria',
            'tarifa_valor' => (float) ($reserva->tarifa_base ?? 0),
            'valor_total' => null,
            'taxa_entrega' => (float) ($reserva->taxa_entrega ?? 0),
            'taxa_coleta' => (float) ($reserva->taxa_coleta ?? 0),
            'plano_nome' => null,
            'caucao' => $valorCalculado,
        ];

        return view('reserva.assinar-contrato-checkin', [
            'reserva' => $reserva,
            'breadcrumb' => $breadcrumb,
            'contextoAssinatura' => $contextoAssinatura,
            'titulo' => 'Assinar check-in da minha reserva #'.$reserva->id,
            'redirectAfterSignUrl' => route('reserva.minhas.show', ['id' => $reserva->codigoReserva?->codigo ?? $reserva->id]),
            'origemMinhasReservas' => true,
        ]);
    }
}
