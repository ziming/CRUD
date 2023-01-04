    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    @if (config('backpack.base.meta_robots_content'))<meta name="robots" content="{{ config('backpack.base.meta_robots_content', 'noindex, nofollow') }}"> @endif

    <meta name="csrf-token" content="{{ csrf_token() }}" /> {{-- Encrypted CSRF token for Laravel, in order for Ajax requests to work --}}
    <title>{{ isset($title) ? $title.' :: '.config('backpack.base.project_name') : config('backpack.base.project_name') }}</title>

    @yield('before_styles')
    @stack('before_styles')

    @basset('https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.css')
    @basset('https://unpkg.com/@digitallyhappy/backstrap@0.5.1/dist/css/legacy.css')
    @basset('https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css')
    @basset('https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-regular-400.woff2')
    @basset('https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-solid-900.woff2')
    @basset('https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-brands-400.woff2')

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    {{-- TODO: We should use Basset for Google Fonts too, but it doesn't quite work yet: --}}
    {{-- @basset('https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,400;0,600;0,700;1,400&display=swap', true, [], 'style') --}}

    @if (config('backpack.base.styles') && count(config('backpack.base.styles')))
        @foreach (config('backpack.base.styles') as $path)
            @if(is_array($path))
                @basset(...$path)
            @else
                @basset($path)
            @endif
        @endforeach
    @endif

    @if (config('backpack.base.mix_styles') && count(config('backpack.base.mix_styles')))
        @foreach (config('backpack.base.mix_styles') as $path => $manifest)
        <link rel="stylesheet" type="text/css" href="{{ mix($path, $manifest) }}">
        @endforeach
    @endif

    @if (config('backpack.base.vite_styles') && count(config('backpack.base.vite_styles')))
        @vite(config('backpack.base.vite_styles'))
    @endif

    @yield('after_styles')
    @stack('after_styles')
