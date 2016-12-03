<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

trait Hooks
{
    /**
     * Runs before any storing takes place, allows modification to the request.
     *
     * @param $request - provides access to the incoming request
     *
     * @return \Backpack\CRUD\app\Http\Requests\CrudRequest
     */
    public function beforeStore($request)
    {
        return $request;
    }

    /**
     * Runs before any updating takes place, allows modification to the request.
     *
     * @param $request - provides access to the incoming request
     *
     * @return \Backpack\CRUD\app\Http\Requests\CrudRequest
     */
    public function beforeUpdate($request)
    {
        return $request;
    }

    /**
     * Runs before any create/update proccesses take place,
     * but AFTER the beforeStore/beforeUpdate.
     *
     * @param $request - provides access to the incoming request
     *
     * @return \Backpack\CRUD\app\Http\Requests\CrudRequest
     */
    public function beforeSave($request)
    {
        return $request;
    }

    /**
     * Runs after the store action has succeeded,
     * and provides access to the request and new item.
     *
     * @param $request - provides access to the incoming request
     * @param $item - the newly created model
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function afterStore($request, $item)
    {
        return $item;
    }

    /**
     * Runs after the update action has succeeded,
     * and provides access to the request and updated item.
     *
     * @param $request - provides access to the incoming request
     * @param $item - the updated model
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function afterUpdate($request, $item)
    {
        return $item;
    }

    /**
     * Runs after the create/update action has succeeded,
     * but AFTER the afterUpdate/afterStore
     * and provides access to the request and updated item.
     *
     * @param $request - provides access to the incoming request
     * @param $item - the updated model
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function afterSave($request, $item)
    {
        return $item;
    }
}
