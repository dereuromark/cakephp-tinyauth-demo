<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;

/**
 * Demo authorization component that checks permissions against TinyAuth database.
 *
 * This simulates how TinyAuth authorization works by reading the role from session
 * and checking against the acl_permissions table.
 */
class DemoAuthComponent extends Component {

	/**
	 * Check if the current user has permission to access the action.
	 *
	 * @param string|null $controller Controller name (defaults to current)
	 * @param string|null $action Action name (defaults to current)
	 * @return bool
	 */
	public function isAuthorized(?string $controller = null, ?string $action = null): bool {
		$request = $this->getController()->getRequest();
		$session = $request->getSession();

		$roleId = $session->read('Auth.role_id');
		if (!$roleId) {
			return false; // Not logged in
		}

		$controller = $controller ?? $request->getParam('controller');
		$action = $action ?? $request->getParam('action');
		$prefix = $request->getParam('prefix');
		$plugin = $request->getParam('plugin');

		// Find the controller in the database
		$controllersTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.TinyauthControllers');
		$controllerEntity = $controllersTable->find()
			->where([
				'name' => $controller,
				'prefix IS' => $prefix,
				'plugin IS' => $plugin,
			])
			->first();

		if (!$controllerEntity) {
			// Controller not found in DB - deny by default
			return false;
		}

		// Find the action
		$actionsTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Actions');
		$actionEntity = $actionsTable->find()
			->where([
				'controller_id' => $controllerEntity->id,
				'name' => $action,
			])
			->first();

		if (!$actionEntity) {
			// Action not found - deny by default
			return false;
		}

		// Check if action is public
		if ($actionEntity->is_public) {
			return true;
		}

		// Check permission
		$permissionsTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.AclPermissions');
		$permission = $permissionsTable->find()
			->where([
				'action_id' => $actionEntity->id,
				'role_id' => $roleId,
			])
			->first();

		if ($permission && $permission->type === 'allow') {
			return true;
		}

		// Check inherited permissions (parent roles)
		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
		$role = $rolesTable->get($roleId);

		if ($role->parent_id) {
			// Check parent role permission recursively
			return $this->checkParentPermission($actionEntity->id, $role->parent_id, $permissionsTable, $rolesTable);
		}

		return false;
	}

	/**
	 * Check parent role permissions recursively.
	 *
	 * @param int $actionId Action ID
	 * @param int $parentRoleId Parent role ID
	 * @param \Cake\ORM\Table $permissionsTable Permissions table
	 * @param \Cake\ORM\Table $rolesTable Roles table
	 * @return bool
	 */
	protected function checkParentPermission(int $actionId, int $parentRoleId, $permissionsTable, $rolesTable): bool {
		$permission = $permissionsTable->find()
			->where([
				'action_id' => $actionId,
				'role_id' => $parentRoleId,
			])
			->first();

		if ($permission && $permission->type === 'allow') {
			return true;
		}

		$parentRole = $rolesTable->find()->where(['id' => $parentRoleId])->first();
		if ($parentRole && $parentRole->parent_id) {
			return $this->checkParentPermission($actionId, $parentRole->parent_id, $permissionsTable, $rolesTable);
		}

		return false;
	}

	/**
	 * Require authorization - throws exception if not authorized.
	 *
	 * @param string|null $controller Controller name
	 * @param string|null $action Action name
	 * @throws \Cake\Http\Exception\ForbiddenException
	 * @return void
	 */
	public function requireAuthorization(?string $controller = null, ?string $action = null): void {
		if (!$this->isAuthorized($controller, $action)) {
			$session = $this->getController()->getRequest()->getSession();
			$roleId = $session->read('Auth.role_id');

			if (!$roleId) {
				throw new ForbiddenException('You must be logged in to access this page. Select a role on the homepage.');
			}

			throw new ForbiddenException('You do not have permission to access this page with your current role.');
		}
	}

	/**
	 * Get the current role info from session.
	 *
	 * @return array{id: int|null, name: string|null}
	 */
	public function getCurrentRole(): array {
		$session = $this->getController()->getRequest()->getSession();

		return [
			'id' => $session->read('Auth.role_id'),
			'name' => $session->read('Auth.role_name'),
		];
	}

	/**
	 * Get the current simulated user entity.
	 *
	 * Creates a user-like entity with role info for resource permission checks.
	 *
	 * @return \Cake\ORM\Entity|null
	 */
	public function getCurrentUser(): ?\Cake\ORM\Entity {
		$session = $this->getController()->getRequest()->getSession();
		$roleId = $session->read('Auth.role_id');
		$userId = $session->read('Auth.user_id');
		$teamId = $session->read('Auth.team_id');

		if (!$roleId) {
			return null;
		}

		// Create a simple entity to represent the user
		$user = new \Cake\ORM\Entity([
			'id' => $userId ?? 1, // Default user ID for demo
			'role_id' => $roleId,
			'team_id' => $teamId,
		]);

		return $user;
	}

	/**
	 * Get the current user's role alias for TinyAuthService.
	 *
	 * @return string|null
	 */
	public function getCurrentRoleAlias(): ?string {
		$session = $this->getController()->getRequest()->getSession();
		$roleId = $session->read('Auth.role_id');

		if (!$roleId) {
			return null;
		}

		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
		$role = $rolesTable->find()->where(['id' => $roleId])->first();

		return $role ? $role->alias : null;
	}

	/**
	 * Check if current user can access a resource with given ability.
	 *
	 * Uses TinyAuthService to check resource-level permissions with scopes.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity to check access for
	 * @param string $ability The ability (view, edit, delete, etc.)
	 * @param string $resourceName The resource name (Article, Project, etc.)
	 * @return bool
	 */
	public function canAccessResource(
		\Cake\Datasource\EntityInterface $entity,
		string $ability,
		string $resourceName,
	): bool {
		$roleAlias = $this->getCurrentRoleAlias();
		$user = $this->getCurrentUser();

		if (!$roleAlias || !$user) {
			return false;
		}

		$service = new \TinyAuthBackend\Service\TinyAuthService();

		return $service->canAccess($roleAlias, $resourceName, $ability, $entity, $user);
	}

	/**
	 * Get scope conditions for query filtering.
	 *
	 * @param string $resourceName The resource name
	 * @param string $ability The ability
	 * @return array|null Null = no access, empty = full access, array = conditions
	 */
	public function getScopeConditions(string $resourceName, string $ability): ?array {
		$roleAlias = $this->getCurrentRoleAlias();
		$user = $this->getCurrentUser();

		if (!$roleAlias || !$user) {
			return null;
		}

		$service = new \TinyAuthBackend\Service\TinyAuthService();

		return $service->getScopeCondition($roleAlias, $resourceName, $ability, $user);
	}

}
