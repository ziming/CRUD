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
                <span title="{{ $column['text'] }}" style="width: 100%; float: left; background-color: {{ $column['text'] }}">&nbsp;</span><br />
            @endif
            {{ $column['text'] }}
        @else
            @if($column['text'] != "-")
                <span title="{!! $column['text'] !!}" style="width: 100%; float: left; background-color: {!! $column['text'] !!}">&nbsp;</span><br />
            @endif
            {!! $column['text'] !!}
        @endif
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
