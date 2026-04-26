<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Response;
use Cake\Utility\Security;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Applies a strict Content-Security-Policy on every request and exposes
 * the request-scoped nonce as the `cspNonce` request attribute so
 * templates can mark inline scripts and inline `<style>` blocks as
 * trusted via `nonce="…"`.
 *
 * Strict here means: no `unsafe-eval`, no `unsafe-inline` (so inline
 * `style="…"` attributes and unnonced `<script>` / `<style>` blocks are
 * blocked). DebugKit's toolbar emits unnonced inline scripts/styles, so
 * it is intentionally disabled in the middleware queue when this
 * middleware is active — see Application::middleware().
 *
 * `unpkg.com` is allow-listed for the HTMX script that the
 * TinyAuthBackend layout loads.
 */
class StrictCspMiddleware implements MiddlewareInterface {

	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Server\RequestHandlerInterface $handler
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$nonce = base64_encode(Security::randomBytes(16));
		$request = $request->withAttribute('cspNonce', $nonce);

		$response = $handler->handle($request);

		$policy = sprintf(
			"default-src 'self'; "
			. "script-src 'self' 'nonce-%s' https://unpkg.com; "
			. "style-src 'self' 'nonce-%s'; "
			. "img-src 'self' data:; "
			. "connect-src 'self'; "
			. "font-src 'self' data:; "
			. "frame-ancestors 'none'; "
			. "base-uri 'self'; "
			. "form-action 'self'",
			$nonce,
			$nonce,
		);

		if ($response instanceof Response) {
			return $response->withHeader('Content-Security-Policy', $policy);
		}

		return $response->withHeader('Content-Security-Policy', $policy);
	}

}
