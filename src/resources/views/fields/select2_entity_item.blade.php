<!-- select2 from ajax -->
@php
    $old_value = old($field['name']) ?? $field['value'] ?? $field['default'] ?? false;
    $field['allows_null'] = $field['allows_null'] ?? true;
@endphp

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>

    <select
        name="{{ $field['name'] }}"
        style="width: 100%"
        id="select2_entity_item_{{ $field['name'] }}"
        data-javascript-function-for-field-initialisation="bpFieldInitSelect2EntityItemElement"
        data-placeholder="{{ $field['placeholder'] }}"
        data-minimumInputLength="{{ $field['minimum_input_length'] }}"
        data-url="{{ $field['data_source'] }}"
        data-item-url="{{ $field['item_data_source'] }}"
        data-method="{{ $field['method'] ?? 'GET' }}"
        data-allows-null="{{ $field['allows_null'] }}"
        data-old-value="{{ $old_value }}"
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control'])
        >
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <!-- include select2 css-->
    <link href="{{ asset('vendor/adminlte/bower_components/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    {{-- allow clear --}}
    @if ($field['allows_null'])
    <style type="text/css">
        .select2-selection__clear::after {
            content: ' {{ trans('backpack::crud.clear') }}';
        }
    </style>
    @endif
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include select2 js-->
    <script src="{{ asset('vendor/adminlte/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script>
        // make select2 work inside bootstrap modals
        (function(){
            var oldSelect2 = jQuery.fn.select2;
            jQuery.fn.select2 = function() {
                const modalParent = jQuery(this).parents('div.modal').first();
                if (arguments.length === 0 && modalParent.length > 0) {
                    arguments = [{dropdownParent: modalParent}];
                } else if (arguments.length === 1
                            && typeof arguments[0] === 'object'
                            && typeof arguments[0].dropdownParent === 'undefined'
                            && modalParent.length > 0) {
                    arguments[0].dropdownParent = modalParent;
                }
                return oldSelect2.apply(this,arguments);
            };
        })();

        // function to add select2 too the backpack field above
        function bpFieldInitSelect2EntityItemElement(element) {
            element.siblings("span.select2-container").remove();

            element.select2({
                theme: 'bootstrap',
                multiple: false,
                placeholder: element.data('placeholder'),
                minimumInputLength: element.data('minimumInputLength'),
                allowClear: element.data('allows-null'),
                ajax: {
                    url: element.data('url'),
                    type: element.data('method'),
                    dataType: 'json',
                    quietMillis: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page, // pagination
                            type: element.parent().prev().children('select').val(),
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        var modelAttribute = element.parent().prev().children('select').children(':selected').data('model-attribute');

                        var result = {
                            results: $.map(data.data, function (item) {
                                return {
                                    text: item[modelAttribute],
                                    id: item["id"]
                                }
                            }),
                           pagination: {
                                 more: data.current_page < data.last_page
                           }
                        };

                        return result;
                    },
                    cache: true
                },
            })
            .on('select2:unselecting', function(e) {
                if (element.data('allows-null')) {
                    $(this).val('').trigger('change');
                    // console.log('cleared! '+$(this).val());
                    e.preventDefault();
                }
            });

            if (element.data('old-value')) {
                $.ajax({
                      method: element.data('method'),
                      url: element.data('item-url'),
                      data: {
                        id: element.data('old-value'),
                        type: element.parent().prev().children('select').val(),
                      }
                    })
                .done(function(data) {
                        var id = data.id;
                        var modelAttribute = element.parent().prev().children('select').children(':selected').data('model-attribute');
                        var text = data[modelAttribute];

                        var newOption = new Option(text, id, false, false);
                        element.append(newOption).trigger('change');
                });
            }
        }
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
