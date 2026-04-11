<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Service\Strategy;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Applies strategy-specific runtime wiring based on the active route
 * prefix.
 *
 * The four strategies differ in exactly three dimensions:
 *
 * 1. **Role source** — ExternalRoles points `TinyAuthBackend.roleSource`
 *    at a session-backed callable; everything else reads from the
 *    users table (default).
 * 2. **Authorization plugin** — AdapterOnly skips the Authorization
 *    plugin entirely (no `requireAuthorization` check, no policy
 *    resolution); everything else requires it. We can't remove the
 *    middleware from the queue here, but we can short-circuit the
 *    authorization-required check.
 * 3. **TinyAuth middleware** — NativeAuth relies on pure Authorization
 *    and has no TinyAuth request-level gating. Since this demo uses
 *    the DemoAuthComponent / session-based auth rather than TinyAuth's
 *    middleware in the first place, this dimension is a no-op here
 *    and is noted as a strategy description only.
 *
 * The middleware is intentionally small — all the real work happens
 * in the strategy-specific controller classes.
 */
class StrategyMiddleware implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $prefix = $request->getAttribute('params')['prefix'] ?? null;
        $slug = is_string($prefix) ? Strategy::slugForPrefix($prefix) : null;

        if ($slug === null) {
            return $handler->handle($request);
        }

        $request = $request->withAttribute('strategy', $slug);

        if ($slug === Strategy::EXTERNAL_ROLES) {
            $session = $request->getAttribute('session');
            if (!$session) {
                /** @var \Cake\Http\ServerRequest $request */
                $session = $request->getSession();
            }
            Configure::write('TinyAuthBackend.roleSource', static function () use ($session): array {
                $alias = $session->read('ExternalRoles.role');
                if (!$alias) {
                    return [];
                }
                $roles = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
                /** @var \Cake\ORM\Entity|null $row */
                $row = $roles->find()->where(['alias' => $alias])->first();
                if (!$row) {
                    return [];
                }

                return [$alias => (int)$row->get('id')];
            });
        } else {
            // Restore the default (DB-backed) role source for any
            // request not under the ExternalRoles subtree. Without
            // this reset the callable would leak across requests in
            // worker-mode runtimes (FrankenPHP / RoadRunner) where
            // Configure survives between requests.
            Configure::write('TinyAuthBackend.roleSource', null);
        }

        return $handler->handle($request);
    }
}
