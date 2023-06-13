<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations\Concerns;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait HasForm
{
    protected function formRoutes(string $operationName, bool $routesHaveIdSegment, string $segment, string $routeName, string $controller): void
    {
        $secondSegment = $routesHaveIdSegment ? '/{id}/' : '/';

        Route::get($segment.$secondSegment.Str::of($operationName)->kebab(), [
            'as'        => $routeName.'.get'.$operationName.'Form',
            'uses'      => $controller.'@get'.$operationName.'Form',
            'operation' => $operationName,
        ]);
        Route::post($segment.$secondSegment.Str::of($operationName)->kebab(), [
            'as'        => $routeName.'.post'.$operationName.'Form',
            'uses'      => $controller.'@post'.$operationName.'Form',
            'operation' => $operationName,
        ]);
    }

    /**
     * Method to handle the GET request and display the View with a Backpack form.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function getForm($id = null)
    {
        if ($id) {
            // Get entry ID from Request (makes sure its the last ID for nested resources)
            $this->data['id'] = $this->crud->getCurrentEntryId() ?? $id;
            $this->data['entry'] = $this->crud->getEntryWithLocale($this->data['id']);
        }

        $this->crud->setOperationSetting('fields', $this->crud->fields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? Str::of($this->crud->getCurrentOperation())->headline();
        $this->data['operation'] = $this->crud->getCurrentOperation();

        $this->data['formAction'] = $this->crud->getOperationSetting('form_action');
        $this->data['formMethod'] = $this->crud->getOperationSetting('form_method');

        return view($this->crud->getOperationSetting('view') ?? 'crud::inc.form_page', $this->data);
    }
}
