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
}
