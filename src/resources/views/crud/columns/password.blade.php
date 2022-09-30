@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['value'] = str_repeat("*", strlen($column['value']));
    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 6;

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    if(is_array($column['value'])) {
        $column['value'] = json_encode($column['value']);
    }

    if(!empty($column['value'])) {
        $column['text'] = Str::limit($column['value'], $column['limit'], '');
    }
@endphp

<span>
    {{ $column['text'] }}
</span>
