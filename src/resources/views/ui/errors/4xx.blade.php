@extends(backpack_view('errors.layout'))

@php
  $error_number ??= 400;
@endphp

@section('title')
  {{ trans('backpack::base.error.'.$error_number) }}
@endsection

@section('description')
  {!! $exception?->getMessage() ? e($exception->getMessage()) : trans('backpack::base.error.message_4xx', [
    'href_back' => 'href="javascript:history.back()"',
    'href_homepage' => 'href="'.url('').'"',
  ]) !!}
@endsection
