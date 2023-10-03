<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\app\Exceptions\AccessDeniedException;

trait Access
{
    /**
     * Set an operation as having access using the Settings API.
     *
     * @param  string|array  $operation
     * @return bool
     */
    public function allowAccess(array|string $operation): bool
    {
        foreach ((array) $operation as $op) {
            $this->set($op.'.access', true);
        }

        return $this->hasAccessToAll($operation);
    }

    /**
     * Disable the access to a certain operation, or the current one.
     *
     * @param  string|array  $operation  [description]
     * @return [type] [description]
     */
    public function denyAccess(array|string $operation)
    {
        foreach ((array) $operation as $op) {
            $this->set($op.'.access', false);
        }

        return ! $this->hasAccessToAny($operation);
    }

    /**
     * Check if a operation is allowed for a Crud Panel. Return false if not.
     *
     * @param  string  $operation
     * @param  \Model  $entry
     * @return bool
     */
    public function hasAccess(string $operation, $entry = null): bool
    {
        $condition = $this->get($operation.'.access');

        if (is_callable($condition)) {
            $entry = $entry ?? $this->getCurrentEntry();

            return $condition($entry);
        }

        return $condition ?? false;
    }

    /**
     * Check if any operations are allowed for a Crud Panel. Return false if not.
     *
     * @param  string|array  $operation_array
     * @param  \Model  $entry
     * @return bool
     */
    public function hasAccessToAny(array|string $operation_array, $entry = null): bool
    {
        foreach ((array) $operation_array as $key => $operation) {
            if ($this->hasAccess($operation, $entry) == true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if all operations are allowed for a Crud Panel. Return false if not.
     *
     * @param  array  $operation_array  Permissions.
     * @param  \Model  $entry
     * @return bool
     */
    public function hasAccessToAll(array|string $operation_array, $entry = null): bool
    {
        foreach ((array) $operation_array as $key => $operation) {
            if (! $this->hasAccess($operation, $entry)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a operation is allowed for a Crud Panel. Fail if not.
     *
     * @param  string  $operation
     * @param  \Model  $entry
     * @return bool
     *
     * @throws \Backpack\CRUD\Exception\AccessDeniedException in case the operation is not enabled
     */
    public function hasAccessOrFail(string $operation, $entry = null): bool
    {
        if (! $this->hasAccess($operation, $entry)) {
            throw new AccessDeniedException(trans('backpack::crud.unauthorized_access', ['access' => $operation]));
        }

        return true;
    }

    /**
     * Get an operation's access condition, if set. A condition
     * can be anything, but usually a boolean or a callable.
     *
     * @param  string  $operation
     * @return bool|callable|null
     */
    public function getAccessCondition(string $operation): bool|callable|null
    {
        return $this->get($operation.'.access');
    }

    /**
     * Set the condition under which an operation is allowed for a Crud Panel.
     *
     * @param  string|array  $operation
     * @param  bool|callable|null  $condition
     * @return void
     */
    public function setAccessCondition(array|string $operation, bool|callable|null $condition): void
    {
        foreach ((array) $operation as $op) {
            $this->set($op.'.access', $condition);
        }
    }

    /**
     * Check if an operation has an access condition already set.
     * A condition can be anything, but usually a boolean or a callable.
     *
     * @param  string  $operation
     * @return bool
     */
    public function hasAccessCondition(string $operation): bool
    {
        return $this->get($operation.'.access') !== null;
    }
}
