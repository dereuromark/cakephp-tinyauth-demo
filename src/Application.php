<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link https://cakephp.org CakePHP(tm) Project
 * @since 3.3.0
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App;

use App\Middleware\DemoFeaturesMiddleware;
use App\Middleware\DemoIdentityMiddleware;
use App\Middleware\HostHeaderMiddleware;
use App\Middleware\RequestGateMiddleware;
use App\Middleware\StrategyMiddleware;
use App\Model\Entity\Article;
use App\Model\Entity\Project;
use App\Model\Table\ArticlesTable;
use App\Model\Table\ProjectsTable;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventManagerInterface;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use TinyAuthBackend\Policy\TinyAuthResolver;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 *
 * @extends \Cake\Http\BaseApplication<\App\Application>
 */
class Application extends BaseApplication implements AuthorizationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        // By default, does not allow fallback classes.
        FactoryLocator::add('Table', (new TableLocator())->allowFallbackClass(false));

        // Authorization plugin is loaded via config/plugins.php — it
        // powers the FullBackend / NativeAuth / ExternalRoles strategy
        // demos via `$this->Authorization->authorize()` and
        // `applyScope()`. AdapterOnly deliberately skips loading the
        // Authorization component in its controllers even though the
        // plugin is available.
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     *
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Validate Host header to prevent Host Header Injection attacks.
            // In production, ensures App.fullBaseUrl is configured and validates
            // the incoming Host header against it.
            ->add(new HostHeaderMiddleware())

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            ->add(new RoutingMiddleware($this))

            // Demo: apply feature toggles from session to Configure.
            // Legacy per-feature escape hatch — the four strategies
            // are the primary UX.
            ->add(new DemoFeaturesMiddleware())

            // Demo: apply strategy-specific wiring based on the active
            // route prefix. Must run after routing so the prefix is
            // available on the request params.
            ->add(new StrategyMiddleware())

            // Demo: set a request-level identity from session state
            // written by the role switcher. Replaces what
            // cakephp/authentication would normally do.
            ->add(new DemoIdentityMiddleware())

            // Demo: role-level request gate. Walks the
            // tinyauth_controllers / tinyauth_actions matrix for every
            // request and 403s when the impersonated role cannot reach
            // the routed action. Real adopters should use
            // TinyAuth\Middleware\RequestAuthorizationMiddleware
            // instead — see RequestGateMiddleware's docblock for why
            // the demo rolls its own.
            ->add(new RequestGateMiddleware())

            // Authorization plugin runs entity-level policies. Used by
            // the FullBackend, NativeAuth, and ExternalRoles strategy
            // controllers. AdapterOnly controllers deliberately do not
            // load the Authorization component, so this middleware is
            // inert for them.
            ->add(new AuthorizationMiddleware($this, [
                'requireAuthorizationCheck' => false,
                'unauthorizedHandler' => [
                    'className' => 'Authorization.Redirect',
                    'url' => '/',
                ],
            ]))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/5/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            // Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/5/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
            ->add(new CsrfProtectionMiddleware([
                'httponly' => true,
            ]));

        return $middlewareQueue;
    }

    /**
     * Wire the Authorization plugin's service with the plugin-provided
     * `TinyAuthResolver`, which maps every known entity / table / query
     * to `TinyAuthBackend\Policy\TinyAuthPolicy`. The policy in turn
     * calls `TinyAuthBackend\Service\TinyAuthService` against live DB
     * rules.
     *
     * The allowlist is explicit so unrelated entities (error pages,
     * test fixtures, etc.) fall through to `MissingPolicyException`
     * instead of being silently governed by TinyAuth.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Authorization\AuthorizationServiceInterface
     */
    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $resolver = new TinyAuthResolver([
            Article::class,
            Project::class,
            ArticlesTable::class,
            ProjectsTable::class,
        ]);

        return new AuthorizationService($resolver);
    }

    /**
     * Register application container services.
     *
     * @link https://book.cakephp.org/5/en/development/dependency-injection.html#dependency-injection
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     *
     * @return void
     */
    public function services(ContainerInterface $container): void
    {
        // Allow your Tables to be dependency injected
        //$container->delegate(new \Cake\ORM\Locator\TableContainer());
    }

    /**
     * Register custom event listeners here
     *
     * @link https://book.cakephp.org/5/en/core-libraries/events.html#registering-listeners
     *
     * @param \Cake\Event\EventManagerInterface $eventManager
     *
     * @return \Cake\Event\EventManagerInterface
     */
    public function events(EventManagerInterface $eventManager): EventManagerInterface
    {
        // $eventManager->on(new SomeCustomListenerClass());

        return $eventManager;
    }
}
