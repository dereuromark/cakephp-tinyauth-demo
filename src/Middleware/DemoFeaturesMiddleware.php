<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyAuthBackend\Service\FeatureService;

/**
 * Middleware to apply demo feature toggles from session.
 *
 * Reads TinyAuthFeatures from session and writes to Configure
 * so FeatureService picks up the overrides.
 */
class DemoFeaturesMiddleware implements MiddlewareInterface
{
    /**
     * Process the request and apply feature overrides.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request instanceof ServerRequest) {
            $session = $request->getSession();
            $features = $session->read('TinyAuthFeatures');

            if ($features !== null && is_array($features)) {
                // Merge with any existing config
                $existing = Configure::read('TinyAuthBackend.features') ?? [];
                Configure::write('TinyAuthBackend.features', array_merge($existing, $features));

                // Clear the FeatureService cache so it picks up new values
                (new FeatureService())->clearCache();
            }
        }

        return $handler->handle($request);
    }
}
