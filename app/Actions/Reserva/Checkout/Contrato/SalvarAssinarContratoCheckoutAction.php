<?php

namespace App\Actions\Reserva\Checkout\Contrato;

use Illuminate\Http\Request;
use App\Services\Reserva\ReservaResolver;
use App\Http\Controllers\Reserva\ReservaHelpersTrait;
use App\Services\ReservaService;
use App\Services\VeiculoService;
use App\Services\ModeloService;
use App\Services\UpgradeVeiculoService;

class SalvarAssinarContratoCheckoutAction
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected VeiculoService $veiculoService,
        protected ModeloService $modeloService,
        protected UpgradeVeiculoService $upgradeVeiculoService
    ) {}

    public function handle(Request $request, $codigo, ReservaResolver $reservaResolver)
    {
        $modo = $request->input('modo', 'digital');

        if ($modo === 'upload') {
            $request->validate([
                'contrato_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240']
            ]);
        } else {
            $request->validate([
                'assinatura' => ['required', 'string']
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
                    $caminhoArquivo = $this->salvarArquivo($file, 'checkout', 'reserva_app_' . $reg->id . '_' . time());
                    
                    $checkout['pdf_path'] = $caminhoArquivo;
                    $checkout['assinatura'] = 'PDF anexado';
                } else {
                    $caminhoAssinatura = $this->salvarArquivo(
                        $request->input('assinatura'), 
                        'checkout/motorista_app_' . (int) $reg->id, 
                        'assinatura_' . \Illuminate\Support\Str::random(10)
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
            if (!$co) {
                return response()->json(['error' => 'Check-out não registrado.'], 404);
            }

            if ($modo === 'upload') {
                $file = $request->file('contrato_pdf');
                $caminhoArquivo = $this->salvarArquivo($file, 'checkout', 'reserva_' . $reserva->id . '_' . time());

                $co->update([
                    'pdf_path' => $caminhoArquivo,
                    'assinatura' => 'PDF anexado',
                    'data_assinatura' => now(),
                ]);
            } else {
                $base64 = $request->input('assinatura');
                $caminhoAssinatura = $this->salvarArquivo(
                    $base64, 
                    'checkout/reserva_' . (int) $reserva->id, 
                    'assinatura_' . \Illuminate\Support\Str::random(10)
                );

                if ($caminhoAssinatura) {
                    $co->update([
                        'assinatura' => $caminhoAssinatura,
                        'data_assinatura' => now(),
                    ]);
                } else {
                    $co->update([
                        'assinatura' => $base64,
                        'data_assinatura' => now(),
                    ]);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha ao salvar assinatura: ' . $e->getMessage()], 500);
        }
    }
}
