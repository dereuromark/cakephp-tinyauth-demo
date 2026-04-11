<?php

declare(strict_types=1);

namespace App\Controller\NativeAuth;

use App\Controller\FullBackend\ProjectsController as FullBackendProjectsController;

/**
 * Projects under the Native Auth strategy. See
 * `ArticlesController` for the rationale — body identical to Full
 * Backend.
 *
 * @property \App\Model\Table\ProjectsTable $Projects
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class ProjectsController extends FullBackendProjectsController
{
}
