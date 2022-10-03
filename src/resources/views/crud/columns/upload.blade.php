@php
    $column['escaped'] = $column['escaped'] ?? false;
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    $column['wrapper']['target'] = $column['wrapper']['target'] ?? '_blank';
    $column_wrapper_href = $column['wrapper']['href'] ?? function($file_path, $disk) { return ( !is_null($disk) ?asset(\Storage::disk($disk)->url($file_path)):asset($file_path) ); };

    $column['wrapper']['href'] = $column_wrapper_href instanceof \Closure ? $column_wrapper_href($column['value'], $column['disk']) : $column_wrapper_href;
@endphp
@include('crud::columns.text')