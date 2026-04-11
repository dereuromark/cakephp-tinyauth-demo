<?php
declare(strict_types=1);

namespace App\Controller\AdapterOnly;

use App\Model\Entity\Project;
use Cake\Http\Exception\NotFoundException;

/**
 * Projects under the Adapter Only strategy.
 *
 * @property \App\Model\Table\ProjectsTable $Projects
 */
class ProjectsController extends AppController
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Projects = $this->fetchTable('Projects');
    }

    /**
     * @return void
     */
    public function index(): void
    {
        $projects = $this->Projects->find()
            ->contain(['Users', 'Teams'])
            ->orderBy(['Projects.created' => 'DESC'])
            ->all()
            ->toArray();

        $this->set(compact('projects'));
        $this->set('pageTitle', 'Projects');
    }

    /**
     * @param int $id
     * @return void
     */
    public function view(int $id): void
    {
        /** @var \App\Model\Entity\Project|null $project */
        $project = $this->Projects->find()
            ->contain(['Users', 'Teams'])
            ->where(['Projects.id' => $id])
            ->first();
        if (!$project) {
            throw new NotFoundException('Project not found.');
        }

        $this->set(compact('project'));
        $this->set('pageTitle', 'View Project');
    }
}
