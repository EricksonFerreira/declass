<?php

namespace App\Menu\Filters;

use App\Services\PlanoPermissaoService;
use Illuminate\Support\Facades\Route;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class RoutePermissionVisibilityFilter implements FilterInterface
{
    public function __construct(
        private readonly PlanoPermissaoService $planoPermissaoService
    ) {
    }

    public function transform($item)
    {
        if (! auth()->check()) {
            return $item;
        }

        if (! is_array($item)) {
            return $item;
        }

        if (! empty($item['submenu']) && is_array($item['submenu'])) {
            $item['submenu'] = array_map(function ($child) {
                return is_array($child) ? $this->transform($child) : $child;
            }, $item['submenu']);
        }

        if (! empty($item['restricted'])) {
            return $item;
        }

        $routeName = $this->resolveRouteName($item);
        if ($routeName === null) {
            return $item;
        }

        if (! $this->planoPermissaoService->usuarioPodeAcessarRota(auth()->user(), $routeName)) {
            $item['restricted'] = true;
        }

        return $item;
    }

    private function resolveRouteName(array $item): ?string
    {
        if (! empty($item['route']) && is_string($item['route'])) {
            return $item['route'];
        }

        if (! empty($item['can']) && is_string($item['can']) && Route::has($item['can'])) {
            return $item['can'];
        }

        return null;
    }
}
