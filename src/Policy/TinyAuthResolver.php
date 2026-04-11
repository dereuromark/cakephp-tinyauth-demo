<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Article;
use App\Model\Entity\Project;
use App\Model\Table\ArticlesTable;
use App\Model\Table\ProjectsTable;
use Authorization\Policy\Exception\MissingPolicyException;
use Authorization\Policy\ResolverInterface;
use Cake\ORM\Query\SelectQuery;
use TinyAuthBackend\Policy\TinyAuthPolicy;

/**
 * A tiny resolver that returns the plugin's `TinyAuthPolicy` for
 * every demo entity, table, and select query routed through the
 * Authorization plugin.
 *
 * Cake's `MapResolver` only matches exact class names, which is fine
 * for entities passed to `authorize()` but not for query objects
 * passed to `applyScope()` — the Authorization plugin unwraps the
 * query to its repository (the table) before resolving, and
 * `MapResolver` won't transitively match sub-queries. OrmResolver
 * handles the unwrapping but insists on class-name-based lookup in
 * `src/Policy/`.
 *
 * This resolver short-circuits that by returning a single shared
 * policy instance for any known demo resource, regardless of which
 * of the three shapes (entity, table, query) the Authorization
 * plugin hands it. It's intentionally explicit and tiny — a drop-in
 * template for adopters who want a similar fast path in their own
 * app.
 */
class TinyAuthResolver implements ResolverInterface
{
    /**
     * @var array<class-string, bool>
     */
    protected const KNOWN = [
        Article::class => true,
        Project::class => true,
        ArticlesTable::class => true,
        ProjectsTable::class => true,
    ];

    /**
     * @var \TinyAuthBackend\Policy\TinyAuthPolicy|null
     */
    protected ?TinyAuthPolicy $policy = null;

    /**
     * @param object $resource
     * @return object
     * @throws \Authorization\Policy\Exception\MissingPolicyException
     */
    public function getPolicy(mixed $resource): object
    {
        if ($resource instanceof SelectQuery) {
            /** @var \Cake\ORM\Table $repository */
            $repository = $resource->getRepository();
            $class = get_class($repository);
        } else {
            $class = get_class($resource);
        }

        if (!isset(self::KNOWN[$class])) {
            throw new MissingPolicyException([$class]);
        }

        $this->policy ??= new TinyAuthPolicy();

        return $this->policy;
    }
}
