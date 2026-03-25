<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Articles Controller
 *
 * Demonstrates TinyAuth resource-level permissions with "own" scope.
 *
 * Example permissions setup:
 * - user: view (no scope), edit (own scope), delete (own scope)
 * - moderator: view (no scope), edit (no scope), delete (no scope)
 * - admin: full access
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 * @property \App\Controller\Component\DemoAuthComponent $DemoAuth
 */
class ArticlesController extends AppController {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();
		$this->loadComponent('DemoAuth');
	}

	/**
	 * List articles.
	 *
	 * Uses scope conditions to filter articles based on user's permissions.
	 * - Users see only their own articles (with "own" scope)
	 * - Moderators/Admins see all articles (no scope restriction)
	 *
	 * @return void
	 */
	public function index(): void {
		$this->DemoAuth->requireAuthorization();

		$query = $this->Articles->find()
			->contain(['Users'])
			->orderBy(['Articles.created' => 'DESC']);

		// Apply scope-based filtering
		$conditions = $this->DemoAuth->getScopeConditions('Article', 'view');

		if ($conditions === null) {
			// No access - show empty list
			$articles = [];
		} elseif ($conditions) {
			// Scoped access - apply conditions
			$query->where($conditions);
			$articles = $query->all()->toArray();
		} else {
			// Full access - no conditions
			$articles = $query->all()->toArray();
		}

		// Add permission flags to each article for the view
		foreach ($articles as $article) {
			$article->canEdit = $this->DemoAuth->canAccessResource($article, 'edit', 'Article');
			$article->canDelete = $this->DemoAuth->canAccessResource($article, 'delete', 'Article');
		}

		$currentUser = $this->DemoAuth->getCurrentUser();
		$currentRole = $this->DemoAuth->getCurrentRole();

		$this->set(compact('articles', 'currentUser', 'currentRole', 'conditions'));
		$this->set('pageTitle', 'Articles - Resource Demo');
	}

	/**
	 * View a single article.
	 *
	 * Checks resource-level "view" ability for the specific article.
	 *
	 * @param int $id Article ID
	 * @throws \Cake\Http\Exception\NotFoundException
	 * @throws \Cake\Http\Exception\ForbiddenException
	 * @return void
	 */
	public function view(int $id): void {
		$this->DemoAuth->requireAuthorization();

		$article = $this->Articles->find()
			->contain(['Users'])
			->where(['Articles.id' => $id])
			->first();

		if (!$article) {
			throw new NotFoundException('Article not found');
		}

		// Check resource permission
		$canView = $this->DemoAuth->canAccessResource($article, 'view', 'Article');
		if (!$canView) {
			throw new ForbiddenException('You do not have permission to view this article.');
		}

		// Also check edit/delete permissions for UI
		$canEdit = $this->DemoAuth->canAccessResource($article, 'edit', 'Article');
		$canDelete = $this->DemoAuth->canAccessResource($article, 'delete', 'Article');

		$currentUser = $this->DemoAuth->getCurrentUser();
		$currentRole = $this->DemoAuth->getCurrentRole();

		$this->set(compact('article', 'canView', 'canEdit', 'canDelete', 'currentUser', 'currentRole'));
		$this->set('pageTitle', 'View Article');
	}

	/**
	 * Edit an article.
	 *
	 * Checks resource-level "edit" ability for the specific article.
	 *
	 * @param int $id Article ID
	 * @throws \Cake\Http\Exception\NotFoundException
	 * @throws \Cake\Http\Exception\ForbiddenException
	 * @return \Cake\Http\Response|null
	 */
	public function edit(int $id): ?Response {
		$this->DemoAuth->requireAuthorization();

		$article = $this->Articles->find()
			->contain(['Users'])
			->where(['Articles.id' => $id])
			->first();

		if (!$article) {
			throw new NotFoundException('Article not found');
		}

		// Check resource permission
		$canEdit = $this->DemoAuth->canAccessResource($article, 'edit', 'Article');
		if (!$canEdit) {
			throw new ForbiddenException('You do not have permission to edit this article.');
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$article = $this->Articles->patchEntity($article, $this->request->getData());
			if ($this->Articles->save($article)) {
				$this->Flash->success(__('Article saved.'));

				return $this->redirect(['action' => 'view', $article->id]);
			}
			$this->Flash->error(__('Could not save article.'));
		}

		$currentUser = $this->DemoAuth->getCurrentUser();
		$currentRole = $this->DemoAuth->getCurrentRole();

		$this->set(compact('article', 'currentUser', 'currentRole'));
		$this->set('pageTitle', 'Edit Article');

		return null;
	}

	/**
	 * Delete an article.
	 *
	 * Checks resource-level "delete" ability for the specific article.
	 *
	 * @param int $id Article ID
	 * @throws \Cake\Http\Exception\NotFoundException
	 * @throws \Cake\Http\Exception\ForbiddenException
	 * @return \Cake\Http\Response|null
	 */
	public function delete(int $id): ?Response {
		$this->request->allowMethod(['post', 'delete']);
		$this->DemoAuth->requireAuthorization();

		$article = $this->Articles->get($id);

		// Check resource permission
		$canDelete = $this->DemoAuth->canAccessResource($article, 'delete', 'Article');
		if (!$canDelete) {
			throw new ForbiddenException('You do not have permission to delete this article.');
		}

		if ($this->Articles->delete($article)) {
			$this->Flash->success(__('Article deleted.'));
		} else {
			$this->Flash->error(__('Could not delete article.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
