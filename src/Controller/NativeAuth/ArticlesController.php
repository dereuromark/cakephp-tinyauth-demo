<?php

declare(strict_types=1);

namespace App\Controller\NativeAuth;

use App\Controller\FullBackend\ArticlesController as FullBackendArticlesController;

/**
 * Articles under the Native Auth strategy. The controller body is
 * identical to the Full Backend one — only the base `AppController`
 * differs (and even then, only in demo description). The policy
 * does all the work either way.
 *
 * Extending from the Full Backend controller rather than duplicating
 * is intentional here: it demonstrates that the enforcement code
 * (`authorize()` / `applyScope()`) is exactly the same whether or
 * not TinyAuth middleware is in the stack.
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class ArticlesController extends FullBackendArticlesController
{
}
