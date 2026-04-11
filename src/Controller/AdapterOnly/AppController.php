<?php

declare(strict_types=1);

namespace App\Controller\AdapterOnly;

use App\Controller\AppController as BaseAppController;
use App\Service\Strategy;
use Cake\Event\EventInterface;

/**
 * Shared base for the Adapter Only strategy subtree.
 *
 * In this strategy the Authorization component is deliberately not
 * loaded — the plugin's only job is to serve as the admin UI for
 * editing classic TinyAuth allow/acl rules (which live in the DB via
 * `DbAllowAdapter` / `DbAclAdapter`). Controllers have no
 * `$this->Authorization->authorize()` calls and no entity-level
 * ownership checks. Request-level gating is the whole story.
 *
 * The practical consequence: any role with `edit` permission on
 * `Articles::edit` can edit **any** article, not just its own. This
 * is the deliberate contrast with Full Backend — use AdapterOnly when
 * you just want the admin UX without adopting the Authorization
 * layer.
 */
class AppController extends BaseAppController
{
    /**
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $this->set('strategy', Strategy::find(Strategy::ADAPTER_ONLY));
    }
}
