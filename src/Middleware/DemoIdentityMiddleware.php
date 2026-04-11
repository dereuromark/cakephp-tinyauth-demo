<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Identity\DemoIdentity;
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
class DemoIdentityMiddleware implements MiddlewareInterface {

	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Server\RequestHandlerInterface $handler
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$session = $request->getAttribute('session');
		if (!$session) {
			/** @var \Cake\Http\ServerRequest $request */
			$session = $request->getSession();
		}

		$userId = $session->read('Auth.user_id');
		if ($userId) {
			$users = TableRegistry::getTableLocator()->get('Users');
			/** @var \Cake\Datasource\EntityInterface|null $user */
			$user = $users->find()->where(['Users.id' => (int)$userId])->first();
			if ($user) {
				$request = $request->withAttribute('identity', new DemoIdentity($user));
			}
		}

		return $handler->handle($request);
	}

}
