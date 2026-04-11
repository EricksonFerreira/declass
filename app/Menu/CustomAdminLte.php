<?php

namespace App\Menu;

use JeroenNoten\LaravelAdminLte\AdminLte;

/**
 * Extends AdminLTE's AdminLte to use CustomBuilder, which removes orphan
 * section headers when no visible items follow them.
 */
class CustomAdminLte extends AdminLte
{
    /**
     * Override buildMenu so that before any items are added we replace the
     * standard Builder with our CustomBuilder (same filters, different compileItems).
     */
    protected function buildMenu()
    {
        // At this point parent::__construct() has already set $this->menuBuilder
        // to a standard Builder instance. We swap it for our CustomBuilder using
        // the same resolved filters.
        $builderRef = new \ReflectionProperty(AdminLte::class, 'menuBuilder');
        $builderRef->setAccessible(true);
        $standardBuilder = $builderRef->getValue($this);

        $filterRef = new \ReflectionProperty(get_class($standardBuilder), 'filters');
        $filterRef->setAccessible(true);
        $filters = $filterRef->getValue($standardBuilder);

        $builderRef->setValue($this, new CustomBuilder($filters));

        // Delegate to parent to add menu items from config and fire the event.
        parent::buildMenu();
    }
}
