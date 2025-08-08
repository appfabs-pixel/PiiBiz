<?php

namespace Workdo\ProductService\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'ProductService';
        $menu = $event->menu;
        $menu->add([
            'category' => 'General',
            'title' => __('Items'),
            'icon' => 'shopping-cart',
            'name' => 'product-service',
            'parent' => null,
            'order' => 100,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'product-service.index',
            'module' => $module,
            'permission' => 'product&service manage'
        ]);
        $menu->add([
            'category' => 'General',
            'title' => __('Production'),
            'icon' => 'box',
            'name' => 'production',
            'parent' => null,
            'order' => 101,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'production.index',
            'module' => $module,
            'permission' => 'product&service manage'
        ]);
        $menu->add([
            'category' => 'General',
            'title' => __('Sales'),
            'icon' => 'businessplan',
            'name' => 'sales',
            'parent' => 'production',
            'order' => 102,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'sales.index',
            'module' => $module,
            'permission' => ''
        ]);
    }
}
