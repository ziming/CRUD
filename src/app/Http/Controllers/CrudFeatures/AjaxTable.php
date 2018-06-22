<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

trait AjaxTable
{
    /**
     * The search function that is called by the data table.
     *
     * @return  JSON Array of cells in HTML form.
     */
    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $totalRows = $filteredRows = $this->crud->count();

        $input = $this->request->all();
        $cookie_name = get_class($this).'_last_crud_search_request';

        if ($this->crud->shouldClearSearchCookie()) {
            \Cookie::queue(\Cookie::forget($cookie_name));
        } else {
            if (\Cookie::get($cookie_name)) {
                $input = json_decode(\Cookie::get($cookie_name), true);
            }
            \Cookie::queue($cookie_name, json_encode($input), 5);
        }

        // if a search term was present
        if ($input['search'] && $input['search']['value']) {
            // filter the results accordingly
            $this->crud->applySearchTerm($input['search']['value']);
            // recalculate the number of filtered rows
            $filteredRows = $this->crud->count();
        }

        // start the results according to the datatables pagination
        if ($input['start']) {
            $this->crud->skip($input['start']);
        }

        // limit the number of results according to the datatables pagination
        if ($input['length']) {
            $this->crud->take($input['length']);
        }

        // overwrite any order set in the setup() method with the datatables order
        if (isset($input['order'])) {
            $column_number = $input['order'][0]['column'];
            $column_direction = $input['order'][0]['dir'];
            $column = $this->crud->findColumnById($column_number);

            if ($column['tableColumn']) {
                // clear any past orderBy rules
                $this->crud->query->getQuery()->orders = null;
                // apply the current orderBy rules
                $this->crud->orderBy($column['name'], $column_direction);
            }
        }

        $entries = $this->crud->getEntries();

        return $this->crud->getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows);
    }
}
