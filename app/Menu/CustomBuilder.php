<?php

namespace App\Menu;

use JeroenNoten\LaravelAdminLte\Helpers\MenuItemHelper;
use JeroenNoten\LaravelAdminLte\Menu\Builder;

/**
 * Extends AdminLTE's Builder to remove section headers that have no
 * visible (non-restricted) items following them.
 */
class CustomBuilder extends Builder
{
    /**
     * Override compileItems to post-process orphan section headers.
     * After applying all filters, any 'header' item that is only followed by
     * restricted items (until the next header or end-of-list) is also marked
     * restricted, so it gets removed from the compiled menu.
     */
    protected function compileItems($items)
    {
        // Apply all filters to each item first (same logic as parent).
        $compiled = array_map([$this, 'applyFilters'], $items);

        // Mark orphan section headers as restricted.
        $compiled = $this->markOrphanHeaders(array_values($compiled));

        // Remove restricted items and re-index.
        return array_values(array_filter($compiled, [MenuItemHelper::class, 'isAllowed']));
    }

    /**
     * Scans the compiled item list and marks any 'header' item as restricted
     * if all items between it and the next header (or end of list) are
     * already restricted.
     */
    protected function markOrphanHeaders(array $items): array
    {
        $count = count($items);

        for ($i = 0; $i < $count; $i++) {
            $item = $items[$i];

            // Only act on section header items.
            if (! is_array($item) || ! isset($item['header'])) {
                continue;
            }

            // Look ahead: find whether any truly visible item exists before
            // the next header (or end of list).
            $hasVisible = false;
            for ($j = $i + 1; $j < $count; $j++) {
                if (! is_array($items[$j])) {
                    $hasVisible = true;
                    break;
                }
                // Reached next section header — stop looking.
                if (isset($items[$j]['header'])) {
                    break;
                }
                // Skip items explicitly restricted by a prior filter.
                if (! empty($items[$j]['restricted'])) {
                    continue;
                }
                // Skip submenu parents whose children were all removed
                // (empty submenu array after compile). isAllowed() would
                // remove them, but they don't have restricted=true yet.
                if (isset($items[$j]['submenu']) && is_array($items[$j]['submenu'])
                    && count($items[$j]['submenu']) === 0) {
                    continue;
                }
                // This item is genuinely visible — keep the header.
                $hasVisible = true;
                break;
            }

            if (! $hasVisible) {
                $items[$i]['restricted'] = true;
            }
        }

        return $items;
    }
}
