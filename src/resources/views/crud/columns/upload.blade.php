@php
    $column['escaped'] = $column['escaped'] ?? false;
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    $column['wrapper']['target'] = $column['wrapper']['target'] ?? '_blank';
    $column['wrapper']['href'] = asset('/').$column['value'];
@endphp
@include('crud::columns.text')