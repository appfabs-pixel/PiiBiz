<?php

namespace App\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Base';
        $menu = $event->menu;
        $menu->add([
            'category' => 'General',
            'title' => __('Dashboard'),
            'icon' => 'home',
            'name' => 'dashboard',
            'parent' => null,
            'order' => 1,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => ''
        ]);
        // $menu->add([
        //     'category' => 'General',
        //     'title' => __('User Management'),
        //     'icon' => 'users',
        //     'name' => 'user-management',
        //     'parent' => null,
        //     'order' => 50,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => '',
        //     'module' => $module,
        //     'permission' => 'user manage'
        // ]);
        // $menu->add([
        //     'category' => 'General',
        //     'title' => __('User'),
        //     'icon' => '',
        //     'name' => 'user',
        //     'parent' => 'user-management',
        //     'order' => 10,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => 'users.index',
        //     'module' => $module,
        //     'permission' => 'user manage'
        // ]);
        // $menu->add([
        //     'category' => 'General',
        //     'title' => __('Role'),
        //     'icon' => '',
        //     'name' => 'role',
        //     'parent' => 'user-management',
        //     'order' => 20,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => 'roles.index',
        //     'module' => $module,
        //     'permission' => 'roles manage'
        // ]);
        // $menu->add([
        //     'category' => 'Finance',
        //     'title' => __('Invoice'),
        //     'icon' => 'file-invoice',
        //     'name' => 'invoice',
        //     'parent' => '',
        //     'order' => 200,
        //     'ignore_if' => [],
        //     'depend_on' => ['Account','Taskly'],
        //     'route' => 'invoice.index',
        //     'module' => $module,
        // 'permission' => 'invoice manage'
        // ]);

        $menu->add([
            'category' => 'Sales',
            'title' => __('Sales'),
            'icon' => 'businessplan',
            'name' => 'sales',
            'parent' => null,
            'order' => 240,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'sales.index',
            'module' => $module,
            'permission' => 'sales manage'
        ]);



        $menu->add([
            'category' => 'Finance',
            'title' => __('Purchases'),
            'icon' => 'shopping-cart',
            'name' => 'purchases',
            'parent' => null,
            'order' => 250,
            'ignore_if' => [],
            'depend_on' => ['Account','Taskly'],
            'route' => '',
            'module' => $module,
            'permission' => 'purchase manage'
        ]);
          $menu->add([
            'category' => 'Finance',
            'title' => __('Purchase'),
            'icon' => '',
            'name' => 'purchase',
            'parent' => 'purchases',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => ['Account','Taskly'],
            'route' => 'purchases.index',
            'module' => $module,
            'permission' => 'purchase manage'
        ]);

        $menu->add([
            'category' => 'Finance',
            'title' => __('Warehouse'),
            'icon' => '',
            'name' => 'warehouse',
            'parent' => 'purchases',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => ['Account','Taskly'],
            'route' => 'warehouses.index',
            'module' => $module,
            'permission' => 'warehouse manage'
        ]);

        $menu->add([
            'category' => 'Finance',
            'title' => __('Transfer'),
            'icon' => '',
            'name' => 'transfer',
            'parent' => 'purchases',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'warehouses-transfer.index',
            'module' => $module,
            'permission' => 'warehouse manage'
        ]);

        $menu->add([
            'category' => 'Finance',
            'title' => __('Report'),
            'icon' => '',
            'name' => 'reports',
            'parent' => 'purchases',
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => ['Account','Taskly'],
            'route' => '',
            'module' => $module,
            'permission' => 'report purchase'
        ]);

        $menu->add([
            'category' => 'Finance',
            'title' => __('Purchase Daily/Monthly Report'),
            'icon' => '',
            'name' => 'purchase-monthly',
            'parent' => 'reports',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'reports.daily.purchase',
            'module' => $module,
            'permission' => 'report purchase'
        ]);

        $menu->add([
            'category' => 'Finance',
            'title' => __('Warehouse Report'),
            'icon' => '',
            'name' => 'warehouse-report',
            'parent' => 'reports',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'reports.warehouse',
            'module' => $module,
            'permission' => 'report warehouse'
        ]);
    }
}
