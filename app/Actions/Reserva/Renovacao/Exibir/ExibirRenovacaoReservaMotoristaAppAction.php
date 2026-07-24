<?php

namespace App\Actions\Reserva\Renovacao\Exibir;

use App\DTO\Reserva\ReservaResolvida;
use App\Models\PlanoMotoristaApp;
use App\Models\ReservaMotoristaApp;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ExibirRenovacaoReservaMotoristaAppAction
{
    public function handle(ReservaResolvida $reservaResolvida): View
    {
        /** @var ReservaMotoristaApp $registro */
        $registro = $reservaResolvida->registro;

        $this->validarAcesso($registro);
        $this->validarPossibilidadeRenovacao($registro);

        $registro->load([
            'empresa',
            'user',
            'codigoReserva',
            'veiculo.marca',
            'veiculo.modelo',
            'planoMotoristaApp.opcionais',
            'pagamentos' => fn ($query) => $query->latest('id'),
            'contratoCheckin',
        ]);

        $planos = PlanoMotoristaApp::query()
            ->with([
                'opcionais',
                'gruposFrota',
            ])
            ->where('empresa_id', $registro->empresa_id)
            ->where('ativo', 'S')
            ->orderBy('nome')
            ->get();

        $pagamentoRenovacaoPendente = $registro->pagamentos
            ->first(function ($pagamento) {
                return strtolower((string) $pagamento->status) === 'pendente'
                    && data_get($pagamento->metadados, 'tipo') === 'renovacao';
            });

        return view('reserva.renovacao.motorista-app', [
            'registro' => $registro,
            'empresa' => $registro->empresa,
            'codigo' => $reservaResolvida->codigo,
            'planos' => $planos,
            'planoAtual' => $registro->planoMotoristaApp,
            'pagamentoRenovacaoPendente' => $pagamentoRenovacaoPendente,
        ]);
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
}
