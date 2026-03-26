<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Controller for switching simulated roles in demo mode.
 */
class RoleSwitcherController extends AppController
{
    /**
     * Switch to a different role.
     *
     * If no user is currently selected, automatically select the first user with that role.
     *
     * @return \Cake\Http\Response|null
     */
    public function switch(): ?Response
    {
        $roleId = (int)$this->request->getData('role_id');
        $roleName = $this->request->getData('role_name');

        if ($roleId > 0) {
            $session = $this->request->getSession();
            $currentUserId = $session->read('Auth.user_id');

            // If no user selected, find first user with this role
            if (!$currentUserId) {
                $usersTable = $this->fetchTable('Users');
                /** @var \App\Model\Entity\User|null $user */
                $user = $usersTable->find()
                    ->contain(['Teams'])
                    ->where(['Users.role_id' => $roleId])
                    ->orderBy(['Users.id' => 'ASC'])
                    ->first();

                if ($user) {
                    $session->write('Auth.id', $user->id);
                    $session->write('Auth.user_id', $user->id);
                    $session->write('Auth.role_id', $roleId);
                    $session->write('Auth.role_name', $roleName);
                    $session->write('Auth.team_id', $user->team_id);
                    $session->write('Auth.username', $user->username);

                    $teamName = $user->team ? $user->team->name : 'No Team';
                    $this->Flash->success(__('Switched to role: {0} (auto-selected user: {1}, {2})', $roleName, $user->username, $teamName));

                    return $this->redirect('/');
                }
            }

            $session->write('Auth.id', $currentUserId ?: 1);
            $session->write('Auth.role_id', $roleId);
            $session->write('Auth.role_name', $roleName);
            $this->Flash->success(__('Switched to role: {0}', $roleName));
        } else {
            // Clear session (logged out)
            $this->request->getSession()->delete('Auth');
            $this->Flash->success(__('Logged out (no role)'));
        }

        return $this->redirect('/');
    }

    /**
     * Switch to a specific user (for resource permission testing).
     *
     * @return \Cake\Http\Response|null
     */
    public function switchUser(): ?Response
    {
        $userId = (int)$this->request->getData('user_id');

        if ($userId > 0) {
            $usersTable = $this->fetchTable('Users');
            $user = $usersTable->find()
                ->contain(['Teams'])
                ->where(['Users.id' => $userId])
                ->first();

            if ($user) {
                $rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
                $role = $rolesTable->find()->where(['id' => $user->role_id])->first();

                $this->request->getSession()->write('Auth.id', $user->id);
                $this->request->getSession()->write('Auth.user_id', $user->id);
                $this->request->getSession()->write('Auth.role_id', $user->role_id);
                $this->request->getSession()->write('Auth.role_name', $role ? $role->alias : 'Unknown');
                $this->request->getSession()->write('Auth.team_id', $user->team_id);
                $this->request->getSession()->write('Auth.username', $user->username);

                $teamName = $user->team ? $user->team->name : 'No Team';
                $this->Flash->success(__('Switched to user: {0} ({1}, {2})', $user->username, $role ? $role->alias : 'Unknown', $teamName));
            } else {
                $this->Flash->error(__('User not found'));
            }
        } else {
            $this->request->getSession()->delete('Auth');
            $this->Flash->success(__('Logged out'));
        }

        return $this->redirect($this->referer('/'));
    }

    /**
     * Clear the current role (logout simulation).
     *
     * @return \Cake\Http\Response|null
     */
    public function clear(): ?Response
    {
        $this->request->getSession()->delete('Auth');
        $this->Flash->success(__('Session cleared'));

        return $this->redirect('/');
    }
}
