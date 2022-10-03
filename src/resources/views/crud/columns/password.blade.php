@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['value'] = str_repeat("*", strlen($column['value']));
    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 6;
@endphp
@include('crud::columns.text')