<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Reads the session-backed fake-login state written by the role
 * switcher and exposes it as an `identity` request attribute so the
 * Authorization plugin can pick it up.
 *
 * This replaces what cakephp/authentication's IdentityMiddleware does
 * in a production app — the demo skips real auth and lets visitors
 * impersonate any seeded user via the switcher.
 */
class DemoIdentityMiddleware implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');
        if (!$session) {
            /** @var \Cake\Http\ServerRequest $request */
            $session = $request->getSession();
        }

        $userId = (int)$session->read('Auth.user_id');
        if ($userId > 0) {
            $users = TableRegistry::getTableLocator()->get('Users');
            /** @var \Cake\Datasource\EntityInterface|null $user */
            $user = $users->find()->where(['Users.id' => $userId])->first();
            if ($user) {
                // Pass the raw entity — the Authorization plugin's
                // AuthorizationMiddleware wraps non-IdentityInterface
                // values in its configured decorator (and injects the
                // AuthorizationService). If we wrapped it ourselves,
                // the decoration is skipped and `applyScope()` has
                // no service to dispatch through.
                $request = $request->withAttribute('identity', $user);
            } else {
                // Stale session — the impersonated user was deleted
                // out from under us. Drop the whole Auth key so the
                // next request is a clean guest instead of a zombie
                // identity the UI keeps flashing up in the switcher.
                $session->delete('Auth');
            }
        }

        return $handler->handle($request);
    }
}
