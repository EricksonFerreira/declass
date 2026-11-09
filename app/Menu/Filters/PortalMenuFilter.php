<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

/**
 * Ajusta o menu lateral para contas de portal (aluno/responsavel):
 * itens marcados com 'somente_portal' so aparecem para eles e,
 * para contas de portal, todo o resto da sidebar fica oculto —
 * a navegacao deles e apenas Inicio + itens do portal. Itens com
 * 'type' (widgets da navbar) nao sao afetados.
 * Roda antes de RemoveEmptySubmenuFilter, que colapsa os pais
 * que ficarem sem filhos visiveis.
 */
class PortalMenuFilter implements FilterInterface
{
    public function transform($item)
    {
        if (! is_array($item)) {
            return $item;
        }

        if (! empty($item['submenu']) && is_array($item['submenu'])) {
            $item['submenu'] = array_map(fn ($child) => $this->transform($child), $item['submenu']);
        }

        $user = auth()->user();
        if (! $user) {
            return $item;
        }

        if ($user->isPortalAccount()) {
            if (empty($item['type']) && ! $this->permitidoNoPortal($item)) {
                $item['restricted'] = true;
            } elseif (($item['route'] ?? null) === 'home') {
                $item['route'] = 'portal.index';
            }
        } elseif (! empty($item['somente_portal'])) {
            $item['restricted'] = true;
        }

        return $item;
    }

    /**
     * Itens da sidebar que a conta de portal pode ver: Inicio e os
     * marcados com 'somente_portal'.
     */
    private function permitidoNoPortal(array $item): bool
    {
        return ! empty($item['somente_portal'])
            || ($item['route'] ?? null) === 'home';
    }
}
