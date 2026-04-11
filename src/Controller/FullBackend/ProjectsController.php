<?php

declare(strict_types=1);

namespace App\Controller\FullBackend;

use App\Model\Entity\Project;
use App\Model\Table\ProjectsTable;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Projects under the Full Backend strategy.
 *
 * Projects use the `team` scope (project.team_id = user.team_id) as
 * well as `own`, so they exercise the scopes table more fully than
 * Articles.
 *
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
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
        $query = $this->Projects->find()
            ->contain(['Users', 'Teams'])
            ->orderBy(['Projects.created' => 'DESC']);
        $query = $this->Authorization->applyScope($query, 'index');

        $projects = $query->all()->toArray();

        $this->set(compact('projects'));
        $this->set('pageTitle', 'Projects');
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function view(int $id): void
    {
        $project = $this->loadProject($id);
        $this->Authorization->authorize($project, 'view');

        $this->set(compact('project'));
        $this->set('pageTitle', 'View Project');
    }

    /**
     * @param int $id
     *
     * @return \Cake\Http\Response|null
     */
    public function edit(int $id): ?Response
    {
        $project = $this->loadProject($id);
        $this->Authorization->authorize($project, 'edit');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $project = $this->Projects->patchEntity($project, $this->request->getData(), [
                'fields' => ['name', 'description'],
            ]);
            if ($this->Projects->save($project)) {
                $this->Flash->success(__('Project saved.'));

                return $this->redirect(['action' => 'view', $project->id]);
            }
            $this->Flash->error(__('Could not save project.'));
        }

        $this->set(compact('project'));
        $this->set('pageTitle', 'Edit Project');

        return null;
    }

    /**
     * @param int $id
     *
     * @throws \Cake\Http\Exception\NotFoundException
     *
     * @return \App\Model\Entity\Project
     */
    protected function loadProject(int $id): Project
    {
        /** @var \App\Model\Entity\Project|null $project */
        $project = $this->Projects->find()
            ->contain(['Users', 'Teams'])
            ->where(['Projects.id' => $id])
            ->first();
        if (!$project) {
            throw new NotFoundException('Project not found.');
        }

        return $project;
    }
}
