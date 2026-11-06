<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Gera o conteúdo PDF a partir de uma view e dados.
     */
    public function gerarConteudo(string $view, array $data = []): string
    {
        return Pdf::loadView($view, $data)->output();
    }

    /**
     * Resposta de download PDF (attachment) a partir de uma view.
     */
    public function download(string $view, array $data, string $filename, string $paper = 'a4', string $orientation = 'portrait')
    {
        return Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->download($filename);
    }
}
