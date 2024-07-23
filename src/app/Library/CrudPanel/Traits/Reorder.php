<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Properties and methods for the Reorder operation.
 */
trait Reorder
{
    /**
     * Change the order and parents of the given elements, according to the NestedSortable AJAX call.
     *
     * @param  array  $request  The entire request from the NestedSortable AJAX Call.
     * @return int The number of items whose position in the tree has been changed.
     */
    public function updateTreeOrder($request)
    {
        $primaryKey = $this->model->getKeyName();

        // we use the upsert method that should update the values of the matching ids.
        // it has the drawback of creating new entries when the id is not found
        // for that reason we get a list of all the ids and filter the ones
        // sent in the request that are not in the database
        $itemKeys = $this->model->query()->select($primaryKey)->get()->pluck($primaryKey);

        $reorderItems = collect($request)->filter(function ($item) use ($itemKeys) {
            return $item['item_id'] !== '' && $item['item_id'] !== null && $itemKeys->contains($item['item_id']);
        })->map(function ($item) use ($primaryKey) {
            $item[$primaryKey] = (int) $item['item_id'];
            $item['parent_id'] = empty($item['parent_id']) ? null : (int) $item['parent_id'];
            $item['depth'] = empty($item['depth']) ? null : (int) $item['depth'];
            $item['lft'] = empty($item['left']) ? null : (int) $item['left'];
            $item['rgt'] = empty($item['right']) ? null : (int) $item['right'];
            // unset mapped items properties.
            unset($item['item_id'], $item['left'], $item['right']);

            return $item;
        })->toArray();

        DB::transaction(function () use ($reorderItems, $primaryKey, $itemKeys) {
            $reorderItemsBindString = implode(',', array_fill(0, count($reorderItems), '?'));
            foreach (['parent_id', 'depth', 'lft', 'rgt'] as $column) {
                $query = '';
                $bindings = [];
                $query .= "UPDATE {$this->model->getTable()} SET {$column} = CASE ";
                foreach ($reorderItems as $item) {
                    $query .= "WHEN {$primaryKey} = ? THEN ? ";
                    $bindings[] = $item[$primaryKey];
                    $bindings[] = $item[$column];
                }
                array_push($bindings, ...$itemKeys->toArray());
                $query .= "ELSE {$column} END WHERE {$primaryKey} IN ({$reorderItemsBindString})";
                DB::statement($query, $bindings);
            }
        });

        return count($reorderItems);
    }

    /**
     * Enable the Reorder functionality in the CRUD Panel for users that have the been given access to 'reorder' using:
     * $this->crud->allowAccess('reorder');.
     *
     * @param  string  $label  Column name that will be shown on the labels.
     * @param  int  $max_level  Maximum hierarchy level to which the elements can be nested (1 = no nesting, just reordering).
     */
    public function enableReorder($label = 'name', $max_level = 1)
    {
        $this->setOperationSetting('enabled', true);
        $this->setOperationSetting('label', $label);
        $this->setOperationSetting('max_level', $max_level);
    }

    /**
     * Disable the Reorder functionality in the CRUD Panel for all users.
     */
    public function disableReorder()
    {
        $this->setOperationSetting('enabled', false);
    }

    /**
     * Check if the Reorder functionality is enabled or not.
     *
     * @return bool
     */
    public function isReorderEnabled()
    {
        return $this->getOperationSetting('enabled');
    }
}
