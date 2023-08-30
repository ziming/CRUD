@php
  // check if user is logged in and verified
  $isLoggedInAndVerified = backpack_user() && (config('backpack.base.setup_email_verification_routes', false) ? backpack_user()->hasVerifiedEmail() : true);
@endphp

{{-- show error using sidebar layout if looged in AND on an admin page; otherwise use a blank page --}}
@extends(backpack_view($isLoggedInAndVerified && backpack_theme_config('layout') ? 'layouts.'.backpack_theme_config('layout') : 'errors.blank'))

@section('content')
<div class="row">
  <div class="col-md-12 text-center">
    <div class="error_number">
      <small>{{ strtoupper(trans('backpack::base.error.title', ['error' => ''])) }}</small><br>
      {{ $error_number }}
      <hr>
    </div>
    <div class="error_title text-muted">
      @yield('title')
    </div>
    @if($isLoggedInAndVerified)
    <div class="error_description text-muted">
      <small>
        @yield('description')
      </small>
    </div>
    @endif
  </div>
</div>
@endsection
