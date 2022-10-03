@php
    $column['value'] = "******";
    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 6;
@endphp
@include('crud::columns.text')