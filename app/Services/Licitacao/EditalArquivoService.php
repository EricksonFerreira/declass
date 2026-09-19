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
     * Anexa um ou mais arquivos ao edital e acrescenta o texto extraído
     * em texto_edital (separado por "=== nome do arquivo ===").
     * Retorna a quantidade total de caracteres extraídos.
     *
     * @param  UploadedFile[]  $arquivos
     */
    public function importar(array $arquivos, Licitacao $licitacao): int
    {
        $lista = $licitacao->arquivos;
        $chars = 0;
        $novosTextos = [];

        foreach ($arquivos as $arquivo) {
            $texto = $this->extrairTexto($arquivo);

            $lista[] = [
                'path' => $arquivo->storeAs(
                    "licitacoes/{$licitacao->empresa_id}",
                    uniqid("edital-{$licitacao->id}-").'.'.$arquivo->getClientOriginalExtension()
                ),
                'nome' => $arquivo->getClientOriginalName(),
            ];

            $chars += mb_strlen(trim($texto));
            if (mb_strlen(trim($texto)) > 0) {
                $novosTextos[] = '=== '.$arquivo->getClientOriginalName()." ===\n".$texto;
            }
        }

        $dados = ['arquivo_path' => json_encode($lista)];
        if ($novosTextos !== []) {
            $base = trim((string) $licitacao->texto_edital);
            $dados['texto_edital'] = mb_substr(
                ($base !== '' ? $base."\n\n" : '').implode("\n\n", $novosTextos),
                0,
                400000
            );
        }

        $licitacao->update($dados);

        return $chars;
    }

    public function excluirArquivo(Licitacao $licitacao, int $index): void
    {
        $lista = $licitacao->arquivos;
        if (! isset($lista[$index])) {
            return;
        }

        Storage::delete($lista[$index]['path']);
        array_splice($lista, $index, 1);

        $licitacao->update(['arquivo_path' => $lista === [] ? null : json_encode($lista)]);
    }

    public function excluir(Licitacao $licitacao): void
    {
        foreach ($licitacao->arquivos as $arquivo) {
            Storage::delete($arquivo['path']);
        }

        $licitacao->update(['arquivo_path' => null]);
    }

    private function extrairTexto(UploadedFile $arquivo): string
    {
        $ext = strtolower($arquivo->getClientOriginalExtension());
        $nome = $arquivo->getClientOriginalName();

        try {
            return match (true) {
                $ext === 'pdf' => $this->extrairPdf($arquivo->getRealPath()),
                $ext === 'docx' => $this->extrairDocx($arquivo->getRealPath()),
                in_array($ext, self::EXTENSOES_TEXTO) => (string) file_get_contents($arquivo->getRealPath()),
                default => throw new RuntimeException("{$nome}: formato não suportado. Envie PDF, DOCX ou TXT."),
            };
        } catch (RuntimeException $e) {
            throw new RuntimeException(str_contains($e->getMessage(), $nome) ? $e->getMessage() : "{$nome}: {$e->getMessage()}");
        }
    }

    private function extrairPdf(string $path): string
    {
        try {
            $documento = (new PdfParser)->parseFile($path);
        } catch (\Throwable $e) {
            throw new RuntimeException('não consegui ler o PDF. Se ele for digitalizado (imagem), cole o texto manualmente.');
        }

        return $documento->getText();
    }

    private function extrairDocx(string $path): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('servidor sem suporte a DOCX (ext-zip ausente). Envie PDF ou cole o texto.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('não consegui ler o DOCX.');
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
