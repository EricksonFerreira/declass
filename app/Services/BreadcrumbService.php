<?php

namespace App\Services;

use App\Helpers\BreadcrumbHelper;
use App\DTO\BreadCrumbItem;

class BreadcrumbService
{
    public function prepararBreadcrumb(array $items)
    {
        return BreadcrumbHelper::generate($items);
    }

    public function prepararBreadcrumbUnico(string $indexName)
    {
        $items = $this->buildBreadCrumbItems([
            [$indexName, null],
        ]);
        return $this->prepararBreadcrumb($items);
    }
    public function prepararBreadcrumbIndex(string $indexName, ?string $grupo = null)
    {
        $items = $this->buildBreadCrumbItems(array_filter([
            ['Home', route('home')],
            $grupo ? [$grupo, null] : null,
            [$indexName, null],
        ]));
        return $this->prepararBreadcrumb($items);
    }

    public function prepararBreadcrumbCreate(string $indexName, string $routeName, string $createName, ?string $grupo = null)
    {
        $items = $this->buildBreadCrumbItems(array_filter([
            ['Home', route('home')],
            $grupo ? [$grupo, null] : null,
            [$indexName, route($routeName)],
            [$createName, null],
        ]));
        return $this->prepararBreadcrumb($items);
    }

    public function prepararBreadcrumbEdit( string $indexName,string $routeName, string $editName, ?string $grupo = null)
    {
        $items = $this->buildBreadCrumbItems(array_filter([
            ['Home', route('home')],
            $grupo ? [$grupo, null] : null,
            [$indexName, route($routeName)],
            [$editName, null],
        ]));
        return $this->prepararBreadcrumb($items);
    }

    public function prepararBreadcrumbShow(string $indexName, string $routeName, string $showName, ?string $grupo = null)
    {
        $items = $this->buildBreadCrumbItems(array_filter([
            ['Home', route('home')],
            $grupo ? [$grupo, null] : null,
            [$indexName, route($routeName)],
            [$showName, null],
        ]));
        return $this->prepararBreadcrumb($items);
    }

    private function buildBreadCrumbItems(array $itemsData)
    {
        $items = [];
        foreach ($itemsData as $itemData) {
            $items[] = new BreadCrumbItem($itemData[0], $itemData[1] ?? null);
        }
        return $items;
    }
}
