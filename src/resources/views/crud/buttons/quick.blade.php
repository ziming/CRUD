@php
    $access = (function() use ($crud, $button) {
        if (isset($button->meta['access']) && $button->meta['access'] !== null && $button->meta['access'] !== false) {
            return $button->meta['access'];
        }
        return !is_null($crud->get(Str::of($button->name)->studly().'.access'))  ? Str::of($button->name)->studly() : $button->name;
    })();
    $icon = $button->meta['icon'] ?? '';
    $label = $button->meta['label'] ?? Str::of($button->name)->headline();

    $defaultHref = url($crud->route. ($entry?->getKey() ? '/'.$entry?->getKey().'/' : '/') . Str::of($button->name)->kebab());
    $defaultClass = match ($button->stack) {
        'line' => 'btn btn-sm btn-link',
        'top' => 'btn btn-outline-primary',
        'bottom' => 'btn btn-sm btn-secondary',
        default => 'btn btn-outline-primary',
    };

    $wrapper = $button->meta['wrapper'] ?? [];
    $wrapper['element'] = $wrapper['element'] ?? 'a';
    $wrapper['href'] = $wrapper['href'] ?? $defaultHref;
    if (is_a($wrapper['href'], \Closure::class, true)) {
        $wrapper['href'] = ($wrapper['href'])($entry, $crud);
    }
    $wrapper['class'] = $wrapper['class'] ?? $defaultClass;
    //if ajax enabled
    $ajax_enabled = $button->meta['ajax'] ?? false;
    if($ajax_enabled) {
        $wrapper['data-route'] = $wrapper['href'];
		$wrapper['data-method'] = $button->meta['ajax']['method'] ?? 'POST';

        $wrapper['href'] = 'javascript:void(0)';
        $wrapper['onclick'] = 'sendQuickRequest(this)';
		$wrapper['data-button-type'] = 'quick';

        //success message
        $wrapper['data-success-title'] = $button->meta['ajax']['success_title'] ?? trans('backpack::crud.quick_button_ajax_success_title');
        $wrapper['data-success-message'] = $button->meta['ajax']['success_message'] ?? trans('backpack::crud.quick_button_ajax_success_message');
        //error message
        $wrapper['data-error-title'] = $button->meta['ajax']['error_title'] ?? trans('backpack::crud.quick_button_ajax_error_title');
        $wrapper['data-error-message']  = $button->meta['ajax']['error_message'] ?? trans('backpack::crud.quick_button_ajax_error_message');
    }
    //endif ajax enabled
@endphp

@if ($access === true || $crud->hasAccess($access, isset($entry) ? $entry : null))
    <{{ $wrapper['element'] }}
        @foreach ($wrapper as $attribute => $value)
            @if (is_string($attribute))
            {{ $attribute }}="{{ $value }}"
            @endif
        @endforeach
        >
        @if ($icon) <i class="{{ $icon }}"></i> @endif
        {{ $label }}
    </{{ $wrapper['element'] }}>
@endif


@if($ajax_enabled)
{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts') @if (request()->ajax()) @endpush @endif
@bassetBlock('backpack/crud/buttons/quick-button-'.app()->getLocale().'.js')
<script>
	if (typeof sendQuickRequest != 'function') {
	  $("[data-button-type=quick]").unbind('click');

	  function sendQuickRequest(button) {
		// e.preventDefault();
		var route = $(button).attr('data-route');
				$.ajax({
			      url: route,
			      type: $(button).attr('data-method'),
			      success: function(result) {
			          if (result) {
			          	  // Show a success notification bubble
			              new Noty({
		                    type: "success",
							text: '<strong>'+$(button).attr('data-success-title')+'</strong><br>'+$(button).attr('data-success-message'),
		                  }).show();

			          } else {
			              // if the result is an array, it means
			              // we have notification bubbles to show
			          	  if (result instanceof Object) {
			          	  	// trigger one or more bubble notifications
			          	  	Object.entries(result).forEach(function(entry, index) {
			          	  	  var type = entry[0];
			          	  	  entry[1].forEach(function(message, i) {
					          	  new Noty({
				                    type: type,
				                    text: message
				                  }).show();
			          	  	  });
			          	  	});
			          	  } else {// Show an error alert
				              swal({
				              	title: $(button).attr('data-error-title'),
	                            text: $(button).attr('data-error-message'),
				              	icon: "error",
				              	timer: 4000,
				              	buttons: false,
				              });
			          	  }
			          }
			      },
			      error: function(result) {
			          // Show an alert with the result
			          swal({
						title: $(button).attr('data-error-title'),
	                    text: $(button).attr('data-error-message'),
		              	icon: "error",
		              	timer: 4000,
		              	buttons: false,
		              });
			      }
			  });

      }
	}

	// make it so that the function above is run after each DataTable draw event
	// crud.addFunctionToDataTablesDrawEventQueue('sendQuickRequest');
</script>
@endBassetBlock
@if (!request()->ajax()) @endpush @endif
@endif