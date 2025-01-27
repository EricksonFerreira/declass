<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    protected $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('dpi', 100);
        $options->set('sans-serif');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isJavascriptEnabled', true);
        $this->dompdf = new Dompdf($options);
    }

    public function generatePdf($htmlContent, $filename = 'document.pdf')
    {

        $this->dompdf->loadHtml($htmlContent);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->setBasePath(public_path());
        $this->dompdf->render();
        $this->dompdf->stream($filename, ["Attachment" => false]);
    }
    public function gerarCheckinPdf($data)
    {
        $bootstrap5 = file_get_contents(public_path() . '/css/bootstrap.min.css');
        $data['css'] = $bootstrap5;
        $htmlContent = view('pdf.contrato-checkin', compact('data'))->render();
        return $this->generatePdf($htmlContent, 'contrato.pdf');

    }

    public function generateReportPdf($reportData)
    {
        $htmlContent = view('pdf.report', compact('reportData'))->render();
        return $this->generatePdf($htmlContent, 'report.pdf');
    }

}
