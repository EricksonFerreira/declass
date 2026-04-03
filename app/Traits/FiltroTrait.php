<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait FiltroTrait
{
    /**
     * Cria filtros de sessao de forma dinamica.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $nomeFiltro
     * @return void
     */
    private function criarFiltros(Request $request, string $nomeFiltro)
    {
        session()->forget("$nomeFiltro.filtro");

        $filtros = $request->all();
        foreach ($filtros as $chave => $valor) {
            if ($valor !== null && !in_array($chave, ['nomeFiltro', 'urlPadrao', 'routeRedirect'])) {
                $nomeExibir = $this->formatarNomeExibir($chave);
                [$chave, $typeWhere] = $this->determinarTipoFiltro($chave);

                session(["$nomeFiltro.filtro.$chave" => [
                    'url' => $this->normalizeAdminPath((string) session()->get($nomeFiltro . '.urlPadrao')) . '/removerfiltro?' . $chave . '=1',
                    'name' => $chave,
                    'nomeExibir' => $nomeExibir,
                    'valor' => $valor,
                    'typeWhere' => $typeWhere,
                ]]);
            }
        }
    }

    /**
     * Remove filtros de sessao.
     *
     * @param string $sessao
     * @param string|null $atributo
     * @return void
     */
    public function removeSessao(string $sessao, string $atributo = null)
    {
        $atributo = $atributo ? ".$atributo" : '';
        session()->forget("$sessao.filtro$atributo");
    }

    public function redirecionaRotaRedirect(string $nomeFiltro, string $mensagem = 'Nao ha rota de redirecionamento', string $tipo = 'success')
    {
        if (session()->has($nomeFiltro . '.rotaRedirect')) {
            return redirect()->route(session($nomeFiltro . '.rotaRedirect'));
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

        if ($normalizedPath === $adminPrefix || str_starts_with($normalizedPath, $adminPrefix . '/')) {
            return $normalizedPath;
        }

        return $adminPrefix . '/' . $normalizedPath;
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
