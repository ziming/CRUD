<!-- select2 multiple -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <select
        name="{{ $field['name'] }}[]"
        id="{{ $field['name'] }}"
        style="width: 100%;"
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control'])
        multiple
        >

        @if (isset($field['model']))
            @foreach ($field['model']::all() as $connected_entity_entry)
                @if( (old($field["name"]) && in_array($connected_entity_entry->getKey(), old($field["name"]))) || (is_null(old($field["name"])) && isset($field['value']) && in_array($connected_entity_entry->getKey(), $field['value']->pluck($connected_entity_entry->getKeyName(), $connected_entity_entry->getKeyName())->toArray())))
                    <option value="{{ $connected_entity_entry->getKey() }}" selected>{{ $connected_entity_entry->{$field['attribute']} }}</option>
                @else
                    <option value="{{ $connected_entity_entry->getKey() }}">{{ $connected_entity_entry->{$field['attribute']} }}</option>
                @endif
            @endforeach
        @endif

        @if (isset($field['options']))
            @foreach ($field['options'] as $key => $value)
                @if((old($field['name']) && (
                        $key == old($field['name']) ||
                        (is_array(old($field['name'])) &&
                        in_array($key, old($field['name']))))) ||
                        (null === old($field['name']) &&
                            ((isset($field['value']) && (
                                        $key == $field['value'] || (
                                                is_array($field['value']) &&
                                                in_array($key, $field['value'])
                                                )
                                        )) ||
                                (isset($field['default']) &&
                                ($key == $field['default'] || (
                                                is_array($field['default']) &&
                                                in_array($key, $field['default'])
                                            )
                                        )
                                ))
                        ))
                    <option value="{{ $key }}" selected>{{ $value }}</option>
                @else
                    <option value="{{ $key }}">{{ $value }}</option>
                @endif
            @endforeach
        @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- include select2 css-->
        <link href="{{ asset('vendor/cord/multiselect/css/multi-select.css') }}" rel="stylesheet" type="text/css" />
        <style type="text/css">
          .search-input { border-bottom: 0; }
          .ms-container { width: 100%; }
          .ms-container .ms-list {
            border-radius: 0;
            -moz-border-radius: 0;
            -webkit-border-radius: 0;
            -webkit-box-shadow: 0;
            -moz-box-shadow: 0;
            box-shadow: 0;
            -webkit-transition: 0;
            -moz-transition: 0;
            -ms-transition: 0;
            -o-transition: 0;
            transition: 0;
          }
          .ms-container .ms-list.ms-focus{
            border-color: 0;
            -webkit-box-shadow: inset 0 1px 1px #fff, 0 0 8px #fff;
            -moz-box-shadow: inset 0 1px 1px #fff, 0 0 8px #fff;
            box-shadow: inset 0 1px 1px #fff, 0 0 8px #fff;
            outline: 0 !important;
          }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- include select2 js-->
        <script src="{{ asset('vendor/cord/multiselect/js/jquery.multi-select.js') }}"></script>
        <script>
            $('#{{ $field['name'] }}').multiSelect({
              @if(isset($field['searchable']) && $field['searchable'])
                selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search..'>",
                selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search..'>",
                afterInit: function(ms){
                  var that = this,
                      $selectableSearch = that.$selectableUl.prev(),
                      $selectionSearch = that.$selectionUl.prev(),
                      selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
                      selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

                  that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                  .on('keydown', function(e){
                    if (e.which === 40){
                      that.$selectableUl.focus();
                      return false;
                    }
                  });

                  that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                  .on('keydown', function(e){
                    if (e.which == 40){
                      that.$selectionUl.focus();
                      return false;
                    }
                  });
                },
                afterSelect: function(){
                  this.qs1.cache();
                  this.qs2.cache();
                },
                afterDeselect: function(){
                  this.qs1.cache();
                  this.qs2.cache();
                }
              @endif
            });
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
