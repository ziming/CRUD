@php
    // defaults; backwards compatibility with Backpack 4.0 widgets
    $widget['wrapper']['class'] = $widget['wrapper']['class'] ?? $widget['wrapperClass'] ?? 'col-sm-6 col-lg-3';
@endphp

@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_start')

<div @foreach($widget['attributes'] ?? [] as $key => $value) {{ $key }}="{{ $value }}" @endforeach>
    @livewire($widget['component'], $widget['params'] ?? [])
</div>

@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_end')
