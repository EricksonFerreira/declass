<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait FiltroTrait
{
    /**
     * Cria filtros de sessao de forma dinamica.
     *
     * @return void
     */
    private function criarFiltros(Request $request, string $nomeFiltro)
    {
        session()->forget("$nomeFiltro.filtro");
        $inputs = $request->all();

        // Detect range inputs like data_inicio / data_fim or campo_from / campo_to
        $ranges = [];
        foreach ($inputs as $k => $v) {
            if (preg_match('/^(.*)_(inicio|fim|from|to)$/i', $k, $m)) {
                $base = $m[1];
                $side = strtolower($m[2]);
                if (! isset($ranges[$base])) {
                    $ranges[$base] = ['from' => null, 'to' => null];
                }
                if (in_array($side, ['inicio', 'from'])) {
                    $ranges[$base]['from'] = $v;
                } else {
                    $ranges[$base]['to'] = $v;
                }
                unset($inputs[$k]);
            }
        }

        // Process remaining inputs normally
        foreach ($inputs as $chave => $valor) {
            if ($valor !== null && ! in_array($chave, ['nomeFiltro', 'urlPadrao', 'routeRedirect'])) {
                $nomeExibir = $this->formatarNomeExibir($chave);
                [$cleanKey, $typeWhere] = $this->determinarTipoFiltro($chave);

                session(["$nomeFiltro.filtro.$cleanKey" => [
                    'url' => $this->normalizeAdminPath((string) session()->get($nomeFiltro.'.urlPadrao')).'/removerfiltro?'.$cleanKey.'=1',
                    'name' => $cleanKey,
                    'nomeExibir' => $nomeExibir,
                    'valor' => $valor,
                    'typeWhere' => $typeWhere,
                ]]);
            }
        }

        // Store combined range filters
        foreach ($ranges as $base => $vals) {
            if ($vals['from'] === null && $vals['to'] === null) {
                continue;
            }

            $nomeExibir = $this->formatarNomeExibir($base);
            session(["$nomeFiltro.filtro.$base" => [
                'url' => $this->normalizeAdminPath((string) session()->get($nomeFiltro.'.urlPadrao')).'/removerfiltro?'.$base.'=1',
                'name' => $base,
                'nomeExibir' => $nomeExibir,
                'valor' => ['from' => $vals['from'], 'to' => $vals['to']],
                'typeWhere' => 'between',
            ]]);
        }
    }

    /**
     * Remove filtros de sessao.
     *
     * @return void
     */
    public function removeSessao(string $sessao, ?string $atributo = null)
    {
        $atributo = $atributo ? ".$atributo" : '';
        session()->forget("$sessao.filtro$atributo");
    }

    public function redirecionaRotaRedirect(string $nomeFiltro, string $mensagem = 'Operação realizada com sucesso.', string $tipo = 'success')
    {
        if (session()->has($nomeFiltro.'.rotaRedirect')) {
            $redirect = redirect()->route(session($nomeFiltro.'.rotaRedirect'));

            return $mensagem !== null
                ? $redirect->with($tipo, $mensagem)
                : $redirect;
        }

        if ($mensagem) {
            return redirect()->back()->with($tipo, $mensagem);
        }

        return redirect()->back();
    }

    private function determinarTipoFiltro(string $chave): array
    {
        $typeWhere = 'where';
        if (str_ends_with($chave, '_like')) {
            $typeWhere = 'like';
            $chave = str_replace('_like', '', $chave);
        } elseif (str_ends_with($chave, '_where')) {
            $chave = str_replace('_where', '', $chave);
        }

        return [$chave, $typeWhere];
    }

    private function formatarNomeExibir($chave)
    {
        $nomeExibir = preg_replace('/(?<!^)([A-Z])/', ' $1', ucfirst($chave));
        $nomeExibir = str_replace(['_where', '_like', '_'], ' ', $nomeExibir);
        $nomeExibir = preg_replace('/\b(id|Id|iD|ID)\b/i', '', $nomeExibir);
        $nomeExibir = ucwords($nomeExibir);

        return $nomeExibir;
    }

    private function normalizeAdminPath(string $path): string
    {
        $normalizedPath = trim($path, '/');
        $adminPrefix = trim((string) config('app.admin_prefix', 'admin'), '/');

        if ($normalizedPath === '') {
            return $adminPrefix === '' ? '' : $adminPrefix;
        }

        if ($adminPrefix === '') {
            return $normalizedPath;
        }

        if ($normalizedPath === $adminPrefix || str_starts_with($normalizedPath, $adminPrefix.'/')) {
            return $normalizedPath;
        }

        return $adminPrefix.'/'.$normalizedPath;
    }

    public static function formatOptions($items, $defaultLabel = 'Selecione uma opcao')
    {
        $options = [];
        $options[] = ['value' => '', 'label' => $defaultLabel, 'selected' => false];

        foreach ($items as $key => $value) {
            $options[] = ['value' => $key, 'label' => $value, 'selected' => false];
        }

        return $options;
    }
}
