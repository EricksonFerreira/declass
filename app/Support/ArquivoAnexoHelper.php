<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArquivoAnexoHelper
{
    public static function armazenar(?UploadedFile $arquivo, string $diretorio, ?string $arquivoAntigo = null, string $disk = 'public'): ?string
    {
        if (!$arquivo instanceof UploadedFile || !$arquivo->isValid()) {
            return null;
        }

        $diretorio = trim($diretorio, "/\\");
        if ($diretorio === '') {
            throw new \InvalidArgumentException('Diretorio de armazenamento nao pode ser vazio.');
        }

        $extensao = strtolower(trim((string) ($arquivo->getClientOriginalExtension() ?: $arquivo->extension() ?: 'bin')));
        $nomeArquivo = Str::uuid()->toString();
        if ($extensao !== '') {
            $nomeArquivo .= '.' . $extensao;
        }

        $caminho = $diretorio . '/' . $nomeArquivo;
        $armazenado = Storage::disk($disk)->putFileAs($diretorio, $arquivo, $nomeArquivo);

        if (!is_string($armazenado) || trim($armazenado) === '') {
            return null;
        }

        $arquivoAntigo = is_string($arquivoAntigo) ? trim($arquivoAntigo) : '';
        if ($arquivoAntigo !== '' && $arquivoAntigo !== $caminho && Storage::disk($disk)->exists($arquivoAntigo)) {
            Storage::disk($disk)->delete($arquivoAntigo);
        }

        return $caminho;
    }
}
