{{-- regular object attribute --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        ********
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
