{{-- single relationships (1-1, 1-n) --}}
@php
    $column['attribute'] = $column['attribute'] ?? (new $column['model'])->identifiableAttribute();
    $column['value'] = $column['value'] ?? $crud->getRelatedEntriesAttributes($entry, $column['entity'], $column['attribute']);
    $column['escaped'] = $column['escaped'] ?? true;    
    $column['label'] = $column['label'] ?? '';
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['limit'] = $column['limit'] ?? 32;

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    foreach ($column['value'] as &$value) {
        $value = Str::limit($value, $column['limit'], '…');
    }
@endphp

<span>
    @if(count($column['value']))
        {!! $column['label'] !!}: 
        {{ $column['prefix'] }}
        @foreach($column['value'] as $key => $text)
            @php
                $related_key = $key;
            @endphp

            <span class="d-inline-flex">
                @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
                    @if($column['escaped'])
                        {{ $text }}@if(!$loop->last),@endif
                    @else
                        {!! $text !!}
                    @endif
                @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
                
            </span>
        @endforeach
        {{ $column['suffix'] }}
    @else
        {!! $column['label'] !!}: 
        {{ $column['default'] ?? '-' }}
    @endif
</span>
