<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Seed demo data for TinyAuth demonstration.
 */
class SeedDemoDataCommand extends Command {

	/**
	 * @param \Cake\Console\ConsoleOptionParser $parser
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser->setDescription('Seed demo data for TinyAuth demonstration');

		return $parser;
	}

	/**
	 * @param \Cake\Console\Arguments $args
	 * @param \Cake\Console\ConsoleIo $io
	 * @return int
	 */
	public function execute(Arguments $args, ConsoleIo $io): int {
		$this->seedRoles($io);
		$this->seedScopes($io);
		$this->seedPublicActions($io);
		$this->seedTeams($io);
		$this->seedUsers($io);
		$this->seedResources($io);
		$this->seedControllerAcl($io);
		$this->seedSampleArticles($io);
		$this->seedSampleProjects($io);

		$io->success('Demo data seeded successfully!');

		return static::CODE_SUCCESS;
	}

	/**
	 * Seed roles.
	 *
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function seedRoles(ConsoleIo $io): void {
		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');

		$roles = [
			['name' => 'user', 'alias' => 'User', 'sort_order' => 3, 'description' => 'Regular user with basic access'],
			['name' => 'moderator', 'alias' => 'Moderator', 'sort_order' => 2, 'description' => 'Can moderate content and access reports'],
			['name' => 'admin', 'alias' => 'Administrator', 'sort_order' => 1, 'description' => 'Full administrative access'],
		];

		foreach ($roles as $roleData) {
			$existing = $rolesTable->find()->where(['name' => $roleData['name']])->first();
			if (!$existing) {
				$role = $rolesTable->newEntity($roleData);
				if ($rolesTable->save($role)) {
					$io->out("  Created role: {$roleData['name']}");
				}
			} else {
				$io->out("  Role exists: {$roleData['name']}");
			}
		}
	}

	/**
	 * Seed scopes.
	 *
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function seedScopes(ConsoleIo $io): void {
		$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');

		$scopes = [
			[
				'name' => 'own',
				'description' => 'User owns the entity (entity.user_id = user.id)',
				'entity_field' => 'user_id',
				'user_field' => 'id',
			],
			[
				'name' => 'team',
				'description' => 'User is on the same team (entity.team_id = user.team_id)',
				'entity_field' => 'team_id',
				'user_field' => 'team_id',
			],
			[
				'name' => 'department',
				'description' => 'User is in the same department (entity.department_id = user.department_id)',
				'entity_field' => 'department_id',
				'user_field' => 'department_id',
			],
			[
				'name' => 'company',
				'description' => 'User is in the same company (entity.company_id = user.company_id)',
				'entity_field' => 'company_id',
				'user_field' => 'company_id',
			],
		];

		foreach ($scopes as $scopeData) {
			$existing = $scopesTable->find()->where(['name' => $scopeData['name']])->first();
			if (!$existing) {
				$scope = $scopesTable->newEntity($scopeData);
				if ($scopesTable->save($scope)) {
					$io->out("  Created scope: {$scopeData['name']}");
				}
			} else {
				$io->out("  Scope exists: {$scopeData['name']}");
			}
		}
	}

	/**
	 * Seed public actions.
	 *
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function seedPublicActions(ConsoleIo $io): void {
		$actionsTable = $this->fetchTable('TinyAuthBackend.Actions');

		// Make certain actions public
		$publicActions = [
			['controller' => 'Dashboard', 'action' => 'index'],
			['controller' => 'Dashboard', 'action' => 'stats'],
			['controller' => 'Pages', 'action' => 'display'],
			['controller' => 'Reports', 'action' => 'usage'],
		];

		foreach ($publicActions as $actionData) {
			$action = $actionsTable->find()
				->matching('TinyauthControllers', function ($q) use ($actionData) {
					return $q->where(['TinyauthControllers.name' => $actionData['controller']]);
				})
				->where(['Actions.name' => $actionData['action']])
				->first();

			if ($action && !$action->is_public) {
				$action->is_public = true;
				if ($actionsTable->save($action)) {
					$io->out("  Made public: {$actionData['controller']}::{$actionData['action']}");
				}
			}
		}
	}

	/**
	 * Seed demo teams.
	 *
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function seedTeams(ConsoleIo $io): void {
		$teamsTable = $this->fetchTable('Teams');

		$teams = [
			['name' => 'Engineering'],
			['name' => 'Marketing'],
			['name' => 'Sales'],
		];

		foreach ($teams as $teamData) {
			$existing = $teamsTable->find()->where(['name' => $teamData['name']])->first();
			if (!$existing) {
				$team = $teamsTable->newEntity($teamData);
				if ($teamsTable->save($team)) {
					$io->out("  Created team: {$teamData['name']}");
				}
			} else {
				$io->out("  Team exists: {$teamData['name']}");
			}
		}
	}

	/**
	 * Seed demo users with team assignments.
	 *
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function seedUsers(ConsoleIo $io): void {
		$usersTable = $this->fetchTable('Users');
		$teamsTable = $this->fetchTable('Teams');
		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');

		// Get team IDs
		$teams = $teamsTable->find()->all()->combine('name', 'id')->toArray();
		$roles = $rolesTable->find()->all()->combine('name', 'id')->toArray();

		$users = [
			// User 1 - Regular user in Engineering team
			[
				'username' => 'alice',
				'email' => 'alice@example.com',
				'password' => 'password',
				'role_id' => $roles['user'] ?? 1,
				'team_id' => $teams['Engineering'] ?? 1,
			],
			// User 2 - Another user in Engineering team (same team as Alice)
			[
				'username' => 'bob',
				'email' => 'bob@example.com',
				'password' => 'password',
				'role_id' => $roles['user'] ?? 1,
				'team_id' => $teams['Engineering'] ?? 1,
			],
			// User 3 - User in Marketing team (different team)
			[
				'username' => 'charlie',
				'email' => 'charlie@example.com',
				'password' => 'password',
				'role_id' => $roles['user'] ?? 1,
				'team_id' => $teams['Marketing'] ?? 2,
			],
			// User 4 - Moderator in Sales team
			[
				'username' => 'diana',
				'email' => 'diana@example.com',
				'password' => 'password',
				'role_id' => $roles['moderator'] ?? 2,
				'team_id' => $teams['Sales'] ?? 3,
			],
			// User 5 - Admin (no team)
			[
				'username' => 'admin',
				'email' => 'admin@example.com',
				'password' => 'password',
				'role_id' => $roles['admin'] ?? 3,
				'team_id' => null,
			],
		];

		foreach ($users as $userData) {
			$existing = $usersTable->find()->where(['username' => $userData['username']])->first();
			if (!$existing) {
				$user = $usersTable->newEntity($userData);
				if ($usersTable->save($user)) {
					$io->out("  Created user: {$userData['username']}");
				}
			} else {
				// Update team_id if missing
				if ($existing->team_id === null && $userData['team_id'] !== null) {
					$existing->team_id = $userData['team_id'];
					if ($usersTable->save($existing)) {
						$io->out("  Updated user team: {$userData['username']}");
					}
				} else {
					$io->out("  User exists: {$userData['username']}");
				}
			}
		}
	}

	/**
	 * Seed resources with abilities and permissions.
	 *
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function seedResources(ConsoleIo $io): void {
		$resourcesTable = $this->fetchTable('TinyAuthBackend.Resources');
		$abilitiesTable = $this->fetchTable('TinyAuthBackend.ResourceAbilities');
		$aclTable = $this->fetchTable('TinyAuthBackend.ResourceAcl');
		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
		$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');

		// Get roles and scopes
		$roles = $rolesTable->find()->all()->combine('alias', 'id')->toArray();
		$scopes = $scopesTable->find()->all()->combine('name', 'id')->toArray();

		// Resource definitions
		$resources = [
			[
				'name' => 'Article',
				'entity_class' => 'App\Model\Entity\Article',
				'table_name' => 'articles',
				'abilities' => ['view', 'edit', 'delete', 'publish'],
				'permissions' => [
					// User: view all, edit/delete own only
					['role' => 'User', 'ability' => 'view', 'type' => 'allow', 'scope' => null],
					['role' => 'User', 'ability' => 'edit', 'type' => 'allow', 'scope' => 'own'],
					['role' => 'User', 'ability' => 'delete', 'type' => 'allow', 'scope' => 'own'],
					// Moderator: full access to view/edit, delete own only
					['role' => 'Moderator', 'ability' => 'view', 'type' => 'allow', 'scope' => null],
					['role' => 'Moderator', 'ability' => 'edit', 'type' => 'allow', 'scope' => null],
					['role' => 'Moderator', 'ability' => 'delete', 'type' => 'allow', 'scope' => 'own'],
					['role' => 'Moderator', 'ability' => 'publish', 'type' => 'allow', 'scope' => null],
					// Admin: full access to everything
					['role' => 'Administrator', 'ability' => 'view', 'type' => 'allow', 'scope' => null],
					['role' => 'Administrator', 'ability' => 'edit', 'type' => 'allow', 'scope' => null],
					['role' => 'Administrator', 'ability' => 'delete', 'type' => 'allow', 'scope' => null],
					['role' => 'Administrator', 'ability' => 'publish', 'type' => 'allow', 'scope' => null],
				],
			],
			[
				'name' => 'Project',
				'entity_class' => 'App\Model\Entity\Project',
				'table_name' => 'projects',
				'abilities' => ['view', 'edit', 'delete'],
				'permissions' => [
					// User: view team's projects, edit own only
					['role' => 'User', 'ability' => 'view', 'type' => 'allow', 'scope' => 'team'],
					['role' => 'User', 'ability' => 'edit', 'type' => 'allow', 'scope' => 'own'],
					// Moderator: view all, edit team's, delete own
					['role' => 'Moderator', 'ability' => 'view', 'type' => 'allow', 'scope' => null],
					['role' => 'Moderator', 'ability' => 'edit', 'type' => 'allow', 'scope' => 'team'],
					['role' => 'Moderator', 'ability' => 'delete', 'type' => 'allow', 'scope' => 'own'],
					// Admin: full access
					['role' => 'Administrator', 'ability' => 'view', 'type' => 'allow', 'scope' => null],
					['role' => 'Administrator', 'ability' => 'edit', 'type' => 'allow', 'scope' => null],
					['role' => 'Administrator', 'ability' => 'delete', 'type' => 'allow', 'scope' => null],
				],
			],
		];

		foreach ($resources as $resourceData) {
			// Create resource
			$resource = $resourcesTable->find()->where(['name' => $resourceData['name']])->first();
			if (!$resource) {
				$resource = $resourcesTable->newEntity([
					'name' => $resourceData['name'],
					'entity_class' => $resourceData['entity_class'],
					'table_name' => $resourceData['table_name'],
				]);
				if ($resourcesTable->save($resource)) {
					$io->out("  Created resource: {$resourceData['name']}");
				}
			} else {
				$io->out("  Resource exists: {$resourceData['name']}");
			}

			// Create abilities
			$abilityIds = [];
			foreach ($resourceData['abilities'] as $abilityName) {
				$ability = $abilitiesTable->find()
					->where(['resource_id' => $resource->id, 'name' => $abilityName])
					->first();

				if (!$ability) {
					$ability = $abilitiesTable->newEntity([
						'resource_id' => $resource->id,
						'name' => $abilityName,
					]);
					if ($abilitiesTable->save($ability)) {
						$io->out("    Created ability: {$abilityName}");
					}
				}
				$abilityIds[$abilityName] = $ability->id;
			}

			// Create permissions
			foreach ($resourceData['permissions'] as $permData) {
				$roleId = $roles[$permData['role']] ?? null;
				$abilityId = $abilityIds[$permData['ability']] ?? null;
				$scopeId = $permData['scope'] ? ($scopes[$permData['scope']] ?? null) : null;

				if (!$roleId || !$abilityId) {
					continue;
				}

				$existingPerm = $aclTable->find()
					->where(['resource_ability_id' => $abilityId, 'role_id' => $roleId])
					->first();

				if (!$existingPerm) {
					$perm = $aclTable->newEntity([
						'resource_ability_id' => $abilityId,
						'role_id' => $roleId,
						'type' => $permData['type'],
						'scope_id' => $scopeId,
					]);
					if ($aclTable->save($perm)) {
						$scopeName = $permData['scope'] ?? 'no scope';
						$io->out("    Created permission: {$permData['role']} can {$permData['ability']} ({$scopeName})");
					}
				}
			}
		}
	}

	/**
	 * Seed controller ACL permissions.
	 *
	 * Sets up proper controller/action permissions for the demo:
	 * - Dashboard: all authenticated users (user, moderator, admin)
	 * - Reports: moderator and admin only
	 * - Admin/Users: admin only
	 *
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function seedControllerAcl(ConsoleIo $io): void {
		$actionsTable = $this->fetchTable('TinyAuthBackend.Actions');
		$controllersTable = $this->fetchTable('TinyAuthBackend.TinyauthControllers');
		$aclTable = $this->fetchTable('TinyAuthBackend.AclPermissions');
		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');

		// Get roles
		$roles = $rolesTable->find()->all()->combine('name', 'id')->toArray();

		// Define ACL permissions: controller => action => [allowed roles]
		$permissions = [
			// Dashboard: all authenticated users
			'Dashboard' => [
				'index' => ['user', 'moderator', 'admin'],
				'stats' => ['user', 'moderator', 'admin'],
			],
			// Reports: moderator and admin only
			'Reports' => [
				'index' => ['moderator', 'admin'],
				'usage' => ['moderator', 'admin'],
				'audit' => ['moderator', 'admin'],
			],
			// Admin/Users: admin only
			'Admin/Users' => [
				'index' => ['admin'],
			],
			// RoleSwitcher: all roles for demo purposes
			'RoleSwitcher' => [
				'switch' => ['user', 'moderator', 'admin'],
				'switchUser' => ['user', 'moderator', 'admin'],
				'clear' => ['user', 'moderator', 'admin'],
			],
			// Articles: all authenticated users
			'Articles' => [
				'index' => ['user', 'moderator', 'admin'],
				'view' => ['user', 'moderator', 'admin'],
				'edit' => ['user', 'moderator', 'admin'],
				'delete' => ['user', 'moderator', 'admin'],
			],
			// Projects: all authenticated users
			'Projects' => [
				'index' => ['user', 'moderator', 'admin'],
				'view' => ['user', 'moderator', 'admin'],
				'edit' => ['user', 'moderator', 'admin'],
				'delete' => ['user', 'moderator', 'admin'],
			],
		];

		foreach ($permissions as $controllerName => $actions) {
			// Handle prefixed controllers (e.g., 'Admin/Users' => prefix='Admin', name='Users')
			$prefix = null;
			$name = $controllerName;
			if (str_contains($controllerName, '/')) {
				[$prefix, $name] = explode('/', $controllerName, 2);
			}

			$query = $controllersTable->find()->where(['name' => $name]);
			if ($prefix) {
				$query->where(['prefix' => $prefix]);
			} else {
				$query->where(['prefix IS' => null]);
			}
			$controller = $query->first();

			if (!$controller) {
				$io->warning("  Controller not found: {$controllerName} (sync controllers first)");
				continue;
			}

			foreach ($actions as $actionName => $allowedRoles) {
				$action = $actionsTable->find()
					->where(['controller_id' => $controller->id, 'name' => $actionName])
					->first();

				if (!$action) {
					$io->warning("  Action not found: {$controllerName}::{$actionName}");
					continue;
				}

				// Set permissions for each role
				foreach ($roles as $roleName => $roleId) {
					$existing = $aclTable->find()
						->where(['action_id' => $action->id, 'role_id' => $roleId])
						->first();

					$shouldAllow = in_array($roleName, $allowedRoles);

					if ($existing) {
						// Update if needed
						if ($shouldAllow && $existing->type !== 'allow') {
							$existing->type = 'allow';
							$aclTable->save($existing);
							$io->out("  Updated: {$controllerName}::{$actionName} - {$roleName} = allow");
						} elseif (!$shouldAllow && $existing->type === 'allow') {
							$aclTable->delete($existing);
							$io->out("  Removed: {$controllerName}::{$actionName} - {$roleName}");
						}
					} elseif ($shouldAllow) {
						// Create new permission
						$perm = $aclTable->newEntity([
							'action_id' => $action->id,
							'role_id' => $roleId,
							'type' => 'allow',
						]);
						if ($aclTable->save($perm)) {
							$io->out("  Created: {$controllerName}::{$actionName} - {$roleName} = allow");
						}
					}
				}
			}
		}
	}

	/**
	 * Seed sample articles for demonstration.
	 *
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function seedSampleArticles(ConsoleIo $io): void {
		$articlesTable = $this->fetchTable('Articles');
		$usersTable = $this->fetchTable('Users');

		$users = $usersTable->find()->all()->combine('username', 'id')->toArray();

		$articles = [
			// Alice's articles
			['user_id' => $users['alice'] ?? 1, 'title' => 'Getting Started with CakePHP', 'body' => 'A beginner guide to CakePHP framework.', 'status' => 'published'],
			['user_id' => $users['alice'] ?? 1, 'title' => 'Alice\'s Draft Post', 'body' => 'This is a draft article.', 'status' => 'draft'],
			// Bob's articles
			['user_id' => $users['bob'] ?? 2, 'title' => 'Understanding TinyAuth', 'body' => 'How to use TinyAuth for authorization.', 'status' => 'published'],
			['user_id' => $users['bob'] ?? 2, 'title' => 'Bob\'s Private Notes', 'body' => 'Personal notes that only Bob should edit.', 'status' => 'draft'],
			// Charlie's articles
			['user_id' => $users['charlie'] ?? 3, 'title' => 'Marketing Strategies 2024', 'body' => 'Top marketing strategies for the year.', 'status' => 'published'],
			// Diana's articles (moderator)
			['user_id' => $users['diana'] ?? 4, 'title' => 'Moderator Guidelines', 'body' => 'How to moderate content effectively.', 'status' => 'published'],
		];

		foreach ($articles as $articleData) {
			$existing = $articlesTable->find()
				->where(['title' => $articleData['title'], 'user_id' => $articleData['user_id']])
				->first();

			if (!$existing) {
				$article = $articlesTable->newEntity($articleData);
				if ($articlesTable->save($article)) {
					$io->out("  Created article: {$articleData['title']}");
				}
			} else {
				$io->out("  Article exists: {$articleData['title']}");
			}
		}
	}

	/**
	 * Seed sample projects for team scope demonstration.
	 *
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function seedSampleProjects(ConsoleIo $io): void {
		$projectsTable = $this->fetchTable('Projects');
		$usersTable = $this->fetchTable('Users');
		$teamsTable = $this->fetchTable('Teams');

		$users = $usersTable->find()->all()->combine('username', 'id')->toArray();
		$teams = $teamsTable->find()->all()->combine('name', 'id')->toArray();

		$projects = [
			// Engineering team projects (Alice & Bob are on this team)
			['user_id' => $users['alice'] ?? 1, 'team_id' => $teams['Engineering'] ?? 1, 'name' => 'API Redesign', 'description' => 'Redesign the REST API for better performance.'],
			['user_id' => $users['bob'] ?? 2, 'team_id' => $teams['Engineering'] ?? 1, 'name' => 'Database Migration', 'description' => 'Migrate from MySQL to PostgreSQL.'],
			['user_id' => $users['alice'] ?? 1, 'team_id' => $teams['Engineering'] ?? 1, 'name' => 'CI/CD Pipeline', 'description' => 'Set up automated testing and deployment.'],
			// Marketing team projects (Charlie is on this team)
			['user_id' => $users['charlie'] ?? 3, 'team_id' => $teams['Marketing'] ?? 2, 'name' => 'Brand Refresh', 'description' => 'Update company branding and logo.'],
			['user_id' => $users['charlie'] ?? 3, 'team_id' => $teams['Marketing'] ?? 2, 'name' => 'Social Media Campaign', 'description' => 'Q2 social media marketing campaign.'],
			// Sales team projects (Diana is on this team)
			['user_id' => $users['diana'] ?? 4, 'team_id' => $teams['Sales'] ?? 3, 'name' => 'CRM Integration', 'description' => 'Integrate new CRM system with existing tools.'],
		];

		foreach ($projects as $projectData) {
			$existing = $projectsTable->find()
				->where(['name' => $projectData['name']])
				->first();

			if (!$existing) {
				$project = $projectsTable->newEntity($projectData);
				if ($projectsTable->save($project)) {
					$io->out("  Created project: {$projectData['name']}");
				}
			} else {
				$io->out("  Project exists: {$projectData['name']}");
			}
		}
	}

}
