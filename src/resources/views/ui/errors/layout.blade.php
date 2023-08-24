@extends(backpack_view(backpack_user() && backpack_theme_config('layout') ? 'layouts.'.backpack_theme_config('layout') : 'errors.blank'))
{{-- show error using sidebar layout if looged in AND on an admin page; otherwise use a blank page --}}

@section('content')
<div class="row">
  <div class="col-md-12 text-center">
    <div class="error_number">
      <small>{{ strtoupper(trans('backpack::base.error.title', ['error' => $error_number])) }}</small><br>
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
@endsection
