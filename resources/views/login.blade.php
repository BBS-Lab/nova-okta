@php($novaName = \Laravel\Nova\Nova::name())
@php($novaLogo = \Laravel\Nova\Nova::logo())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full font-sans antialiased">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ trans('nova-okta::messages.login_title') }} &middot; {{ $novaName }}</title>

    {{-- Reuse Nova's compiled stylesheet when it has been published so the page
         looks native; skip it gracefully otherwise (e.g. during tests). --}}
    @if (file_exists(public_path('vendor/nova/mix-manifest.json')))
        <link rel="stylesheet" href="{{ mix('app.css', 'vendor/nova') }}">
    @endif

    {{-- Honour nova.brand.colors: overrides the primary palette used below. --}}
    <style>{!! \Laravel\Nova\Nova::brandColorsCSS() !!}</style>

    <script>
        if (localStorage.novaTheme === 'dark' || (!('novaTheme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        }
    </script>
</head>
<body class="min-h-full text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-900">
<div class="py-6 px-1 md:px-2 lg:px-6">
    <div class="mx-auto py-8 max-w-sm flex justify-center text-gray-900 dark:text-white">
        {{-- Honour nova.brand.logo when configured, otherwise the brand name. --}}
        @if ($novaLogo && str_contains($novaLogo, '<svg'))
            {!! $novaLogo !!}
        @else
            <h1 class="text-3xl font-bold text-center">{{ $novaName }}</h1>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-8 w-[25rem] mx-auto">
        @if ($error = session('okta::error'))
            <p class="mb-6 text-center text-red-500">{{ $error }}</p>
        @endif

        @if (config('nova-okta.password_login') && \Illuminate\Support\Facades\Route::has('nova.login'))
            <form method="POST" action="{{ route('nova.login') }}" class="flex flex-col gap-6">
                @csrf

                @if ($errors->any())
                    <div class="space-y-1">
                        @foreach ($errors->all() as $message)
                            <p class="text-red-500">{{ $message }}</p>
                        @endforeach
                    </div>
                @endif

                <div>
                    <label class="block mb-2" for="email">{{ trans('nova-okta::messages.email') }}</label>
                    <input id="email" name="email" type="email" autocomplete="email" required autofocus
                        value="{{ old('email') }}"
                        class="w-full form-control form-input form-control-bordered">
                </div>

                <div>
                    <label class="block mb-2" for="password">{{ trans('nova-okta::messages.password') }}</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="w-full form-control form-input form-control-bordered">
                </div>

                <label class="flex items-center gap-2" for="remember">
                    <input id="remember" name="remember" type="checkbox" class="checkbox">
                    <span>{{ trans('nova-okta::messages.remember') }}</span>
                </label>

                <button type="submit"
                    class="w-full flex justify-center shadow rounded focus:outline-none focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-900 px-3 h-9 items-center text-sm font-bold">
                    {{ trans('nova-okta::messages.log_in') }}
                </button>

                @if (\Illuminate\Support\Facades\Route::has('nova.pages.password.email'))
                    <a href="{{ route('nova.pages.password.email') }}" class="text-center no-underline text-primary-500 font-bold">
                        {{ trans('nova-okta::messages.forgot_password') }}
                    </a>
                @endif
            </form>
        @endif

        {{-- Okta button in Nova's brand primary, so it respects nova.brand.colors. --}}
        <a href="{{ route('nova-okta.login') }}"
            id="okta-signin-submit"
            @class([
                'w-full flex justify-center items-center h-9 px-3 shadow rounded text-sm font-bold no-underline focus:outline-none focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-900',
                'mt-6' => config('nova-okta.password_login'),
            ])>
            {{ trans('nova-okta::messages.log_in_with_okta') }}
        </a>
    </div>
</div>
</body>
</html>
