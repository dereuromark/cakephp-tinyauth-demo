<?php

declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use TinyAuthBackend\Service\TinyAuthService;

/**
 * Demo-only helper component for resource-level permission checks
 * and session-derived identity lookups.
 *
 * ## Why this component exists
 *
 * A real adopter would normally get all of this from
 * `cakephp/authentication` (for the identity/user lookups) and from
 * calling `$this->Authorization->authorize(...)` directly on entities
 * (for the resource checks). The demo cannot use either cleanly
 * because it hosts all four usage strategies in one app — the
 * AdapterOnly rung deliberately runs without the Authorization
 * component loaded, and there's no real login at all; users are
 * impersonated via a session-backed switcher.
 *
 * This component fills both gaps for the non-strategy-prefixed
 * "classic" demo pages (Dashboard, Articles, Projects, Reports):
 *
 * - `getCurrentUser()` / `getCurrentRole()` — surface the
 *   impersonated user/role that `DemoIdentityMiddleware` wrote to
 *   the session. Equivalent to `$this->Authentication->getIdentity()`
 *   in a real app.
 * - `canAccessResource()` / `getScopeConditions()` — thin wrappers
 *   around `TinyAuthService` so templates and controllers can ask
 *   "can this user edit this article?" without wiring up the
 *   Authorization plugin's policy dispatch.
 *
 * ## What changed in this refactor
 *
 * Request-level authorization (the old `isAuthorized()` /
 * `requireAuthorization()` methods and the hand-rolled `parent_id`
 * hierarchy walk) moved to `App\Middleware\RequestGateMiddleware`,
 * which gates every request at the framework boundary instead of
 * requiring every controller action to call
 * `$this->DemoAuth->requireAuthorization()` manually.
 *
 * Real apps should not copy this file. Use `cakephp/authentication`
 * + `cakephp/authorization` + `TinyAuthBackend\Policy\TinyAuthPolicy`
 * instead — see `docs/` in the plugin repo for the canonical wiring.
 *
 * @method \App\Controller\AppController getController()
 */
class DemoAuthComponent extends Component
{
    /**
     * Get the current role info from session.
     *
     * @return array{id: int|null, name: string|null}
     */
    public function getCurrentRole(): array
    {
        $session = $this->getController()->getRequest()->getSession();

        return [
            'id' => $session->read('Auth.role_id'),
            'name' => $session->read('Auth.role_name'),
        ];
    }

    /**
     * Get the impersonated user entity built from session state.
     *
     * Real apps get this from `$this->Authentication->getIdentity()`.
     * The demo builds a stub entity with the minimum fields that
     * `TinyAuthService` scope evaluation needs (`id`, `role_id`,
     * `team_id`) because there's no real login flow.
     *
     * @return \Cake\ORM\Entity|null
     */
    public function getCurrentUser(): ?Entity
    {
        $session = $this->getController()->getRequest()->getSession();
        $roleId = $session->read('Auth.role_id');
        $userId = $session->read('Auth.user_id');
        $teamId = $session->read('Auth.team_id');

        if (!$roleId) {
            return null;
        }

        return new Entity([
            'id' => $userId ?? 1,
            'role_id' => $roleId,
            'team_id' => $teamId,
        ]);
    }

    /**
     * Get the current user's role alias for `TinyAuthService`.
     *
     * @return string|null
     */
    public function getCurrentRoleAlias(): ?string
    {
        $session = $this->getController()->getRequest()->getSession();
        $roleId = $session->read('Auth.role_id');

        if (!$roleId) {
            return null;
        }

        $rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
        $role = $rolesTable->find()->where(['id' => $roleId])->first();

        return $role ? $role->get('alias') : null;
    }

    /**
     * Check if the current user can perform an ability on a resource
     * entity. Thin wrapper around `TinyAuthService::canAccess()`.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to check access for.
     * @param string $ability The ability (view, edit, delete, etc.).
     * @param string $resourceName The resource name (Article, Project, etc.).
     *
     * @return bool
     */
    public function canAccessResource(
        EntityInterface $entity,
        string $ability,
        string $resourceName,
    ): bool {
        $roleAlias = $this->getCurrentRoleAlias();
        $user = $this->getCurrentUser();

        if (!$roleAlias || !$user) {
            return false;
        }

        return (new TinyAuthService())->canAccess($roleAlias, $resourceName, $ability, $entity, $user);
    }

    /**
     * Get scope conditions for query filtering. Thin wrapper around
     * `TinyAuthService::getScopeCondition()`.
     *
     * @param string $resourceName The resource name.
     * @param string $ability The ability.
     *
     * @return array<string, mixed>|null Null = no access, empty array = full access, array = conditions.
     */
    public function getScopeConditions(string $resourceName, string $ability): ?array
    {
        $roleAlias = $this->getCurrentRoleAlias();
        $user = $this->getCurrentUser();

        if (!$roleAlias || !$user) {
            return null;
        }

        return (new TinyAuthService())->getScopeCondition($roleAlias, $resourceName, $ability, $user);
    }
}
