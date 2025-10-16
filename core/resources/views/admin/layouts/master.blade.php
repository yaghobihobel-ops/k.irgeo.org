<!DOCTYPE html>
{{-- Enable locale-aware direction for the admin dashboard --}}
<html lang="{{ config('app.locale') }}" dir="{{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ gs()->siteName($pageTitle ?? '') }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ siteFavicon() }}">

    <link
        href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800;900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    {{-- Load Vazir font and typography tweaks for Persian locale --}}
    @if (app()->getLocale() === 'fa')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Vazir:wght@300;400;500;700&display=swap" rel="stylesheet">
        <style>
            body,
            button,
            input,
            select,
            textarea {
                font-family: 'Vazir', sans-serif !important;
            }

            body {
                direction: rtl;
            }
        </style>
    @endif

    <script src="{{ asset('assets/admin/js/theme.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">

    @stack('style-lib')

    <link rel="stylesheet" href="{{ asset('assets/admin/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
    @stack('style')
</head>

<body>
    <div class="sidebar-overlay"></div>
    @yield('content')

    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>

    @include('partials.notify')
    @stack('script-lib')

    <script src="{{ asset('assets/global/js/global.js') }}"></script>
    <script src="{{ asset('assets/admin/js/search.js') }}"></script>
    <script src="{{ asset('assets/admin/js/main.js') }}"></script>
    @stack('script')

    <script>
        (function($) {
            "use strict";
            // event when change lang
            $(".langSel").on("click", function() {
                const code = $(this).data('code')
                window.location.href = "{{ route('home') }}/change/" + code;
            });

            //set some property to the window object for access from a js file
            window.app_config = {
                empty_image_url: "{{ asset('assets/images/empty_box.png') }}",
                empty_title: "@lang('No data found')",
                empty_message: "@lang('There are no available data to display.')",
                allow_precision: "{{ gs('allow_precision') }}"
            }
        })(jQuery);
    </script>
</body>
</html>
