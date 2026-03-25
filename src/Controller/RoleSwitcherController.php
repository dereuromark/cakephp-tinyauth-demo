<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Controller for switching simulated roles in demo mode.
 */
class RoleSwitcherController extends AppController {

	/**
	 * Switch to a different role.
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function switch(): ?Response {
		$roleId = (int)$this->request->getData('role_id');
		$roleName = $this->request->getData('role_name');

		if ($roleId > 0) {
			$this->request->getSession()->write('Auth.id', 1); // Fake user ID
			$this->request->getSession()->write('Auth.role_id', $roleId);
			$this->request->getSession()->write('Auth.role_name', $roleName);
			$this->Flash->success(__('Switched to role: {0}', $roleName));
		} else {
			// Clear session (logged out)
			$this->request->getSession()->delete('Auth');
			$this->Flash->success(__('Logged out (no role)'));
		}

		return $this->redirect('/');
	}

	/**
	 * Clear the current role (logout simulation).
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function clear(): ?Response {
		$this->request->getSession()->delete('Auth');
		$this->Flash->success(__('Session cleared'));

		return $this->redirect('/');
	}

}
