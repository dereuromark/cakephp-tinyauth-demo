<?php

declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyAuthBackend\Service\HierarchyService;

/**
 * Demo-only request-level authorization gate.
 *
 * ## Why this middleware exists at all
 *
 * A real adopter would **not** need to write this. TinyAuth upstream
 * already ships `TinyAuth\Middleware\RequestAuthorizationMiddleware`,
 * which consumes the same `DbAllowAdapter` + `DbAclAdapter` this demo
 * configures in `config/bootstrap.php` and will happily gate every
 * request against the `tinyauth_*` tables. Three-line install, done.
 *
 * The demo can't use that middleware because it deliberately hosts all
 * four usage strategies (AdapterOnly, FullBackend, NativeAuth,
 * ExternalRoles) in one app. Each strategy wants a different runtime
 * enforcement pattern, but a global middleware can only pick one. So
 * the demo implements its own lightweight gate here, scoped by session
 * role, and the four strategy controllers add their own entity-level
 * checks (or not) on top.
 *
 * If you are copying code out of this demo for a real project, reach
 * for `TinyAuth\Middleware\RequestAuthorizationMiddleware` first. Only
 * write a custom middleware like this one if you genuinely cannot load
 * `cakephp/authorization` — in which case the 30-line happy path below
 * is a reasonable starting template.
 *
 * ## What it does
 *
 * 1. Reads the impersonated role from session (written by the demo's
 *    role switcher — see `DemoIdentityMiddleware`).
 * 2. Looks up the routed controller + action in `tinyauth_controllers`
 *    / `tinyauth_actions`.
 * 3. Skips public actions (`is_public = true`) — unauthenticated
 *    requests are allowed through.
 * 4. Walks role hierarchy via `TinyAuthBackend\Service\HierarchyService`
 *    so a role inherits its parents' permissions.
 * 5. Returns 403 on deny, passes through on allow.
 *
 * Requests that fall outside the `tinyauth_controllers` matrix (the
 * plugin's own `/admin/auth` admin UI, static `/pages/*`, the root
 * demo controller) fall through unchanged — the backend plugin gates
 * its own admin via `TinyAuthBackend.editorCheck`, and the public
 * demo pages are intentionally open.
 */
class RequestGateMiddleware implements MiddlewareInterface
{
    /**
     * Plugin names that are explicitly skipped. `TinyAuthBackend`
     * ships its own authorization hook (`TinyAuthBackend.editorCheck`)
     * for its `/admin/auth` UI and double-gating it here would
     * conflict with the plugin's own deny/allow semantics.
     *
     * @var array<string>
     */
    protected const SKIPPED_PLUGINS = ['TinyAuthBackend'];

    /**
     * Route prefixes that are skipped because their controller
     * subtrees each implement a *different* enforcement pattern and
     * shouldn't share the same request-level gate.
     *
     * - AdapterOnly: pedagogically has no enforcement (that's the
     *   contrast it teaches — "swap INI for DB admin UI, nothing else
     *   changes"). Request gating would change its meaning.
     * - FullBackend / NativeAuth / ExternalRoles: enforce at
     *   entity level via `$this->Authorization->authorize()` and
     *   `->applyScope()`. Adding a matrix-based request gate on top
     *   would either duplicate the check or deny requests the
     *   entity-level policy would have allowed (e.g. a list view
     *   that returns an empty scope-narrowed result).
     *
     * The middleware therefore only gates the non-prefixed "classic"
     * demo pages (Dashboard, Articles, Projects, Reports) and the
     * demo's own `Admin/Users` page — which is exactly the set of
     * controllers that used to call
     * `$this->DemoAuth->requireAuthorization()` before this refactor.
     *
     * @var array<string>
     */
    protected const SKIPPED_PREFIXES = ['AdapterOnly', 'FullBackend', 'NativeAuth', 'ExternalRoles'];

