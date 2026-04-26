<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link https://cakephp.org CakePHP(tm) Project
 * @since 3.3.0
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Test\TestCase;

use App\Application;
use App\Middleware\HostHeaderMiddleware;
use App\Middleware\StrictCspMiddleware;
use Cake\Core\Configure;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * ApplicationTest class
 */
class ApplicationTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Test bootstrap in production.
     *
     * @return void
     */
    public function testBootstrap(): void
    {
        Configure::write('debug', false);
        $app = new Application(dirname(__DIR__, 2) . '/config');
        $app->bootstrap();
        $plugins = $app->getPlugins();

        $this->assertTrue($plugins->has('Bake'), 'plugins has Bake?');
        $this->assertFalse($plugins->has('DebugKit'), 'plugins has DebugKit?');
        $this->assertTrue($plugins->has('Migrations'), 'plugins has Migrations?');
    }

    /**
     * DebugKit is currently disabled in config/plugins.php — its inline
     * toolbar conflicts with strict CSP. Assert it is *not* loaded so a
     * future re-enable does not silently slip in.
     *
     * @return void
     */
    public function testDebugKitDisabledForStrictCsp(): void
    {
        Configure::write('debug', true);
        $app = new Application(dirname(__DIR__, 2) . '/config');
        $app->bootstrap();
        $plugins = $app->getPlugins();

        $this->assertFalse(
            $plugins->has('DebugKit'),
            'DebugKit should remain disabled while the demo runs under strict CSP. See config/plugins.php.',
        );
    }

    /**
     * testMiddleware — order matches src/Application.php:
     *   ErrorHandler → StrictCsp → HostHeader → Asset → Routing → …
     *
     * @return void
     */
    public function testMiddleware(): void
    {
        $app = new Application(dirname(__DIR__, 2) . '/config');
        $middleware = new MiddlewareQueue();

        $middleware = $app->middleware($middleware);

        $this->assertInstanceOf(ErrorHandlerMiddleware::class, $middleware->current());
        $middleware->seek(1);
        $this->assertInstanceOf(StrictCspMiddleware::class, $middleware->current());
        $middleware->seek(2);
        $this->assertInstanceOf(HostHeaderMiddleware::class, $middleware->current());
        $middleware->seek(3);
        $this->assertInstanceOf(AssetMiddleware::class, $middleware->current());
        $middleware->seek(4);
        $this->assertInstanceOf(RoutingMiddleware::class, $middleware->current());
    }
}
