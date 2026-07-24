<?php

namespace App\Actions\Reserva\Renovacao\Executa;

use App\Actions\ReservaMotoristaApp\RenovarMotoristaAppAction;
use App\Actions\Reserva\Renovacao\Executa\CriarAditivoRenovacaoMotoristaAppAction;
use App\DTO\Reserva\ReservaResolvida;
use App\Models\ReservaMotoristaApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ExecutaRenovacaoReservaMotoristaAppAction
{
    public function __construct(
        private readonly RenovarMotoristaAppAction $renovarMotoristaAppAction,
        private readonly CriarAditivoRenovacaoMotoristaAppAction $criarAditivoAction,
    ) {}

    public function handle(
        ReservaResolvida $reservaResolvida,
        Request $request
    ): RedirectResponse {
        /** @var ReservaMotoristaApp $registro */
        $registro = $reservaResolvida->registro;

        $registro->loadMissing([
            'codigoReserva',
            'planoMotoristaApp.opcionais',
            'contratoCheckin',
            'pagamentos',
        ]);

        $this->validarAcesso($registro);
        $this->validarPossibilidadeRenovacao($registro);

        /*
         * Mantém aqui temporariamente porque a RenovarMotoristaAppAction
         * atualmente recebe um Request.
         *
         * O ideal futuro é criar um RenovarMotoristaAppRequest e enviar
         * apenas $request->validated() para a action de domínio.
         */
        $request->validate([
            'periodos' => [
                'required',
                'integer',
                'min:1',
                'max:120',
            ],

            'acao_renovacao' => [
                'nullable',
                'string',
                'in:mantem,trocar',
            ],

            'plano_id' => [
                'nullable',
                'integer',
                'exists:planos_motorista_app,id',
            ],

            'cupom_codigo' => [
                'nullable',
                'string',
                'max:100',
            ],

            'opcionais' => [
                'nullable',
                'array',
            ],

            'opcionais.*' => [
                'integer',
            ],

            'observacoes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $this->validarTrocaDePlano($request);

        $pagamento = $this->renovarMotoristaAppAction->handle(
            $request,
            $registro
        );

        // Create the aditivo record linked to this payment
        $aditivo = $this->criarAditivoAction->handle(
            $registro,
            $pagamento,
            $request->all()
        );

        $codigo = $registro->codigoReserva?->codigo
            ?? $reservaResolvida->codigo
            ?? $this->gerarCodigoFallback($registro);

        return redirect()
            ->route('reserva.show', [
                'reserva' => $codigo,
            ])
            ->with(
                'success',
                'Renovação criada com sucesso! Efetue o pagamento para confirmar o novo período.'
            )
            ->with('pagamento', $pagamento->id)
            ->with('aditivo', $aditivo->id);
    }

    private function validarAcesso(ReservaMotoristaApp $reserva): void
    {
        $usuario = Auth::user();

        if (! $usuario) {
            throw new RuntimeException('Usuário não autenticado.');
        }

        $ehAdministrador = (bool) ($usuario->isAdministrador() ?? false);

        if (! $ehAdministrador && (int) $reserva->user_id !== (int) $usuario->id) {
            throw new RuntimeException(
                'Você não possui permissão para renovar esta reserva.'
            );
        }
    }

    private function validarPossibilidadeRenovacao(
        ReservaMotoristaApp $reserva
    ): void {
        if (strtolower((string) $reserva->status) === 'cancelada') {
            throw new RuntimeException(
                'Não é possível renovar uma reserva cancelada.'
            );
        }

        if (! empty($reserva->checkout_realizado_em)) {
            throw new RuntimeException(
                'Não é possível renovar o plano após o checkout da locação.'
            );
        }

        $possuiCheckin = ! empty($reserva->checkin_realizado_em)
            || $reserva->contratoCheckin()->exists();

        if (! $possuiCheckin) {
            throw new RuntimeException(
                'A renovação só fica disponível após o check-in.'
            );
        }

        $contratoCheckin = $reserva->relationLoaded('contratoCheckin')
            ? $reserva->contratoCheckin
            : $reserva->contratoCheckin()->latest('id')->first();

        $possuiAssinaturaSnapshot = ! empty(
            data_get($reserva->contrato_snapshot_json, 'assinatura')
        );

        $possuiContratoAssinado = $contratoCheckin
            && strtoupper((string) $contratoCheckin->assinado) === 'S';

        if (! $possuiAssinaturaSnapshot && ! $possuiContratoAssinado) {
            throw new RuntimeException(
                'A renovação só fica disponível após a assinatura do contrato.'
            );
        }
    }

    private function validarTrocaDePlano(Request $request): void
    {
        $acao = (string) $request->input('acao_renovacao', 'mantem');

        if ($acao === 'trocar' && ! $request->filled('plano_id')) {
            throw new RuntimeException(
                'Selecione o novo plano para realizar a troca.'
            );
        }
    }

    private function gerarCodigoFallback(
        ReservaMotoristaApp $reserva
    ): string {
        return 'MAP-'.str_pad(
            (string) $reserva->id,
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}
