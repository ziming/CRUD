<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Support;

trait HasSubfields
{
    /**
     * When subfields are defined, pass them through the guessing function
     * so that they have label, relationship attributes, etc.
     *
     * @param  array  $subfields  Subfield definition array
     * @return self
     */
    public function subfields($subfields)
    {
        $callAttributeMacro = ! isset($this->attributes['subfields']);
        $this->attributes['subfields'] = $subfields;
        $this->attributes = $this->crud()->makeSureFieldHasNecessaryAttributes($this->attributes);
        if ($callAttributeMacro) {
            $this->callRegisteredAttributeMacros();
        }

        return $this->save();
    }
}
