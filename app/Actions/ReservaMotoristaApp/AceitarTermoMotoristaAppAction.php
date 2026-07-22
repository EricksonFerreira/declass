<?php

namespace App\Actions\ReservaMotoristaApp;

use App\Models\ReservaMotoristaApp;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AceitarTermoMotoristaAppAction
{
    public function __construct(
        private ReservaService $reservaService,
    ) {}

    public function handle(ReservaMotoristaApp $reserva, Request $request): ReservaMotoristaApp
    {
        // if (!$reserva->pagamento_efetuado) {
        //     throw new \RuntimeException('Confirme o pagamento antes de aceitar o termo da reserva.');
        // }

        // if (! empty($reserva->checkin_realizado_em)) {
        //     return $reserva;
        // }

        $empresaId = (int) ($reserva->empresa_id ?? 0);
        $termoConsentimento = null;
        if ($empresaId > 0) {
            $termoConsentimento = \App\Models\TermoConsentimento::query()
                ->where('empresa_id', $empresaId)
                ->where(function ($query) {
                    $query->whereNull('ativo')->orWhere('ativo', 'S');
                })
                ->where('tipo', 'aceito-motorista-app')
                ->orderBy('ordem')
                ->orderBy('id')
                ->first();
        }

        $documentos = (array) ($reserva->documentos_json ?? []);
        data_set($documentos, 'termo_confirmacao.aceito', true);
        data_set($documentos, 'termo_confirmacao.aceito_em', now()->format('Y-m-d H:i:s'));
        data_set($documentos, 'termo_confirmacao.aceito_por_user_id', Auth::id());
        data_set($documentos, 'termo_confirmacao.ip', $request->ip());
        data_set($documentos, 'termo_confirmacao.user_agent', substr((string) $request->userAgent(), 0, 500));
        data_set($documentos, 'termo_confirmacao.titulo', $termoConsentimento?->titulo ?? 'Termo de confirmação da reserva');
        data_set($documentos, 'termo_confirmacao.texto', $termoConsentimento?->texto ?? 'Este termo confirma a reserva e autoriza a preparação posterior do contrato de locação no check-in.');

        $reserva->update([
            'status' => 'confirmada',
            'documentos_json' => $documentos,
        ]);

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao' => 'Termo de confirmacao da reserva motorista app aceito',
            'data' => now()->format('d/m/Y H:i'),
        ]));

        return $reserva;
    }
}
