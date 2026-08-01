<?php

namespace App\Actions\Reserva\Checkin\Contrato\Exibir;

use App\DTO\Reserva\ReservaResolvida;
use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class VerContratoCheckinMinhaReservaAction
{
    public function __construct(
        private readonly ExibirContratoCheckinAction $exibirContratoCheckinAction,
        private readonly ReservaResolver $reservaResolver,
    ) {}

    public function handle(
        Request $request,
        string $codigo
    ) {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        try {
            $reservaResolvida = $this->reservaResolver->resolver($codigo);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('reserva.minhas')
                ->with('error', 'Reserva não encontrada.');
        }
        $reserva = $reservaResolvida->registro;

        if (! $this->usuarioPodeVisualizar($reservaResolvida, $user)) {
            return redirect()
                ->route('reserva.minhas.show', [
                    'id' => $reservaResolvida->codigo,
                ])
                ->with(
                    'error',
                    'Você não tem permissão para visualizar este contrato.'
                );
        }

        try {
            $pdfPath = $this->obterPdfPath($reservaResolvida);

            /*
             * Caso exista um PDF salvo, ele sempre será utilizado.
             */
            if (filled($pdfPath)) {
                if (! Storage::disk('public')->exists($pdfPath)) {
                    throw new RuntimeException(
                        'O contrato está assinado, mas o arquivo PDF não foi encontrado.'
                    );
                }

                return $this->respostaPdf(
                    Storage::disk('public')->get($pdfPath),
                    $codigo
                );
            }

            /*
             * Contrato assinado sem pdf_path é uma inconsistência.
             * Não deve gerar novamente.
             */
            if ($this->estaAssinado($reservaResolvida)) {
                throw new RuntimeException(
                    'O contrato está assinado, mas não possui um PDF salvo.'
                );
            }

            /*
             * Caso ainda não esteja assinado, deve exibir 
             *  contrato não assinado.
             */
            $pdfContent = $this->exibirContratoCheckinAction->gerarPdf(
                $reserva
            );

            return $this->respostaPdf($pdfContent, $codigo);
              
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('reserva.minhas.show', [
                    'id' => $reservaResolvida->codigo,
                ])
                ->with('error', $e->getMessage());
        }
    }

    private function usuarioPodeVisualizar(
        ReservaResolvida $reservaResolvida,
        $user
    ): bool {
        $reserva = $reservaResolvida->registro;

        /*
         * Administrador pode visualizar.
         * Retire esta condição caso apenas o titular possa acessar.
         */
        if ($user->isAdministrador()) {
            return true;
        }

        $possiveisTitulares = [
            (int) ($reserva->user_id ?? 0),
            (int) ($reserva->usuario_id ?? 0),
            (int) ($reserva->criado_por_user_id ?? 0),
            (int) ($reserva->usuario?->id ?? 0),
        ];

        return in_array(
            (int) $user->id,
            array_filter($possiveisTitulares),
            true
        );
    }

    private function obterPdfPath(
        ReservaResolvida $reservaResolvida
    ): ?string {
        $reserva = $reservaResolvida->registro;

        if ($reservaResolvida->ehMotoristaApp()) {
            return data_get(
                $reserva->documentos_json,
                'checkin.pdf_path'
            ) ?: $reserva->contrato_pdf_path;
        }

        return $reserva->contratoCheckin?->pdf_path;
    }

    private function estaAssinado(
        ReservaResolvida $reservaResolvida
    ): bool {
        $reserva = $reservaResolvida->registro;

        if ($reservaResolvida->ehMotoristaApp()) {
            return (bool) data_get(
                $reserva->documentos_json,
                'checkin.assinado',
                false
            ) || filled($reserva->contrato_pdf_path);
        }

        return $reserva->contratoCheckin?->assinado === 'S';
    }

    private function respostaPdf(
        string $pdfContent,
        string $codigo
    ) {
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf(
                'inline; filename="contrato-checkin-%s.pdf"',
                $codigo
            ),
            'Content-Length' => strlen($pdfContent),
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}