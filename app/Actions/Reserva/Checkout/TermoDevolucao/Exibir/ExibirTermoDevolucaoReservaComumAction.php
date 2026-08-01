<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Exibir;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction;
use App\Models\Reserva;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ExibirTermoDevolucaoReservaComumAction
{
    public function __construct(
        private PdfService $pdfService,
        private ExibirContratoCheckinReservaComumAction $exibirCheckinAction,
    ) {}

    public function servirPdf(Reserva $reserva): ?Response
    {
        $reserva->loadMissing([
            'contratoCheckout',
        ]);

        $checkout = $reserva->contratoCheckout;

        if (! $checkout) {
            return null;
        }

        /*
         * Se o checkout já foi assinado, deve sempre exibir
         * o PDF definitivo salvo.
         */
        if ($checkout->assinado === 'S') {
            if (blank($checkout->pdf_path)) {
                throw new RuntimeException(
                    'O termo de devolução está assinado, mas não possui um PDF salvo.'
                );
            }

            if (! Storage::disk('public')->exists($checkout->pdf_path)) {
                throw new RuntimeException(
                    'O termo de devolução está assinado, mas o arquivo PDF não foi encontrado.'
                );
            }

            $pdfContent = Storage::disk('public')->get(
                $checkout->pdf_path
            );

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf(
                    'inline; filename="termo-devolucao-%s.pdf"',
                    $reserva->id
                ),
                'Content-Length' => strlen($pdfContent),
                'Cache-Control' => 'private, no-store, max-age=0',
                'Pragma' => 'no-cache',
            ]);
        }

        /*
         * Enquanto não estiver assinado, gera somente uma
         * visualização dinâmica.
         */
        $pdfContent = $this->gerarPdf($reserva);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf(
                'inline; filename="termo-devolucao-%s.pdf"',
                $reserva->id
            ),
            'Content-Length' => strlen($pdfContent),
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function gerarPdf(Reserva $reserva): string
    {
        $reserva->loadMissing([
            'contratoCheckout.veiculo.marca',
            'contratoCheckout.veiculo.modelo',
            'contratoCheckout.veiculo.grupoFrota',
            'contratoCheckin.veiculo.marca',
            'contratoCheckin.veiculo.modelo',
            'contratoCheckin.veiculo.grupoFrota',
            'contratoCheckin.marca',
            'contratoCheckin.modelo',
            'usuario',
            'empresa',
            'veiculo',
            'grupoFrota',
            'estacaoRetirada',
            'estacaoDevolucao',
            'opcionais',
            'modelos',
            'statusReserva',
            'transacoes.user',
            'transacoes.pagoPor',
            'calcao.configuracao',
            'veiculoDanos.dano.categoria',
            'veiculoDanos.imagem',
            'veiculoDanos.usuarioCorrecao',
        ]);

        $checkout = $reserva->contratoCheckout;

        if (! $checkout) {
            throw new RuntimeException(
                'Check-out não encontrado para geração do termo de devolução.'
            );
        }

        /*
         * Usa o montador comum para aproveitar dados da empresa,
         * cliente, financeiro, veículo e demais informações.
         */
        $data = $this->exibirCheckinAction->montarDados(
            $reserva,
            'checkout'
        );

        /*
         * Sobrescreve os dados que pertencem especificamente
         * ao checkout.
         */
        data_set(
            $data,
            'documento.data_assinatura',
            $checkout->data_assinatura
                ? Carbon::parse($checkout->data_assinatura)
                    ->format('d/m/Y H:i')
                : null
        );

        data_set(
            $data,
            'contrato.titulo',
            'TERMO DE DEVOLUÇÃO DO VEÍCULO'
        );

        data_set(
            $data,
            'contrato.subtitulo',
            'VISTORIA E COMPROVANTE DE DEVOLUÇÃO'
        );

        /*
         * A assinatura usada aqui pode estar apenas em memória,
         * enviada pela FinalizarCheckoutReservaComumAction.
         */
        data_set(
            $data,
            'contrato.assinatura',
            $checkout->assinatura
        );

        data_set(
            $data,
            'assinatura',
            $checkout->assinatura
        );

        data_set(
            $data,
            'contrato.data_assinatura',
            $checkout->data_assinatura
                ? Carbon::parse($checkout->data_assinatura)
                    ->format('d/m/Y H:i')
                : null
        );

        data_set(
            $data,
            'veiculo.quilometros',
            $checkout->quilometragem_devolucao
        );

        data_set(
            $data,
            'veiculo.combustivel',
            $checkout->nivel_combustivel_devolucao
        );

        data_set(
            $data,
            'checkout',
            [
                'quilometragem_final' => $checkout->quilometragem_devolucao,

                'combustivel_final' => $checkout->nivel_combustivel_devolucao,

                'data_devolucao' => $checkout->data_devolucao
                    ? Carbon::parse($checkout->data_devolucao)
                        ->format('d/m/Y')
                    : null,

                'hora_devolucao' => $checkout->hora_devolucao
                    ? substr((string) $checkout->hora_devolucao, 0, 5)
                    : null,

                'data_hora_devolucao' => $this->formatarDataHoraDevolucao(
                    $checkout->data_devolucao,
                    $checkout->hora_devolucao
                ),

                'notas_devolucao' => $checkout->notas_devolucao,

                'observacoes_devolucao' => $checkout->observacoes_devolucao,

                'fotos' => $this->montarFotosCheckout($checkout),
            ]
        );

        /*
         * Mantém também esta chave para compatibilidade com
         * a Blade atual, caso ela use $checkout_fotos.
         */
        $data['checkout_fotos'] = [
            'Direita' => $checkout->foto_direita_path,
            'Esquerda' => $checkout->foto_esquerda_path,
            'Frente' => $checkout->foto_frente_path,
            'Trás' => $checkout->foto_tras_path,
            'Velocímetro' => $checkout->foto_velocimetro_path,
            'Combustível' => $checkout->foto_combustivel_path,
        ];

        return $this->pdfService->gerarConteudo(
            view: 'pdf.contratos.checkout.comum',
            data: $data,
        );
    }

    private function montarFotosCheckout($checkout): array
    {
        $fotos = [
            'Frente' => $checkout->foto_frente_path,
            'Trás' => $checkout->foto_tras_path,
            'Direita' => $checkout->foto_direita_path,
            'Esquerda' => $checkout->foto_esquerda_path,
            'Velocímetro' => $checkout->foto_velocimetro_path,
            'Combustível' => $checkout->foto_combustivel_path,
        ];

        return collect($fotos)
            ->map(function ($path, $label) {
                $path = trim((string) $path);

                if ($path === '') {
                    return null;
                }

                if (! Storage::disk('public')->exists($path)) {
                    return null;
                }

                $conteudo = Storage::disk('public')->get($path);

                if ($conteudo === '') {
                    return null;
                }

                $caminhoAbsoluto = Storage::disk('public')->path($path);
                $mime = is_file($caminhoAbsoluto)
                    ? mime_content_type($caminhoAbsoluto)
                    : null;

                if (! $mime) {
                    return null;
                }

                return [
                    'label' => $label,
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                    'base64' => 'data:'.$mime.';base64,'
                        .base64_encode($conteudo),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function formatarDataHoraDevolucao(
        $data,
        $hora
    ): ?string {
        if (! $data) {
            return null;
        }

        $dataFormatada = Carbon::parse($data)->format('d/m/Y');
        $horaFormatada = $hora
            ? substr((string) $hora, 0, 5)
            : null;

        return trim($dataFormatada.' '.$horaFormatada);
    }
}