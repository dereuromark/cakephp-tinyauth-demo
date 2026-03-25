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

}
