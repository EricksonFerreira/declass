<?php

namespace App\Actions\Reserva\Checkout\Contrato;

use App\Actions\Reserva\Checkout\TermoDevolucao\Assinar\AssinarTermoDevolucaoAction;
use App\Http\Controllers\Reserva\ReservaHelpersTrait;
use App\Services\ModeloService;
use App\Services\Reserva\ReservaResolver;
use App\Services\ReservaService;
use App\Services\UpgradeVeiculoService;
use App\Services\VeiculoService;
use Illuminate\Http\Request;

class SalvarAssinarContratoCheckoutAction
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected VeiculoService $veiculoService,
        protected ModeloService $modeloService,
        protected UpgradeVeiculoService $upgradeVeiculoService,
        protected AssinarTermoDevolucaoAction $assinarTermoDevolucaoAction
    ) {}

    public function handle(Request $request, $codigo, ReservaResolver $reservaResolver)
    {
        $modo = $request->input('modo', 'digital');

        if ($modo === 'upload') {
            $request->validate([
                'contrato_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            ]);
        } else {
            $request->validate([
                'assinatura' => ['required', 'string'],
            ]);
        }

        try {
            $reservaResolvida = $reservaResolver->resolver($codigo);

            if ($reservaResolvida->ehMotoristaApp()) {
                /** @var \App\Models\ReservaMotoristaApp $reg */
                $reg = $reservaResolvida->registro;
                $docs = is_array($reg->documentos_json) ? $reg->documentos_json : [];
                $checkout = isset($docs['checkout']) && is_array($docs['checkout']) ? $docs['checkout'] : [];

                if ($modo === 'upload') {
                    $file = $request->file('contrato_pdf');
                    $caminhoArquivo = $this->salvarArquivo($file, 'checkout', 'reserva_app_'.$reg->id.'_'.time());

                    $checkout['pdf_path'] = $caminhoArquivo;
                    $checkout['assinatura'] = 'PDF anexado';
                } else {
                    $caminhoAssinatura = $this->salvarArquivo(
                        $request->input('assinatura'),
                        'checkout/motorista_app_'.(int) $reg->id,
                        'assinatura_'.\Illuminate\Support\Str::random(10)
                    );
                    $checkout['assinatura'] = $caminhoAssinatura ?? $request->input('assinatura');
                }

                $checkout['data_assinatura'] = now()->toDateTimeString();

                $docs['checkout'] = $checkout;
                $reg->documentos_json = $docs;
                $reg->save();

                return response()->json(['success' => true]);
            }

            /** @var \App\Models\Reserva $reserva */
            $reserva = $reservaResolvida->registro;
            $co = $reserva->contratoCheckout;

            if (! $co) {
                return response()->json(['error' => 'Check-out nÃƒÆ’Ã‚Â£o registrado.'], 404);
            }

            if ($modo === 'upload') {
                $file = $request->file('contrato_pdf');
                $caminhoArquivo = $this->salvarArquivo($file, 'checkout', 'reserva_'.$reserva->id.'_'.time());

                $co->update([
                    'pdf_path' => $caminhoArquivo,
                    'assinatura' => 'PDF anexado',
                    'data_assinatura' => now(),
                ]);

                $this->reservaService->adicionaLog(
                    (int) $reserva->id,
                    (int) auth()->id(),
                    [
                        'acao' => 'Termo de devoluÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o assinado',
                        'contrato_checkout_id' => $co->id,
                        'pdf_path' => $caminhoArquivo,
                        'data_assinatura' => optional($co->data_assinatura)->toDateTimeString(),
                        'signatario_nome' => $co->signatario_nome,
                    ],
                    'Termo de devoluÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o assinado',
                    'success'
                );

                return response()->json(['success' => true]);
            }

            $this->assinarTermoDevolucaoAction->handle($codigo, $request->input('assinatura'));

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao salvar assinatura de checkout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'codigo' => $codigo,
            ]);

            return response()->json(['error' => 'Falha ao salvar assinatura: '.$e->getMessage()], 500);
        }
    }
}
