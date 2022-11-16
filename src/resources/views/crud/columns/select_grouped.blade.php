{{-- single relationships (1-1, 1-n) --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);

    $column['attribute'] = $column['attribute'] ?? (new $column['model'])->identifiableAttribute();
    $related_model = $column['model'];
    $group_by_model = (new $related_model)->{$column['group_by']}()->getRelated();
    $categories = $group_by_model::with($column['group_by_relationship_back'])->get();

    if (isset($column['model'])) {
        $categorylessEntries = $related_model::doesnthave($column['group_by'])->get();
    }

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    if(is_array($column['value'])) {
        $column['value'] = json_encode($column['value']);
    }
@endphp

@foreach ($categories as $category)
    @foreach ($category->{$column['group_by_relationship_back']} as $subEntry)
        @if ( ( old($column['name']) && old($column['name']) == $subEntry->getKey() ) || (isset($column['value']) && $subEntry->getKey()==$column['value']))
            {{ $subEntry->{$column['attribute']} }}
        @endif
    @endforeach
@endforeach
