<?php
declare(strict_types=1);

namespace App\Controller\FullBackend;

use App\Model\Entity\Article;
use App\Model\Table\ArticlesTable;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Articles under the Full Backend strategy.
 *
 * Exactly the code a production app would ship: list actions call
 * `applyScope()` so the Authorization plugin hands the query to
 * `TinyAuthPolicy` (which in turn asks `TinyAuthService` for the
 * active role's scope conditions against the `Article` resource).
 * Entity actions call `authorize()` with the ability name.
 */
class ArticlesController extends AppController
{
    protected ArticlesTable $Articles;

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        /** @var \App\Model\Table\ArticlesTable $table */
        $table = $this->fetchTable('Articles');
        $this->Articles = $table;
    }

    /**
     * @return void
     */
    public function index(): void
    {
        $query = $this->Articles->find()
            ->contain(['Users'])
            ->orderBy(['Articles.created' => 'DESC']);

        // Hands the query to TinyAuthPolicy::scopeIndex() via the
        // Authorization plugin. The policy asks TinyAuthService for
        // the active role's scope conditions and applies them.
        $query = $this->Authorization->applyScope($query, 'index');

        $articles = $query->all()->toArray();

        $this->set(compact('articles'));
        $this->set('pageTitle', 'Articles');
    }

    /**
     * @param int $id
     * @return void
     */
    public function view(int $id): void
    {
        $article = $this->loadArticle($id);
        $this->Authorization->authorize($article, 'view');

        $this->set(compact('article'));
        $this->set('pageTitle', 'View Article');
    }

    /**
     * @param int $id
     * @return \Cake\Http\Response|null
     */
    public function edit(int $id): ?Response
    {
        $article = $this->loadArticle($id);
        $this->Authorization->authorize($article, 'edit');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->getData(), [
                'fields' => ['title', 'body', 'status'],
            ]);
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Article saved.'));

                return $this->redirect(['action' => 'view', $article->id]);
            }
            $this->Flash->error(__('Could not save article.'));
        }

        $this->set(compact('article'));
        $this->set('pageTitle', 'Edit Article');

        return null;
    }

    /**
     * @param int $id
     * @return \Cake\Http\Response|null
     */
    public function delete(int $id): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        /** @var \App\Model\Entity\Article $article */
        $article = $this->Articles->get($id);
        $this->Authorization->authorize($article, 'delete');

        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('Article deleted.'));
        } else {
            $this->Flash->error(__('Could not delete article.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * @param int $id
     * @return \App\Model\Entity\Article
     */
    protected function loadArticle(int $id): Article
    {
        /** @var \App\Model\Entity\Article|null $article */
        $article = $this->Articles->find()
            ->contain(['Users'])
            ->where(['Articles.id' => $id])
            ->first();
        if (!$article) {
            throw new NotFoundException('Article not found.');
        }

        return $article;
    }
}
