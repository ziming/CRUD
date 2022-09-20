{{-- regular object attribute --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['text'] = $column['default'] ?? '-';
    $max_value = (isset($column['max'])) ? $column['max'] : '100';

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    if(is_array($column['value'])) {
        $column['value'] = json_encode($column['value']);
    }

    if(!empty($column['value'])) {
        $column['text'] = ($column['value'] / $max_value) * 100;
    }
@endphp

@php
    if($column['text'] != "-"):
@endphp
<div title="{{ $column['text'] }}%" style="float: left; width: 100%; border:1px blue solid;"> <span style="width: {{ $column['text'] }}%; background: green; float: left;">&nbsp;</span></div>
@php
    endif;
@endphp
