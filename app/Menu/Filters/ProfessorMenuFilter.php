<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

/**
 * Ajusta o menu lateral para perfis de docencia (professor,
 * professor_substituto): grupos marcados com 'ocultar_professor'
 * somem e itens marcados com 'somente_professor' so aparecem
 * para eles. Roda antes de RemoveEmptySubmenuFilter, que colapsa
 * os pais que ficarem sem filhos visiveis.
 */
class ProfessorMenuFilter implements FilterInterface
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

        if (! empty($item['somente_professor']) && ! $user->isProfessor()) {
            $item['restricted'] = true;
        }

        if (! empty($item['ocultar_professor']) && $user->isProfessor()) {
            $item['restricted'] = true;
        }

        return $item;
    }
}
