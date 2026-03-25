<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Reports Controller
 *
 * This controller requires moderator or higher role
 *
 * @property \App\Controller\Component\DemoAuthComponent $DemoAuth
 */
class ReportsController extends AppController
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('DemoAuth');
    }

    /**
     * Main reports page
     *
     * @return void
     */
    public function index(): void
    {
        $this->DemoAuth->requireAuthorization();
        $this->set('pageTitle', 'Reports Overview');
        $this->set('currentRole', $this->DemoAuth->getCurrentRole());
    }

    /**
     * Usage report
     *
     * @return void
     */
    public function usage(): void
    {
        $this->DemoAuth->requireAuthorization();
        $this->set('pageTitle', 'Usage Report');
        $this->set('currentRole', $this->DemoAuth->getCurrentRole());
    }

    /**
     * Audit log report
     *
     * @return void
     */
    public function audit(): void
    {
        $this->DemoAuth->requireAuthorization();
        $this->set('pageTitle', 'Audit Log');
        $this->set('currentRole', $this->DemoAuth->getCurrentRole());
    }
}
