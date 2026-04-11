<?php
declare(strict_types=1);

namespace App\Controller\ExternalRoles;

use App\Controller\FullBackend\ProjectsController as FullBackendProjectsController;

/**
 * Projects under the External Roles strategy.
 *
 * @property \App\Model\Table\ProjectsTable $Projects
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class ProjectsController extends FullBackendProjectsController
{
}
