<?php

namespace App\Support;

use App\Models\ArquivoRastreado;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArquivoAnexoHelper
{
    public static function armazenar(
        ?UploadedFile $arquivo,
        string $diretorio,
        ?string $arquivoAntigo = null,
        string $disk = 'public'
    ): ?string {
        if (! $arquivo instanceof UploadedFile || ! $arquivo->isValid()) {
            return null;
        }

        $diretorio = trim($diretorio, "/\\");

        if ($diretorio === '') {
            throw new \InvalidArgumentException(
                'Diretorio de armazenamento nao pode ser vazio.'
            );
        }

        $extensao = strtolower(trim((string) (
            $arquivo->getClientOriginalExtension()
            ?: $arquivo->extension()
            ?: 'bin'
        )));

        $nomeArquivo = Str::uuid()->toString();

        if ($extensao !== '') {
            $nomeArquivo .= '.' . $extensao;
        }

        $caminho = $diretorio . '/' . $nomeArquivo;

        /*
     * No Windows, getRealPath() pode retornar false mesmo com o
     * arquivo temporário válido. Nesse caso, usamos getPathname().
     */
        $realPath = $arquivo->getRealPath();

        $tempPath = is_string($realPath)
            ? trim($realPath)
            : '';

        if ($tempPath === '') {
            $pathname = $arquivo->getPathname();

            $tempPath = is_string($pathname)
                ? trim($pathname)
                : '';
        }

        if (
            $tempPath === ''
            || ! is_file($tempPath)
            || ! is_readable($tempPath)
        ) {
            Log::warning('ArquivoAnexoHelper: arquivo temporario invalido', [
                'diretorio' => $diretorio,
                'disk' => $disk,
                'original_name' => $arquivo->getClientOriginalName(),
                'temp_path' => $tempPath,
                'exists' => $tempPath !== '' && is_file($tempPath),
                'readable' => $tempPath !== '' && is_readable($tempPath),
            ]);

            return null;
        }

        $stream = fopen($tempPath, 'rb');

        if ($stream === false) {
            Log::error('ArquivoAnexoHelper: não foi possível abrir o temporário', [
                'temp_path' => $tempPath,
                'original_name' => $arquivo->getClientOriginalName(),
            ]);

            return null;
        }

        try {
            $armazenado = Storage::disk($disk)->writeStream(
                $caminho,
                $stream
            );
        } catch (\Throwable $error) {
            Log::error('ArquivoAnexoHelper: erro ao salvar arquivo', [
                'diretorio' => $diretorio,
                'disk' => $disk,
                'caminho' => $caminho,
                'disk_root' => Storage::disk($disk)->path(''),
                'original_name' => $arquivo->getClientOriginalName(),
                'temp_path' => $tempPath,
                'erro' => $error->getMessage(),
                'classe_erro' => get_class($error),
            ]);

            return null;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if ($armazenado !== true) {
            Log::error('ArquivoAnexoHelper: writeStream retornou false', [
                'diretorio' => $diretorio,
                'disk' => $disk,
                'caminho' => $caminho,
                'original_name' => $arquivo->getClientOriginalName(),
            ]);

            return null;
        }

        if (! Storage::disk($disk)->exists($caminho)) {
            Log::error('ArquivoAnexoHelper: arquivo não encontrado após gravação', [
                'disk' => $disk,
                'caminho' => $caminho,
            ]);

            return null;
        }

        $arquivoAntigo = is_string($arquivoAntigo)
            ? trim($arquivoAntigo)
            : '';

        if (
            $arquivoAntigo !== ''
            && $arquivoAntigo !== $caminho
            && Storage::disk($disk)->exists($arquivoAntigo)
        ) {
            Storage::disk($disk)->delete($arquivoAntigo);
        }

        return $caminho;
    }

    public static function armazenarComTamanho(?UploadedFile $arquivo, string $diretorio, string $disk = 'public'): ?array
    {
        $caminho = self::armazenar($arquivo, $diretorio, null, $disk);
        if (! is_string($caminho) || trim($caminho) === '') {
            return null;
        }

        $tamanho = 0;
        if ($arquivo instanceof UploadedFile && $arquivo->isValid()) {
            $tamanho = (int) $arquivo->getSize();
        }

        if ($tamanho <= 0) {
            $tamanho = (int) (Storage::disk($disk)->size($caminho) ?: 0);
        }

        return [
            'path' => $caminho,
            'size' => $tamanho,
        ];
    }

    public static function armazenarRastreado(?UploadedFile $arquivo, string $diretorio, array $dados = [], ?string $arquivoAntigo = null, string $disk = 'public'): ?array
    {
        if (!$arquivo instanceof UploadedFile || ! $arquivo->isValid()) {
            return null;
        }

        $caminho = self::armazenar($arquivo, $diretorio, $arquivoAntigo, $disk);
        if (! is_string($caminho) || trim($caminho) === '') {
            return null;
        }

        $tamanho = (int) $arquivo->getSize();
        if ($tamanho <= 0) {
            $tamanho = (int) (Storage::disk($disk)->size($caminho) ?: 0);
        }

        $meta = Arr::get($dados, 'meta');
        if (! is_array($meta)) {
            $meta = [];
        }

        ArquivoRastreado::create([
            'empresa_id' => Arr::get($dados, 'empresa_id'),
            'user_id' => Arr::get($dados, 'user_id', Auth::id()),
            'modulo' => (string) Arr::get($dados, 'modulo', 'geral'),
            'entidade' => Arr::get($dados, 'entidade'),
            'entidade_id' => Arr::get($dados, 'entidade_id'),
            'campo' => Arr::get($dados, 'campo'),
            'contexto' => Arr::get($dados, 'contexto'),
            'disk' => $disk,
            'path' => $caminho,
            'original_name' => $arquivo->getClientOriginalName(),
            'mime_type' => $arquivo->getClientMimeType(),
            'size_bytes' => $tamanho,
            'hash_sha1' => $tamanho > 0 ? self::sha1Arquivo($arquivo) : null,
            'meta' => $meta,
        ]);

        return [
            'path' => $caminho,
            'size' => $tamanho,
        ];
    }

    public static function armazenarUploadRastreado(
        ?UploadedFile $arquivo,
        string $diretorio,
        array $dados = [],
        ?string $arquivoAntigo = null,
        string $disk = 'public'
    ): ?string {
        $resultado = self::armazenarRastreado($arquivo, $diretorio, $dados, $arquivoAntigo, $disk);

        return is_array($resultado) ? ($resultado['path'] ?? null) : null;
    }

    public static function salvarConteudoRastreado(string $conteudo, string $diretorio, array $dados = [], ?string $nomeArquivo = null, string $disk = 'public'): ?string
    {
        $diretorio = trim($diretorio, "/\\");
        if ($diretorio === '') {
            throw new \InvalidArgumentException('Diretorio de armazenamento nao pode ser vazio.');
        }

        $nomeArquivo = $nomeArquivo ?: Str::uuid()->toString() . '.bin';
        $caminho = $diretorio . '/' . ltrim($nomeArquivo, '/\\');

        Storage::disk($disk)->put($caminho, $conteudo);

        $tamanho = strlen($conteudo);
        ArquivoRastreado::create([
            'empresa_id' => Arr::get($dados, 'empresa_id'),
            'user_id' => Arr::get($dados, 'user_id', Auth::id()),
            'modulo' => (string) Arr::get($dados, 'modulo', 'geral'),
            'entidade' => Arr::get($dados, 'entidade'),
            'entidade_id' => Arr::get($dados, 'entidade_id'),
            'campo' => Arr::get($dados, 'campo'),
            'contexto' => Arr::get($dados, 'contexto'),
            'disk' => $disk,
            'path' => $caminho,
            'original_name' => $nomeArquivo,
            'mime_type' => Arr::get($dados, 'mime_type'),
            'size_bytes' => $tamanho,
            'hash_sha1' => sha1($conteudo),
            'meta' => is_array(Arr::get($dados, 'meta')) ? Arr::get($dados, 'meta') : [],
        ]);

        return $caminho;
    }

    private static function sha1Arquivo(UploadedFile $arquivo): ?string
    {
        $caminho = is_string($arquivo->getRealPath()) && $arquivo->getRealPath() !== ''
            ? $arquivo->getRealPath()
            : (is_string($arquivo->getPathname()) ? $arquivo->getPathname() : '');

        if ($caminho === '' || ! is_file($caminho)) {
            return null;
        }

        $hash = @sha1_file($caminho);

        return is_string($hash) && $hash !== '' ? $hash : null;
    }
}
