<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fallback de /storage/*: quando public/storage nao e symlink para
 * storage/app/public (deploy que copiou o conteudo como pasta real),
 * o Apache nao encontra o arquivo novo e cai no front controller —
 * aqui o arquivo e servido do disk publico, mesmo resultado do link.
 */
class StorageFileController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        abort_if(str_contains($path, '..'), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path);
    }
}
