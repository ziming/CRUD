{{-- regular object attribute --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['escaped'] = $column['escaped'] ?? true;
    $column['text'] = $column['default'] ?? '-';

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    if(!empty($column['value'])) {
        $column['text'] = $column['value'];
    }
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        @if($column['escaped'])
            @if($column['text'] != "-")
                <span title="{{ $column['text'] }}" class="btn rounded-circle" style="font-size: 0.5rem; background-color: {{ $column['text'] }}">&nbsp;</span>
            @else
                {{ $column['text'] }}
            @endif
        @else
            {!! $column['text'] !!} &nbsp;<span title="{{ $column['text'] }}" class="btn" style="width: 16px; height: 16px; padding: 0px; border-radius: 0px; background-color: {{ $column['text'] }}">&nbsp;</span>
        @endif
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
