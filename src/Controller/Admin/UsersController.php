<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Admin Users Controller
 *
 * This controller requires admin role
 *
 * @property \App\Controller\Component\DemoAuthComponent $DemoAuth
 */
class UsersController extends AppController {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();
		$this->loadComponent('DemoAuth');
	}

	/**
	 * List all users
	 *
	 * @return void
	 */
	public function index(): void {
		$this->DemoAuth->requireAuthorization();
		$this->set('currentRole', $this->DemoAuth->getCurrentRole());
		$this->set('pageTitle', 'Admin: Users');
		// Mock users data for demo
		$this->set('users', []);
	}

	/**
	 * View a user
	 *
	 * @param string|null $id User id
	 * @return void
	 */
	public function view(?string $id = null): void {
		$this->DemoAuth->requireAuthorization();
		$this->set('currentRole', $this->DemoAuth->getCurrentRole());
		$this->set('pageTitle', 'View User');
	}

	/**
	 * Add a user
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function add(): ?\Cake\Http\Response {
		$this->DemoAuth->requireAuthorization();
		$this->set('currentRole', $this->DemoAuth->getCurrentRole());
		$this->set('pageTitle', 'Add User');

		return null;
	}

	/**
	 * Delete a user
	 *
	 * @param string|null $id User id
	 * @return \Cake\Http\Response|null
	 */
	public function delete(?string $id = null): ?\Cake\Http\Response {
		$this->DemoAuth->requireAuthorization();
		$this->request->allowMethod(['post', 'delete']);
		$this->Flash->error(__('Demo mode - delete disabled.'));

		return $this->redirect(['action' => 'index']);
	}

}
