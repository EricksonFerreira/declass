<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class RemoveEmptySubmenuFilter implements FilterInterface
{
    public function transform($item)
    {
        if (empty($item['submenu']) || ! is_array($item['submenu'])) {
            return $item;
        }

        // If any child is visible, keep this parent. Children marked as
        // 'restricted' by GateFilter are considered hidden.
        $hasVisible = false;

        foreach ($item['submenu'] as $child) {
            if ($this->isVisible($child)) {
                $hasVisible = true;
                break;
            }
        }

        if (! $hasVisible) {
            $item['restricted'] = true;
        }

        return $item;
    }

    protected function isVisible($item)
    {
        // Non-array items (strings) consider visible
        if (! is_array($item)) {
            return true;
        }

        // If explicitly restricted by prior filters (e.g. GateFilter), hide it
        if (! empty($item['restricted'])) {
            return false;
        }

        // If item has submenu, it's visible only if any child is visible
        if (! empty($item['submenu']) && is_array($item['submenu'])) {
            foreach ($item['submenu'] as $child) {
                if ($this->isVisible($child)) {
                    return true;
                }
            }

            return false;
        }

        // IMPORTANT: Treat items without an explicit `can` as NOT visible.
        // This causes parent groups that contain only items without permission
        // declarations to be hidden — matching the requested behaviour.
        if (empty($item['can'])) {
            return false;
        }

        return true;
    }
}
