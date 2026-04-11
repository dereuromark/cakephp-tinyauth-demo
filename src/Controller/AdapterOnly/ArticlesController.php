<?php
declare(strict_types=1);

namespace App\Controller\AdapterOnly;

use App\Model\Entity\Article;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Articles under the Adapter Only strategy.
 *
 * No Authorization component, no `authorize()` calls, no scope
 * filtering. The demo intentionally surfaces **every** article to
 * every authenticated role, to illustrate what this strategy does
 * (and does not) enforce.
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Articles = $this->fetchTable('Articles');
    }

    /**
     * @return void
     */
    public function index(): void
    {
        $articles = $this->Articles->find()
            ->contain(['Users'])
            ->orderBy(['Articles.created' => 'DESC'])
            ->all()
            ->toArray();

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

        if ($this->request->is(['patch', 'post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
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
