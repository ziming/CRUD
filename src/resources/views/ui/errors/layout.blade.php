@extends(backpack_user() ? (backpack_theme_config('layout') ? backpack_view('layouts.'.backpack_theme_config('layout')) : backpack_view('errors.blank')) : backpack_view('errors.blank'))
{{-- show error using sidebar layout if looged in AND on an admin page; otherwise use a blank page --}}

@php
  // Set the page title
  $title = 'Error '.$error_number;
@endphp

@section('content')
<div class="row">
  <div class="col-md-12 text-center">
      <div class="card pb-4">
          <div class="error_number">
              <small>ERROR</small><br>
              {{ $error_number }}
              <hr>
          </div>
          <div class="error_title text-muted">
              @yield('title')
          </div>
          @if(backpack_user())
              <div class="error_description text-muted">
                <small>
                  @yield('description')
                </small>
              </div>
          @endif
      </div>
  </div>
</div>
@endsection
