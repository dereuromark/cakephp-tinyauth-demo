<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * Demo Controller
 *
 * Main landing page for the TinyAuth demo application.
 */
class DemoController extends AppController
{
    /**
     * Demo home page with role/user/feature switchers.
     *
     * @return void
     */
    public function index(): void
    {
        $session = $this->request->getSession();

        // Current auth state
        $currentRoleId = $session->read('Auth.role_id');
        $currentRoleName = $session->read('Auth.role_name');
        $currentUserId = $session->read('Auth.user_id');
        $currentUsername = $session->read('Auth.username');
        $currentTeamId = $session->read('Auth.team_id');

        // Feature toggles
        $sessionFeatures = $session->read('TinyAuthFeatures') ?? [];

        // Demo users for user switcher
        $demoUsers = $this->fetchTable('Users')->find()
            ->contain(['Teams'])
            ->orderBy(['Users.id' => 'ASC'])
            ->all()
            ->toArray();

        // Roles for role switcher
        $roles = $this->fetchTable('TinyAuthBackend.Roles')->find()
            ->orderBy(['sort_order' => 'ASC', 'id' => 'ASC'])
            ->all()
            ->toArray();

        $this->set(compact(
            'currentRoleId',
            'currentRoleName',
            'currentUserId',
            'currentUsername',
            'currentTeamId',
            'sessionFeatures',
            'demoUsers',
            'roles',
        ));
    }
}
