<?php

declare(strict_types=1);

namespace App\Controller\AdapterOnly;

use App\Model\Table\ProjectsTable;
use Cake\Http\Exception\NotFoundException;

/**
 * Projects under the Adapter Only strategy.
 */
class ProjectsController extends AppController
{
    protected ProjectsTable $Projects;

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        /** @var \App\Model\Table\ProjectsTable $table */
        $table = $this->fetchTable('Projects');
        $this->Projects = $table;
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
     *
     * @throws \Cake\Http\Exception\NotFoundException
     *
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
