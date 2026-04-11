<?php
/**
 * Routes configuration.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->connect('/', ['controller' => 'Demo', 'action' => 'index']);
        $builder->connect('/pages/*', 'Pages::display');
        $builder->connect('/strategy', ['controller' => 'Strategy', 'action' => 'index']);
        $builder->connect('/strategy/external-roles/role', [
            'controller' => 'ExternalRoleSwitcher',
            'action' => 'switch',
        ]);
        $builder->fallbacks();
    });

    // Strategy route prefixes — one per usage strategy.
    $strategyPrefixes = [
        'AdapterOnly' => 'adapter-only',
        'FullBackend' => 'full-backend',
        'NativeAuth' => 'native-auth',
        'ExternalRoles' => 'external-roles',
    ];
    foreach ($strategyPrefixes as $prefix => $path) {
        $routes->prefix($prefix, ['path' => '/' . $path], function (RouteBuilder $builder): void {
            $builder->connect('/', ['controller' => 'Articles', 'action' => 'index']);
            $builder->connect('/articles', ['controller' => 'Articles', 'action' => 'index']);
            $builder->connect('/articles/{id}', ['controller' => 'Articles', 'action' => 'view'], ['pass' => ['id'], 'id' => '\d+']);
            $builder->connect('/articles/{id}/edit', ['controller' => 'Articles', 'action' => 'edit'], ['pass' => ['id'], 'id' => '\d+']);
            $builder->connect('/articles/{id}/delete', ['controller' => 'Articles', 'action' => 'delete'], ['pass' => ['id'], 'id' => '\d+']);
            $builder->connect('/projects', ['controller' => 'Projects', 'action' => 'index']);
            $builder->connect('/projects/{id}', ['controller' => 'Projects', 'action' => 'view'], ['pass' => ['id'], 'id' => '\d+']);
            $builder->connect('/projects/{id}/edit', ['controller' => 'Projects', 'action' => 'edit'], ['pass' => ['id'], 'id' => '\d+']);
            $builder->fallbacks();
        });
    }

    // Admin prefix routes
    $routes->prefix('Admin', function (RouteBuilder $builder): void {
        $builder->fallbacks();
    });
};
