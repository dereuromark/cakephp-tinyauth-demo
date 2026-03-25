<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Dashboard Controller
 *
 * This controller requires authentication (any logged-in user can access)
 *
 * @property \App\Controller\Component\DemoAuthComponent $DemoAuth
 */
class DashboardController extends AppController {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();
		$this->loadComponent('DemoAuth');
	}

	/**
	 * Main dashboard page
	 *
	 * @return void
	 */
	public function index(): void {
		$this->DemoAuth->requireAuthorization();
		$this->set('pageTitle', 'Dashboard');
		$this->set('currentRole', $this->DemoAuth->getCurrentRole());
	}

	/**
	 * User statistics
	 *
	 * @return void
	 */
	public function stats(): void {
		$this->DemoAuth->requireAuthorization();
		$this->set('pageTitle', 'Statistics');
		$this->set('currentRole', $this->DemoAuth->getCurrentRole());
	}

}
