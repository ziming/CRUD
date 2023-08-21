@extends(backpack_view('errors.layout'))

@php
  $error_number = 500;
@endphp

@section('title')
  {{ trans('backpack::base.error.500') }}
@endsection

@section('description')
  {!! $exception?->getMessage() ? e($exception->getMessage()) : trans('backpack::base.error.message_500') !!}
@endsection
