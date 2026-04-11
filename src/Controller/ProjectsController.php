<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Projects Controller
 *
 * Demonstrates TinyAuth resource-level permissions with both "own" and "team" scopes.
 *
 * Example permissions setup:
 * - user: view (team scope), edit (own scope), delete (none)
 * - moderator: view (no scope), edit (team scope), delete (own scope)
 * - admin: full access
 *
 * This shows how different scopes can be applied to different abilities:
 * - "own" scope: user_id === current_user.id
 * - "team" scope: team_id === current_user.team_id
 *
 * @property \App\Model\Table\ProjectsTable $Projects
 * @property \App\Controller\Component\DemoAuthComponent $DemoAuth
 */
class ProjectsController extends AppController
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('DemoAuth');
    }

    /**
     * List projects.
     *
     * Uses scope conditions to filter projects based on user's permissions.
     *
     * @return void
     */
    public function index(): void
    {
        $this->DemoAuth->requireAuthorization();

        $query = $this->Projects->find()
            ->contain(['Users', 'Teams'])
            ->orderBy(['Projects.created' => 'DESC']);

        // Apply scope-based filtering
        $conditions = $this->DemoAuth->getScopeConditions('Project', 'view');

        if ($conditions === null) {
            // No access
            $projects = [];
        } elseif ($conditions) {
            // Scoped access - prefix with table alias to avoid ambiguity in joins
            $prefixedConditions = [];
            foreach ($conditions as $field => $value) {
                if ($value === null) {
                    // Use IS NULL syntax for null values
                    $prefixedConditions['Projects.' . $field . ' IS'] = null;
                } else {
                    $prefixedConditions['Projects.' . $field] = $value;
                }
            }
            $query->where($prefixedConditions);
            $projects = $query->all()->toArray();
        } else {
            // Full access
            $projects = $query->all()->toArray();
        }

        // Add permission flags to each project for the view
        foreach ($projects as $project) {
            $project->canEdit = $this->DemoAuth->canAccessResource($project, 'edit', 'Project');
            $project->canDelete = $this->DemoAuth->canAccessResource($project, 'delete', 'Project');
        }

        $currentUser = $this->DemoAuth->getCurrentUser();
        $currentRole = $this->DemoAuth->getCurrentRole();

        $this->set(compact('projects', 'currentUser', 'currentRole', 'conditions'));
        $this->set('pageTitle', 'Projects - Resource Demo (Team Scope)');
    }

    /**
     * View a single project.
     *
     * @param int $id Project ID
     * @throws \Cake\Http\Exception\NotFoundException
     * @throws \Cake\Http\Exception\ForbiddenException
     * @return void
     */
    public function view(int $id): void
    {
        $this->DemoAuth->requireAuthorization();

        $project = $this->Projects->find()
            ->contain(['Users', 'Teams'])
            ->where(['Projects.id' => $id])
            ->first();

        if (!$project) {
            throw new NotFoundException('Project not found');
        }

        // Check resource permission
        $canView = $this->DemoAuth->canAccessResource($project, 'view', 'Project');
        if (!$canView) {
            throw new ForbiddenException('You do not have permission to view this project.');
        }

        // Check other permissions for UI
        $canEdit = $this->DemoAuth->canAccessResource($project, 'edit', 'Project');
        $canDelete = $this->DemoAuth->canAccessResource($project, 'delete', 'Project');

        $currentUser = $this->DemoAuth->getCurrentUser();
        $currentRole = $this->DemoAuth->getCurrentRole();

        $this->set(compact('project', 'canView', 'canEdit', 'canDelete', 'currentUser', 'currentRole'));
        $this->set('pageTitle', 'View Project');
    }

    /**
     * Edit a project.
     *
     * @param int $id Project ID
     * @throws \Cake\Http\Exception\NotFoundException
     * @throws \Cake\Http\Exception\ForbiddenException
     * @return \Cake\Http\Response|null
     */
    public function edit(int $id): ?Response
    {
        $this->DemoAuth->requireAuthorization();

        $project = $this->Projects->find()
            ->contain(['Users', 'Teams'])
            ->where(['Projects.id' => $id])
            ->first();

        if (!$project) {
            throw new NotFoundException('Project not found');
        }

        // Check resource permission
        $canEdit = $this->DemoAuth->canAccessResource($project, 'edit', 'Project');
        if (!$canEdit) {
            throw new ForbiddenException('You do not have permission to edit this project.');
        }

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

        $teams = $this->Projects->Teams->find('list', keyField: 'id', valueField: 'name')->toArray();
        $currentUser = $this->DemoAuth->getCurrentUser();
        $currentRole = $this->DemoAuth->getCurrentRole();

        $this->set(compact('project', 'teams', 'currentUser', 'currentRole'));
        $this->set('pageTitle', 'Edit Project');

        return null;
    }

    /**
     * Delete a project.
     *
     * @param int $id Project ID
     * @throws \Cake\Http\Exception\NotFoundException
     * @throws \Cake\Http\Exception\ForbiddenException
     * @return \Cake\Http\Response|null
     */
    public function delete(int $id): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->DemoAuth->requireAuthorization();

        $project = $this->Projects->get($id);

        // Check resource permission
        $canDelete = $this->DemoAuth->canAccessResource($project, 'delete', 'Project');
        if (!$canDelete) {
            throw new ForbiddenException('You do not have permission to delete this project.');
        }

        if ($this->Projects->delete($project)) {
            $this->Flash->success(__('Project deleted.'));
        } else {
            $this->Flash->error(__('Could not delete project.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
