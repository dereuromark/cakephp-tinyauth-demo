<?php
declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Authorization\Policy\BeforePolicyInterface;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query\SelectQuery;
use TinyAuthBackend\Service\TinyAuthService;

/**
 * Full entity-level policy for the demo, implemented directly
 * against `TinyAuthBackend\Service\TinyAuthService` instead of
 * extending `TinyAuthBackend\Policy\TinyAuthPolicy`.
 *
 * **Why not extend `TinyAuthPolicy`:** the upstream policy's
 * `canView(EntityInterface $user, ...)` signature is incompatible
 * with CakePHP Authorization's calling convention — Authorization
 * passes `?IdentityInterface $identity`, and PHP's LSP rules forbid
 * loosening the parameter type in an override. Composition side-
 * steps this cleanly.
 *
 * **What it adds on top of TinyAuthService:**
 *
 * - Identity-aware `can*()` methods that match Cake Authorization's
 *   dispatch convention (`canView` / `canEdit` / `canDelete`).
 * - `scopeIndex()` / `scopeView()` that apply
 *   `getScopeCondition()` to a query so
 *   `$this->Authorization->applyScope($query)` narrows list results.
 * - A `before()` hook that bypasses for a configurable super-admin
 *   role (`TinyAuthBackend.superAdminRole`), matching the upstream
 *   policy's behavior.
 *
 * This whole class is a good follow-up upstream contribution once
 * the plugin's policy method signatures are relaxed.
 */
class TinyAuthScopedPolicy implements BeforePolicyInterface
{
    protected TinyAuthService $service;

    /**
     * @param \TinyAuthBackend\Service\TinyAuthService|null $service
     */
    public function __construct(?TinyAuthService $service = null)
    {
        $this->service = $service ?? new TinyAuthService();
    }

    /**
     * @param \Authorization\IdentityInterface|null $identity
     * @param mixed $resource
     * @param string $action
     * @return bool|null
     */
    public function before(?IdentityInterface $identity, mixed $resource, string $action): ?bool
    {
        if ($identity === null) {
            return false;
        }

        $user = $identity->getOriginalData();
        if (!$user instanceof EntityInterface) {
            return false;
        }

        $roles = $this->service->getUserRoles($user);
        if (array_intersect($roles, $this->superAdminRoles())) {
            return true;
        }

        return null;
    }

    /**
     * @param \Authorization\IdentityInterface|null $identity
     * @param \Cake\Datasource\EntityInterface $entity
     * @return bool
     */
    public function canView(?IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->check($identity, 'view', $entity);
    }

    /**
     * @param \Authorization\IdentityInterface|null $identity
     * @param \Cake\Datasource\EntityInterface $entity
     * @return bool
     */
    public function canEdit(?IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->check($identity, 'edit', $entity);
    }

    /**
     * @param \Authorization\IdentityInterface|null $identity
     * @param \Cake\Datasource\EntityInterface $entity
     * @return bool
     */
    public function canDelete(?IdentityInterface $identity, EntityInterface $entity): bool
    {
        return $this->check($identity, 'delete', $entity);
    }

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
     * @param \Authorization\IdentityInterface|null $identity
     * @param string $ability
     * @param \Cake\Datasource\EntityInterface $entity
     * @return bool
     */
    protected function check(?IdentityInterface $identity, string $ability, EntityInterface $entity): bool
    {
        if ($identity === null) {
            return false;
        }
        $user = $identity->getOriginalData();
        if (!$user instanceof EntityInterface) {
            return false;
        }

        $roles = $this->service->getUserRoles($user);
        $resource = $this->resourceNameFor($entity);

        return $this->service->canAccess($roles, $resource, $ability, $entity, $user);
    }

    /**
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

        $roles = $this->service->getUserRoles($user);

        // Admins bypass scoping entirely — no WHERE narrowing.
        if (array_intersect($roles, $this->superAdminRoles())) {
            return $query;
        }

        $resource = $this->resourceNameFor($this->entityForQuery($query));
        $conditions = $this->service->getScopeCondition($roles, $resource, $ability, $user);

        if ($conditions === null) {
            return $query->where(['1 = 0']);
        }
        if ($conditions === []) {
            return $query;
        }

        return $query->where($this->qualifyConditions($conditions, $query));
    }

    /**
     * @param \Cake\Datasource\EntityInterface $entity
     * @return string
     */
    protected function resourceNameFor(EntityInterface $entity): string
    {
        return get_class($entity);
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery $query
     * @return \Cake\Datasource\EntityInterface
     */
    protected function entityForQuery(SelectQuery $query): EntityInterface
    {
        /** @var \Cake\ORM\Table $repository */
        $repository = $query->getRepository();
        $class = $repository->getEntityClass();

        /** @var \Cake\Datasource\EntityInterface $prototype */
        $prototype = new $class();

        return $prototype;
    }

    /**
     * @return array<string>
     */
    protected function superAdminRoles(): array
    {
        $role = Configure::read('TinyAuthBackend.superAdminRole')
            ?? Configure::read('TinyAuth.superAdminRole');

        if (is_string($role) && $role !== '') {
            return [$role];
        }
        if (is_array($role)) {
            return array_values(array_filter($role, 'is_string'));
        }

        return ['admin', 'superadmin'];
    }

    /**
     * Table-qualify the scope conditions so they don't collide with
     * joined tables, and translate null values into `IS NULL`.
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
                    fn($sub) => is_array($sub) ? $this->qualifyConditions($sub, $query) : $sub,
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
