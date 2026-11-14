<?php

namespace App\Menu\Filters;

use App\Models\SolicitacaoMatricula;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

/**
 * Badge com o total de solicitacoes de matricula pendentes no
 * item de menu da fila — visivel para os perfis que tem a
 * permissao da rota (diretoria/secretaria/administracao).
 */
class SolicitacaoMatriculaBadgeFilter implements FilterInterface
{
    private const ROTA_FILA = 'solicitacao_matricula.index';

    private ?int $pendentes = null;

    public function transform($item)
    {
        if (! auth()->check() || ! is_array($item)) {
            return $item;
        }

        if (! empty($item['submenu']) && is_array($item['submenu'])) {
            $item['submenu'] = array_map(
                fn ($child) => is_array($child) ? $this->transform($child) : $child,
                $item['submenu']
            );
        }

        if (($item['route'] ?? null) !== self::ROTA_FILA) {
            return $item;
        }

        if (! empty($item['restricted']) || ! auth()->user()->can('solicitacao_matricula.index')) {
            return $item;
        }

        $pendentes = $this->pendentes();
        if ($pendentes > 0) {
            $item['label'] = $pendentes;
            $item['label_color'] = 'warning';
        }

        return $item;
    }

    private function pendentes(): int
    {
        return $this->pendentes ??= SolicitacaoMatricula::query()
            ->whereIn('status', ['pendente', 'em_analise'])
            ->count();
    }
}
