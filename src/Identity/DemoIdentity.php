<?php
declare(strict_types=1);

namespace App\Identity;

use ArrayAccess;
use Authorization\AuthorizationServiceInterface;
use Authorization\IdentityInterface;
use Authorization\Policy\ResultInterface;
use Cake\Datasource\EntityInterface;

/**
 * Lightweight identity wrapper around a User entity for the demo.
 *
 * The demo has no real login flow — user identity comes from session
 * state written by the role switcher. This wrapper satisfies the
 * Authorization plugin's IdentityInterface contract against a plain
 * User entity without requiring cakephp/authentication.
 */
class DemoIdentity implements IdentityInterface
{
    /**
     * @param \Cake\Datasource\EntityInterface $user
     * @param \Authorization\AuthorizationServiceInterface|null $service
     */
    public function __construct(
        protected EntityInterface $user,
        protected ?AuthorizationServiceInterface $service = null,
    ) {
    }

    /**
     * @param string $action
     * @param mixed $resource
     * @return bool
     */
    public function can(string $action, mixed $resource): bool
    {
        return $this->service?->can($this, $action, $resource) ?? false;
    }

    /**
     * @param string $action
     * @param mixed $resource
     * @return \Authorization\Policy\ResultInterface
     */
    public function canResult(string $action, mixed $resource): ResultInterface
    {
        /** @var \Authorization\Policy\ResultInterface */
        return $this->service->canResult($this, $action, $resource);
    }

    /**
     * @param string $action
     * @param mixed $resource
     * @return mixed
     */
    public function applyScope(string $action, mixed $resource, mixed ...$optionalArgs): mixed
    {
        return $this->service?->applyScope($this, $action, $resource, ...$optionalArgs);
    }

    /**
     * Returns the underlying user entity. The Authorization plugin's
     * interface declares this return type as `ArrayAccess|array`; a
     * CakePHP entity satisfies that since `EntityInterface` extends
     * `ArrayAccess`. The TinyAuthPolicy::before() hook downcasts to
     * `EntityInterface` at runtime.
     *
     * @return \ArrayAccess<string, mixed>|array<string, mixed>
     */
    public function getOriginalData(): ArrayAccess|array
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getIdentifier(): mixed
    {
        return $this->user->get('id');
    }

    /**
     * @param \Authorization\AuthorizationServiceInterface $service
     * @return $this
     */
    public function setAuthorization(AuthorizationServiceInterface $service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->user->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->user->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->user->set($offset, $value);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->user->unset($offset);
    }
}
