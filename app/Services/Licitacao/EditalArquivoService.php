<?php

namespace App\Services\Licitacao;

use App\Models\Licitacao;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class EditalArquivoService
{
    private const EXTENSOES_TEXTO = ['txt', 'md'];

    /**
     * Salva o arquivo do edital e extrai o texto para texto_edital.
     * Retorna a quantidade de caracteres extraídos.
     */
    public function importar(UploadedFile $arquivo, Licitacao $licitacao): int
    {
        $texto = $this->extrairTexto($arquivo);

        if ($licitacao->arquivo_path) {
            Storage::delete($licitacao->arquivo_path);
        }

        $path = $arquivo->storeAs(
            "licitacoes/{$licitacao->empresa_id}",
            "edital-{$licitacao->id}-".now()->format('YmdHis').'.'.$arquivo->getClientOriginalExtension()
        );

        $dados = ['arquivo_path' => $path];
        if (mb_strlen(trim($texto)) > 0) {
            $dados['texto_edital'] = mb_substr($texto, 0, 400000);
        }

        $licitacao->update($dados);

        return mb_strlen(trim($texto));
    }

    public function excluir(Licitacao $licitacao): void
    {
        if ($licitacao->arquivo_path) {
            Storage::delete($licitacao->arquivo_path);
            $licitacao->update(['arquivo_path' => null]);
        }
    }

    private function extrairTexto(UploadedFile $arquivo): string
    {
        $ext = strtolower($arquivo->getClientOriginalExtension());

        return match (true) {
            $ext === 'pdf' => $this->extrairPdf($arquivo->getRealPath()),
            $ext === 'docx' => $this->extrairDocx($arquivo->getRealPath()),
            in_array($ext, self::EXTENSOES_TEXTO) => (string) file_get_contents($arquivo->getRealPath()),
            default => throw new RuntimeException('Formato não suportado. Envie PDF, DOCX ou TXT.'),
        };
    }

    private function extrairPdf(string $path): string
    {
        try {
            $documento = (new PdfParser)->parseFile($path);
        } catch (\Throwable $e) {
            throw new RuntimeException('Não consegui ler o PDF. Se ele for digitalizado (imagem), cole o texto manualmente.');
        }

        return $documento->getText();
    }

    private function extrairDocx(string $path): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Servidor sem suporte a DOCX (ext-zip ausente). Envie PDF ou cole o texto.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Não consegui ler o DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException('DOCX inválido: document.xml não encontrado.');
        }

        $xml = preg_replace('/<w:p[ >]/', "\n<w:p ", $xml);

        return trim(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }
}
