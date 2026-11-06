<?php

namespace App\Helpers;

use App\DTO\BreadCrumbItem;

class BreadcrumbHelper
{
    /**
     * Generate a breadcrumb array.
     *
     * @param  BreadCrumbItem[]  $items  Array of BreadCrumbItem objects.
     */
    public static function generate(array $items): array
    {
        $breadcrumb = [];
        foreach ($items as $item) {
            $breadcrumb[] = [
                'name' => $item->getName(),
                'url' => $item->getUrl(),
            ];
        }

        return $breadcrumb;
    }
}
