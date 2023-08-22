<?php

namespace Backpack\CRUD\app\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NestedMenuDropdown extends MenuDropdown
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return backpack_view('components.nested-menu-dropdown');
    }
}
