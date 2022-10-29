{{-- regular object attribute --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['text'] = $column['default'] ?? '-';
    $column['progress_class'] = $column['progress_class'] ?? '';
    $column['striped_class'] = $column['is_striped'] ? 'progress-bar-striped' : '';
    $max_value = (isset($column['attributes']['max'])) ? $column['attributes']['max'] : '100';

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

@if ($column['text'] != "-")
<div class="progress">
    <div class="progress-bar {{ $column['progress_class'].' '.$column['striped_class'] }}" role="progressbar" style="width: {{ $column['text'] }}%" aria-valuenow="{{ $column['text'] }}" aria-valuemin="0" aria-valuemax="{{ $max_value }}">{{ $column['text'] }}%</div>
</div>
@else
    <span>{{ $column['text'] }}</span>
@endif
