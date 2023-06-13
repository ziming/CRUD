@php
    $id = (isset($entry) && $entry != null) ? $entry->getKey() : false;
    $access = $parameters['access'] ?? Str::of($button->name)->studly();
    $url = $parameters['url'] ?? url($crud->route. ($id ? '/'.$id.'/' : '/'). Str::of($button->name)->kebab());
    $classes = $parameters['classes'] ?? ($button->stack == 'top' ? 'btn btn-outline-primary' : 'btn btn-sm btn-link');
    $icon = $parameters['icon'] ?? '';
    $text = $parameters['text'] ?? Str::of($button->name)->headline();
@endphp

@if ($crud->hasAccess($access))
    <a href="{{ $url }}" class="{{ $classes }}">
        <i class="{{ $icon }}"></i> {{ $text }}
    </a>
@endif
