<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadStorage
{
    /**
     * Grava um arquivo enviado via upload.
     *
     * UploadedFile::store()/storeAs() abrem o arquivo por getRealPath(), mas
     * realpath() retorna false para temporarios de upload em C:\Windows\Temp
     * nesta stack (Apache/PHP no Windows), mesmo com o arquivo existindo e
     * is_uploaded_file() true. Abrir o stream pelo getPathname() evita isso.
     */
    public static function putFile(UploadedFile $file, string $directory, ?string $name = null, ?string $disk = null): ?string
    {
        $stream = @fopen($file->getPathname(), 'r');
        if ($stream === false) {
            return null;
        }

        $path = trim($directory, '/').'/'.($name ?: $file->hashName());
        $stored = Storage::disk($disk)->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return $stored ? $path : null;
    }
}
