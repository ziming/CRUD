@include('crud::columns.checklist', ['column' => $column['subfields']['primary']]) <br />

@php
	$column = $column['subfields']['secondary'];

    $column['attribute'] = $column['attribute'] ?? (new $column['model'])->identifiableAttribute();
    $column['value'] = $column['value'] ?? $crud->getRelatedEntriesAttributes($entry, $column['entity'], $column['attribute']);
    $column['escaped'] = $column['escaped'] ?? true;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['limit'] = $column['limit'] ?? 32;
    $field_text = $column['default'] ?? '-';

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    foreach ($column['value'] as &$value) {
        $value = Str::limit($value, $column['limit'], '…');
    }

    $entity_model = $crud->getModel();
            
    $id = $entry->id;

    $entity_dependencies = $entity_model->with($column['entity_primary'])
      ->with($column['entity_primary'].'.'.$column['entity'])
      ->find($id);

    $primary_array = $entity_dependencies->{$column['entity_primary']}->toArray();

    $secondary_ids = [];
    if($primary_array)
    {
        foreach ($primary_array as $primary_item) {
            foreach ($primary_item[$column['entity']] as $second_item) {
                $secondary_ids[$second_item['id']] = $second_item[$column['attribute']];
            }
        }
    }

    $secondary_data = ($secondary_ids ? implode(", ", $secondary_ids) : "");
@endphp

<span>
    @if(count($column['value']))
        {{ $column['prefix'] }}
        @foreach($column['value'] as $key => $text)
            @php
                $related_key = $key;
            @endphp

            <span class="d-inline-flex">
                @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
                    @if($column['escaped'])
                        {{ $text }}
                    @else
                        {!! $text !!}
                    @endif
                @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')

                @if(!$loop->last), @endif
            </span>
        @endforeach

        {{-- Show Secondary depenedency if exists with primary --}}

        @php
            echo ($secondary_data) ? ", ".$secondary_data : $field_text;
        @endphp

        {{ $column['suffix'] }}
    @else

        {{-- Show Secondary depenedency --}}
        @php
            echo ($secondary_data) ? $secondary_data : $field_text;
        @endphp
    @endif
</span>