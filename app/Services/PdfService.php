<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    public function gerarConteudo(
        string $view,
        array $data,
        string $orientacao = 'portrait',
        string $papel = 'A4',
    ): string {
        $dompdf = $this->criarDompdf();

        $data = $this->normalizarDados($data);

        $html = view($view, [
            'data' => $data,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper($papel, $orientacao);
        $dompdf->setBasePath(public_path());
        $dompdf->render();

        return $dompdf->output();
    }

    public function gerarCheckinPdfContent(array $snapshot): string
    {
        return $this->gerarConteudo(
            view: 'pdf.contratos.checkin.motorista-app',
            data: $snapshot,
        );
    }

    public function exibir(
        string $view,
        array $data,
        string $filename = 'documento.pdf',
        string $orientacao = 'portrait',
        string $papel = 'A4',
    ) {
        $conteudo = $this->gerarConteudo(
            view: $view,
            data: $data,
            orientacao: $orientacao,
            papel: $papel,
        );

        return response($conteudo, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf(
                'inline; filename="%s"',
                $filename
            ),
        ]);
    }

    private function criarDompdf(): Dompdf
    {
        $options = new Options();

        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('dpi', 100);
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isJavascriptEnabled', false);

        return new Dompdf($options);
    }

    private function normalizarDados(array $data): array
    {
        array_walk_recursive($data, function (&$value): void {
            if (
                $value === '<br>'
                || $value === '<br />'
                || $value === null
                || $value === ''
            ) {
                $value = '-';
            }
        });

        return $data;
    }
}