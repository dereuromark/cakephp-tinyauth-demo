<?php

declare(strict_types=1);

namespace App\Controller\FullBackend;

use App\Controller\AppController as BaseAppController;
use App\Service\Strategy;
use Cake\Event\EventInterface;

/**
 * Shared base for the FullBackend strategy subtree.
 *
 * This strategy uses the full CakePHP Authorization stack:
 * - `AuthorizationComponent` is loaded so actions can call
 *   `$this->Authorization->authorize($entity)` and
 *   `$this->Authorization->applyScope($query)`.
 * - Entity-to-policy mapping is resolved by the MapResolver in
 *   `Application::getAuthorizationService()`, which maps every
 *   relevant entity to `TinyAuthBackend\Policy\TinyAuthPolicy`.
 * - Rules, hierarchy, abilities, and scopes all live in the plugin's
 *   tables and are edited through `/admin/auth/*`.
 *
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class AppController extends BaseAppController
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Authorization.Authorization');
    }

    /**
     * Resolve the active strategy from the request prefix rather than
     * hard-coding it — this matters for NativeAuth and ExternalRoles
     * which extend the Full Backend controllers for code reuse.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event
     *
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $prefix = (string)$this->request->getParam('prefix');
        $slug = Strategy::slugForPrefix($prefix) ?? Strategy::FULL_BACKEND;
        $this->set('strategy', Strategy::find($slug));
    }
}
