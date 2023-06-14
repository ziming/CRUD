@php
    $access = $parameters['access'] ?? Str::of($button->name)->studly();
    $icon = $parameters['icon'] ?? '';
    $label = $parameters['label'] ?? Str::of($button->name)->headline();

    $wrapper = $parameters['wrapper'] ?? [];
    $wrapper['element'] = $wrapper['element'] ?? 'a';
    $wrapper['href'] = $wrapper['href'] ?? url($crud->route. ($entry?->getKey() ? '/'.$entry?->getKey().'/' : '/') . Str::of($button->name)->kebab());
    $wrapper['class'] = $wrapper['class'] ?? ($button->stack == 'top' ? 'btn btn-outline-primary' : 'btn btn-sm btn-link');
@endphp

@if ($access == true || $crud->hasAccess($access))
    <{{ $wrapper['element'] }}
        @foreach ($wrapper as $attribute => $value)
            @if (is_string($attribute))
            {{ $attribute }}="{{ $value }}"
            @endif
        @endforeach
        >
        <i class="{{ $icon }}"></i> {{ $label }}
    </{{ $wrapper['element'] }}>
@endif
