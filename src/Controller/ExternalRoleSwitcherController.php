<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Writes the current "external" role alias to the session so that
 * `StrategyMiddleware`'s callable-based `TinyAuthBackend.roleSource`
 * can read it back. Drives the ExternalRoles strategy demo.
 */
class ExternalRoleSwitcherController extends AppController
{
    /**
     * @return \Cake\Http\Response|null
     */
    public function switch(): ?Response
    {
        $this->request->allowMethod(['post']);

        $role = (string)$this->request->getData('role');
        $session = $this->request->getSession();
        if ($role === '') {
            $session->delete('ExternalRoles.role');
        } else {
            $session->write('ExternalRoles.role', $role);
        }

        $referer = $this->referer('/strategy', true);

        return $this->redirect($referer);
    }
}
