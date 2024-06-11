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
                if($locale === $editLocale) continue;

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
        $showTranslationNotice = empty($translatedAttributes) && ! empty($entry->getTranslatableAttributes()) && ! $crud->getRequest()->input('_use_fallback');
    @endphp
    <div @if($showTranslationNotice) class="mb-2 row text-center" @else class="mb-2 text-right text-end" @endif>
    @if($showTranslationNotice)
        <div class="alert ml-0 col-md-10" role="alert">
            {{ trans('backpack::crud.no_attributes_translated', ['locale' => $translatableLocales[$editLocale]]) }}
            @if(count($translatedLocales) === 1)
                <a 
                    href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $editLocale }}&_use_fallback={{current($translatedLocales)}}" 
                    class="btn btn-outline-primary" 
                    role="button">
                    {{trans('backpack::crud.no_attributes_translated_href_text', ['locale' => $translatableLocales[current($translatedLocales)]]) }}
                </a>
        @else
            {{trans('backpack::crud.no_attributes_translated_href_text', ['locale' => '']) }}:
            <div class="btn-group @if($showTranslationNotice) col-md-2 text-{{$showTranslationNotice ? 'center' : 'right text-end'}}"  style="margin-top:0.8em; display:inline;" @else " @endif>
                <a 
                    type="button" 
                    class="btn btn-sm btn-link pr-0" 
                    href="#"
                    ><span> {{trans('backpack::crud.language')}} </span>
                </a>
            <a class="btn btn-sm btn-link dropdown-toggle text-primary pl-1" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                @foreach ($translatedLocales as $locale)
                    <a 
                        class="dropdown-item" 
                        href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $editLocale }}&_use_fallback={{ $locale }}">
                        {{ $translatableLocales[$locale] }}
                    </a>
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
            <a 
                class="dropdown-item" 
                href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $key }}">
                {{ $locale }}
            </a>
        @endforeach
        </ul>
    </div>
</div>