    /**
     * Controller names that are explicitly public regardless of any
     * row in `tinyauth_actions`. These are the demo's entry points
     * and must stay reachable for unauthenticated visitors.
     *
     * @var array<string>
     */
    protected const PUBLIC_CONTROLLERS = [
        'Demo',
        'Pages',
        'Strategy',
        'RoleSwitcher',
        'FeatureSwitcher',
        'ExternalRoleSwitcher',
        'Error',
    ];

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @throws \Cake\Http\Exception\ForbiddenException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Routing may not have run yet for some edge cases (asset
        // requests, error handling, etc.) — in that case let them
        // fall through untouched.
        $params = (array)$request->getAttribute('params');
        if (!isset($params['controller'], $params['action'])) {
            return $handler->handle($request);
        }

        $plugin = $params['plugin'] ?? null;
        if (is_string($plugin) && in_array($plugin, self::SKIPPED_PLUGINS, true)) {
            return $handler->handle($request);
        }

        $prefix = $params['prefix'] ?? null;
        if (is_string($prefix) && in_array($prefix, self::SKIPPED_PREFIXES, true)) {
            return $handler->handle($request);
        }

        $controllerName = (string)$params['controller'];
        if (in_array($controllerName, self::PUBLIC_CONTROLLERS, true)) {
            return $handler->handle($request);
        }

        $session = $request->getAttribute('session') ?? Router::getRequest()?->getSession();
        $roleId = $session?->read('Auth.role_id');

        // Look up the action row so we can honor `is_public` before
        // deciding whether a role is even required.
        $actionRow = $this->findAction($controllerName, (string)$params['action'], $prefix, $plugin);
        if ($actionRow === null) {
            // Controller/action not tracked in the matrix yet. Deny by
            // default so forgotten routes don't become an open door —
            // matches what TinyAuth upstream's middleware does.
            throw new ForbiddenException(
                'This action is not registered in the TinyAuth matrix. Run `bin/cake tiny_auth_backend sync`.',
            );
        }

        if ($actionRow['is_public']) {
            return $handler->handle($request);
        }

        if (!$roleId) {
            throw new ForbiddenException(
                'You must pick a role on the homepage before accessing this page.',
            );
        }

        if (!$this->roleCanReach((int)$roleId, (int)$actionRow['id'])) {
            throw new ForbiddenException(
                'Your current role is not allowed to access this page.',
            );
        }

        return $handler->handle($request);
    }

    /**
     * Find the `tinyauth_actions` row matching the current route.
     *
     * @param string $controller
     * @param string $action
     * @param string|null $prefix
     * @param string|null $plugin
     *
     * @return array{id: int, is_public: bool}|null
     */
    protected function findAction(string $controller, string $action, ?string $prefix, ?string $plugin): ?array
    {
        $controllersTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.TinyauthControllers');

        // Plugin `null` and plugin `'App'` both mean "app-level controller"
        // in this demo, depending on how the request was routed — match
        // both shapes to be robust across Cake routing quirks.
        $pluginConditions = $plugin === null
            ? ['OR' => ['TinyauthControllers.plugin IS' => null, 'TinyauthControllers.plugin' => 'App']]
            : ['TinyauthControllers.plugin' => $plugin];

        $controllerEntity = $controllersTable->find()
            ->where($pluginConditions + [
                'TinyauthControllers.name' => $controller,
                'TinyauthControllers.prefix IS' => $prefix,
            ])
            ->first();

        if (!$controllerEntity) {
            return null;
        }

        $actionsTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Actions');
        $actionEntity = $actionsTable->find()
            ->where([
                'Actions.controller_id' => $controllerEntity->id,
                'Actions.name' => $action,
            ])
            ->first();

        if (!$actionEntity) {
            return null;
        }

        return [
            'id' => (int)$actionEntity->id,
            'is_public' => (bool)$actionEntity->is_public,
        ];
    }

    /**
     * Check whether the given role (by id) — or any of its parents in
     * the hierarchy — has an `allow` permission for the given action.
     *
     * @param int $roleId
     * @param int $actionId
     *
     * @return bool
     */
    protected function roleCanReach(int $roleId, int $actionId): bool
    {
        $rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
        /** @var \Cake\Datasource\EntityInterface|null $role */
        $role = $rolesTable->find()->where(['Roles.id' => $roleId])->first();
        if (!$role) {
            return false;
        }

        // Build the set of role aliases to test: the current role plus
        // its parent chain. HierarchyService walks the parent_id pointers
        // for us — this is the same method the admin UI matrix uses to
        // render inherited permissions, so the runtime and the UI agree.
        $roleAlias = (string)$role->get('alias');
        $aliasesToCheck = [$roleAlias, ...(new HierarchyService())->getParentRoles($roleAlias)];

        // Resolve those aliases back to IDs for a single IN query.
        $roleIds = $rolesTable->find()
            ->select(['Roles.id'])
            ->where(['Roles.alias IN' => $aliasesToCheck])
            ->all()
            ->extract('id')
            ->toList();

        if ($roleIds === []) {
            return false;
        }

        $permissionsTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.AclPermissions');
        $allowCount = $permissionsTable->find()
            ->where([
                'AclPermissions.action_id' => $actionId,
                'AclPermissions.role_id IN' => $roleIds,
                'AclPermissions.type' => 'allow',
            ])
            ->count();

        return $allowCount > 0;
    }
}
