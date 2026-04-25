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

        if (!empty($item['url']) && is_string($item['url'])) {
            $item['url'] = $this->normalizeAdminUrl($item['url']);
        }

        if (!empty($item['submenu']) && is_array($item['submenu'])) {
            $item['submenu'] = array_map(function ($child) {
                return is_array($child) ? $this->transform($child) : $child;
            }, $item['submenu']);
        }

        // Se a permissao foi definida manualmente no menu, respeita,
        // sem impedir o tratamento automatico dos filhos.
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
        $firstSegment = explode('/', $this->stripAdminPrefix($url))[0] ?? '';
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

    private function normalizeAdminUrl(string $url): string
    {
        $value = trim($url);
        if ($value === '' || str_starts_with($value, '#') || Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        $adminPrefix = $this->adminPrefix();
        if ($adminPrefix === '') {
            return ltrim($value, '/');
        }

        $normalized = ltrim($value, '/');
        if ($normalized === $adminPrefix || str_starts_with($normalized, $adminPrefix . '/')) {
            return $normalized;
        }

        return $adminPrefix . '/' . $normalized;
    }

    private function stripAdminPrefix(string $path): string
    {
        $adminPrefix = $this->adminPrefix();
        $normalized = trim($path, '/');

        if ($adminPrefix === '' || $normalized === '') {
            return $normalized;
        }

        if ($normalized === $adminPrefix) {
            return '';
        }

        if (str_starts_with($normalized, $adminPrefix . '/')) {
            return substr($normalized, strlen($adminPrefix) + 1);
        }

        return $normalized;
    }

    private function adminPrefix(): string
    {
        return trim((string) config('app.admin_prefix', 'admin'), '/');
    }
}
