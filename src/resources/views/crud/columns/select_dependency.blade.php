{{-- single relationships (1-1, 1-n) --}}
@php
    $column['attribute'] = $column['attribute'] ?? (new $column['model'])->identifiableAttribute();
    $column['value'] = $column['value'] ?? $crud->getRelatedEntriesAttributes($entry, $column['entity'], $column['attribute']);
    $column['escaped'] = $column['escaped'] ?? true;
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
        {{ $column['suffix'] }}
    @else
        <?php
            $entity_model = $crud->getModel();
        ?>

        @php
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


            $field_text = $column['default'] ?? '-';

            $secondary_data = ($secondary_ids ? implode(", ", $secondary_ids) : "");
        @endphp

        @php
            echo ($secondary_data) ? $secondary_data : $field_text;
        @endphp
    @endif
</span>
