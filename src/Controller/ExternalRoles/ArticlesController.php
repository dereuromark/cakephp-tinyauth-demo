<?php

declare(strict_types=1);

namespace App\Controller\ExternalRoles;

use App\Controller\FullBackend\ArticlesController as FullBackendArticlesController;

/**
 * Articles under the External Roles strategy. Body identical to
 * Full Backend — the only difference lives in
 * `StrategyMiddleware`, which points `TinyAuthBackend.roleSource` at
 * a session-backed callable for requests under this prefix.
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class ArticlesController extends FullBackendArticlesController
{
}
