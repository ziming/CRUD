@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => backpack_url('dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.edit') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</h1>
        <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">{!! $crud->getSubheading() ?? trans('backpack::crud.edit').' '.$crud->entity_name !!}.</p>
        @if ($crud->hasAccess('list'))
            <p class="mb-0 ms-2 ml-2" bp-section="page-subheading-back-button">
                <small><a href="{{ url($crud->route) }}" class="d-print-none font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
            </p>
        @endif
    </section>
@endsection

@section('content')
<div class="row" bp-section="crud-operation-update">
	<div class="{{ $crud->getEditContentClass() }}">
		{{-- Default box --}}

		@include('crud::inc.grouped_errors')

		  <form method="post"
		  		action="{{ url($crud->route.'/'.$entry->getKey()) }}"
				@if ($crud->hasUploadFields('update', $entry->getKey()))
				enctype="multipart/form-data"
				@endif
		  		>
		  {!! csrf_field() !!}
		  {!! method_field('PUT') !!}

		  	@if ($crud->model->translationEnabled())
				@php
					$editLocale = $crud->getRequest()->input('_locale', app()->getLocale());
					$fallbackLocale = app()->getFallbackLocale();
					$translatableAttributes = $entry->getTranslatableAttributes();
					$translatableLocales = $crud->model->getAvailableLocales();
					$translatedLocales = [];
					$translatedAttributes = array_filter($translatableAttributes, function($attribute) use ($entry, $editLocale, &$translatedLocales) {
							$translation = $entry->getTranslation($attribute, $editLocale, false) ?? false;
							if($translation) {
								$translatedLocales[] = $editLocale;
							}
							return $translation;
					});
					$translatedLocales = array_unique($translatedLocales);

					// if translated locales are empty, we need to cycle through all available locales and check if they are translated
					if(empty($translatedLocales)) {
						foreach ($translatableLocales as $locale => $localeName) {
							if($locale == $editLocale) continue;

							array_filter($translatableAttributes, function($attribute) use ($entry, $locale, &$translatedLocales) {
								$translation = $entry->getTranslation($attribute, $locale, false) ?? false;
								if($translation) {
									$translatedLocales[] = $locale;
								}
								return $translation;
							});
						}
						$translatedLocales = array_unique($translatedLocales);
					}
					//dd($translatedLocales);
					$showTranslationNotice = empty($translatedAttributes) && ! empty($entry->getTranslatableAttributes()) && ! $crud->getRequest()->input('_use_fallback');
				@endphp
			  	<div @if($showTranslationNotice) class="mb-2 row text-center" @else class="mb-2 text-right text-end" @endif>
				@if($showTranslationNotice)
					<div class="alert ml-0 col-md-10" role="alert">
						{{ trans('backpack::crud.no_attributes_translated', ['locale' => $translatableLocales[$editLocale]]) }}
						{{-- <a 
							href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $editLocale }}&_use_fallback=true" 
							class="btn btn-outline-primary" 
							role="button">
							{{trans('backpack::crud.no_attributes_translated_href_text', ['locale' => $crud->model->getAvailableLocales()[$fallbackLocale]]) }}
						</a> --}}
						@if(count($translatedLocales) === 1)
						<a 
						href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $editLocale }}&_use_fallback={{current($translatedLocales)}}" 
						class="btn btn-outline-primary" 
						role="button">
						{{trans('backpack::crud.no_attributes_translated_href_text', ['locale' => $translatableLocales[current($translatedLocales)]]) }}
					</a>
					@else
					{{trans('backpack::crud.no_attributes_translated_href_text', ['locale' => '']) }}
						<div class="btn-group @if($showTranslationNotice) col-md-2 text-{{$showTranslationNotice ? 'center' : 'right text-end'}}"  style="margin-top:0.8em; display:inline;" @else " @endif>
							<button 
								type="button" 
								class="btn btn-primary dropdown-toggle" 
								data-toggle="dropdown" 
								data-bs-toggle="dropdown" 
								aria-haspopup="true" 
								aria-expanded="false">
								{{trans('backpack::crud.language')}}: &nbsp; <span class="caret"></span>
						  </button>
						  <ul class="dropdown-menu">
							  @foreach ($translatedLocales as $locale)
								  <a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $editLocale }}&_use_fallback={{ $locale }}">{{ $translatableLocales[$locale] }}</a>
							  @endforeach
						  </ul>
						</div>
						@endif
					</div>
				
				@endif
					<!-- Single button -->
				<div class="btn-group @if($showTranslationNotice) col-md-2 text-{{$showTranslationNotice ? 'center' : 'right text-end'}}"  style="margin-top:0.8em; display:inline;" @else " @endif>
					<button 
						type="button" 
						class="btn btn-primary dropdown-toggle" 
						data-toggle="dropdown" 
						data-bs-toggle="dropdown" 
						aria-haspopup="true" 
						aria-expanded="false">
						{{trans('backpack::crud.language')}}: {{ $crud->model->getAvailableLocales()[request()->input('_locale') ? request()->input('_locale') : app()->getLocale()] }} &nbsp; <span class="caret"></span>
				  </button>
				  <ul class="dropdown-menu">
				  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
					  	<a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $key }}">{{ $locale }}</a>
				  	@endforeach
				  </ul>
				</div>
		    </div>
		    @endif
		      {{-- load the view from the application if it exists, otherwise load the one in the package --}}
		      @if(view()->exists('vendor.backpack.crud.form_content'))
		      	@include('vendor.backpack.crud.form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
		      @else
		      	@include('crud::form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
              @endif
              {{-- This makes sure that all field assets are loaded. --}}
            <div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>
            @include('crud::inc.form_save_buttons')
		  </form>
	</div>
</div>
@endsection

