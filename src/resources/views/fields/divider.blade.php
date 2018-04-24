<!-- text input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label @include('crud::inc.field_attributes') type="label">{!! $field['label'] !!}</label>
</div>

@push('crud_fields_styles')
  <style type="text/css">
    label[type="label"] {
      border: 0;
      padding: 10px 0;
      border-bottom:1px solid #ccc;
    }
  </style>
@endpush
