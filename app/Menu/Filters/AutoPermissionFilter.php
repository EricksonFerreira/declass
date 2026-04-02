<?php

namespace App\Menu\Filters;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class AutoPermissionFilter implements FilterInterface
{
    public function transform($item)
    {
        if (!auth()->check()) {
            return $item;
        }

        // Se a permissao foi definida manualmente no menu, respeita.
        if (!empty($item['can'])) {
            return $item;
        }

        $permission = $this->resolvePermissionFromMenuItem($item);
        if ($permission !== null) {
            $item['can'] = $permission;
        }

        return $item;
    }

    private function resolvePermissionFromMenuItem(array $item): ?string
    {
        // Ignora itens de estrutura/auxiliares.
        if (isset($item['header']) || !empty($item['submenu']) || isset($item['type'])) {
            return null;
        }

        if (!empty($item['route']) && is_string($item['route'])) {
            return $item['route'];
        }

        if (empty($item['url']) || !is_string($item['url'])) {
            return null;
        }

        $url = trim((string) Str::of($item['url'])->before('?'), '/');
        if ($url === '' || str_starts_with($url, '#') || Str::startsWith($url, ['http://', 'https://'])) {
            return null;
        }

        $matchedRouteName = $this->matchGetRouteNameByPath($url);
        if ($matchedRouteName) {
            return $matchedRouteName;
        }

        // Fallback para padrao comum: modulo.index
        $firstSegment = explode('/', $url)[0] ?? '';
        $firstSegment = str_replace('-', '_', $firstSegment);
        if ($firstSegment === '') {
            return null;
        }

        return $firstSegment . '.index';
    }

    private function matchGetRouteNameByPath(string $path): ?string
    {
        try {
            $route = Route::getRoutes()->match(request()->create('/' . $path, 'GET'));

            return $route?->getName();
        } catch (\Throwable $e) {
            return null;
        }
    }
}

