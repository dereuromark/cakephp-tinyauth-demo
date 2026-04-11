<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query\SelectQuery;
use TinyAuthBackend\Policy\TinyAuthPolicy;

/**
 * Extends TinyAuthPolicy with `scope*()` methods so CakePHP
 * Authorization's `$this->Authorization->applyScope($query)` works
 * against DB-managed scopes.
 *
 * The upstream plugin's policy only checks individual entities; this
 * subclass bridges to `TinyAuthService::getScopeCondition()` so the
 * same scope rows that drive entity-level checks also narrow list
 * queries. Kept in the demo for now; a good follow-up PR moves this
 * into the plugin itself.
 */
class TinyAuthScopedPolicy extends TinyAuthPolicy
{
    /**
     * @param \Authorization\IdentityInterface|null $identity
     * @param \Cake\ORM\Query\SelectQuery $query
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function scopeIndex(?IdentityInterface $identity, SelectQuery $query): SelectQuery
    {
        return $this->applyScopeConditions($identity, $query, 'view');
    }

    /**
     * @param \Authorization\IdentityInterface|null $identity
     * @param \Cake\ORM\Query\SelectQuery $query
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function scopeView(?IdentityInterface $identity, SelectQuery $query): SelectQuery
    {
        return $this->applyScopeConditions($identity, $query, 'view');
    }

    /**
     * Resolve scope conditions for the current identity and apply
     * them to the query. Handles the three states
     * `TinyAuthService::getScopeCondition()` returns:
     *
     *   - `null` → no access; the query is forced empty so no rows leak.
     *   - `[]`  → full access; the query is returned untouched.
     *   - `[...]` → scoped access; conditions are applied with
     *     table-qualified field names and `IS NULL` for null values.
     *
     * @param \Authorization\IdentityInterface|null $identity
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param string $ability
     * @return \Cake\ORM\Query\SelectQuery
     */
    protected function applyScopeConditions(?IdentityInterface $identity, SelectQuery $query, string $ability): SelectQuery
    {
        if ($identity === null) {
            return $query->where(['1 = 0']);
        }
        $user = $identity->getOriginalData();
        if (!$user instanceof EntityInterface) {
            return $query->where(['1 = 0']);
        }

        $resource = $this->resourceForQuery($query);
        $roles = $this->getTinyAuthService()->getUserRoles($user);
        $conditions = $this->getTinyAuthService()->getScopeCondition($roles, $resource, $ability, $user);

        if ($conditions === null) {
            return $query->where(['1 = 0']);
        }
        if ($conditions === []) {
            return $query;
        }

        return $query->where($this->qualifyConditions($conditions, $query));
    }

    /**
     * Derive the resource class name from a query. Uses the repository's
     * entity class so the lookup matches how `TinyAuthPolicy::can()`
     * already resolves single-entity checks.
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @return string
     */
    protected function resourceForQuery(SelectQuery $query): string
    {
        /** @var \Cake\ORM\Table $repository */
        $repository = $query->getRepository();

        return $repository->getEntityClass();
    }

    /**
     * Table-qualify the scope conditions returned by TinyAuthService
     * so they don't collide with joined tables, and translate explicit
     * nulls into `field IS` form.
     *
     * @param array<string, mixed> $conditions
     * @param \Cake\ORM\Query\SelectQuery $query
     * @return array<string, mixed>
     */
    protected function qualifyConditions(array $conditions, SelectQuery $query): array
    {
        /** @var \Cake\ORM\Table $repository */
        $repository = $query->getRepository();
        $alias = $repository->getAlias();

        $out = [];
        foreach ($conditions as $field => $value) {
            if ($field === 'OR' || $field === 'AND') {
                $out[$field] = array_map(
                    fn ($sub) => is_array($sub) ? $this->qualifyConditions($sub, $query) : $sub,
                    (array)$value,
                );

                continue;
            }

            $qualified = str_contains($field, '.') ? $field : $alias . '.' . $field;
            if ($value === null) {
                $out[$qualified . ' IS'] = null;
            } else {
                $out[$qualified] = $value;
            }
        }

        return $out;
    }
}
