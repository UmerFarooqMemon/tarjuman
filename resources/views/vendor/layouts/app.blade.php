<!doctype html>
<html
  lang="{{ str_replace('_', '-', app()->getLocale()) }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}"
  data-theme="theme-default"
  data-admin-nav-layout="sidebar"
  data-assets-path="{!! asset('assets') !!}/"
  data-template="vertical-menu-template">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="{{ ($siteSettings->site_title ?? config('app.name', 'Tarjuman')) }} vendor portal — manage translation jobs and account details.">
    <meta name="keywords" content="Tarjuman, translation, vendor, portal, orders, UAE">
    <meta name="author" content="{{ $siteSettings->site_title ?? config('app.name', 'Tarjuman') }}">
    <title>{{ $siteSettings->site_title ?? config('app.name', 'Tarjuman') }} — {{ __('general.vendor_portal') }}</title>
    @if ($siteSettings?->favicon && file_exists(uploadsDir('front') . $siteSettings->favicon))
    <link rel="apple-touch-icon" href="{!! asset(uploadsDir('front') . $siteSettings->favicon) !!}">
    <link rel="shortcut icon" type="image/x-icon" href="{!! asset(uploadsDir('front') . $siteSettings->favicon) !!}">
    <link rel="icon" type="image/x-icon" href="{!! asset(uploadsDir('front') . $siteSettings->favicon) !!}" />
    @else
    <link rel="apple-touch-icon" href="{!! asset('assets/admin/app-assets/images/ico/apple-icon-120.png') !!}">
    <link rel="shortcut icon" type="image/x-icon" href="{!! asset('assets/admin/app-assets/images/ico/favicon.ico') !!}">
    <link rel="icon" type="image/x-icon" href="{!! asset('assets/admin/app-assets/images/ico/apple-icon-120.png') !!}" />
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="{!! asset('assets/vendor/fonts/fontawesome.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/fonts/tabler-icons.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/fonts/flag-icons.css') !!}" />

    <link rel="stylesheet" href="{!! asset('assets/vendor/css/rtl/core.css') !!}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/css/rtl/theme-semi-dark.css') !!}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{!! asset('assets/css/demo.css') !!}" />

    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/node-waves/node-waves.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/typeahead-js/typeahead.css') !!}" />

    <script>
        const currentLocale = "{{ app()->getLocale() }}";
        let langUrl = '';
        if (currentLocale === 'ar') {
            langUrl = "{!! asset('assets/json/locales/ar.json') !!}";
        }
    </script>

    <script src="{!! asset('assets/vendor/js/helpers.js') !!}"></script>
    <script src="{!! asset('assets/vendor/js/template-customizer.js') !!}"></script>
    <script src="{!! asset('assets/js/config.js') !!}"></script>

    <script>
      (function () {
        try {
          var style = localStorage.getItem('admin-card-style');
          document.documentElement.setAttribute(
            'data-admin-card-style',
            style === 'glass' ? 'glass' : 'classic'
          );
        } catch (e) {
          document.documentElement.setAttribute('data-admin-card-style', 'classic');
        }
        document.documentElement.setAttribute('data-admin-nav-layout', 'sidebar');
      })();
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .template-customizer-open-btn { display: none !important; }
        .required-fl { color: red; font-weight: 500 }
    </style>
    @include('admin.partials.branding-styles')
    <link rel="stylesheet" href="{!! asset('assets/css/admin-dock-nav.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/css/admin-appearance.css') !!}" />
    @yield('css')
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('vendor.layouts.partials.header')
            <div class="layout-page">
                @include('vendor.layouts.partials.navigation')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    @include('vendor.layouts.partials.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <form id="logout-form" action="{{ route('vendor.auth.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script src="{!! asset('assets/vendor/libs/jquery/jquery.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/popper/popper.js') !!}"></script>
    <script src="{!! asset('assets/vendor/js/bootstrap.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/node-waves/node-waves.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/hammer/hammer.js') !!}"></script>
    <script src="{!! asset('assets/vendor/js/menu.js') !!}"></script>
    <script src="{!! asset('assets/js/main.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/sweetalert2/sweetalert2.js') !!}"></script>
    <script src="{!! asset('assets/admin/app-assets/vendors/js/extensions/toastr.min.js') !!}"></script>
    <script src="{!! asset('assets/admin/app-assets/js/scripts/extensions/toastr.js') !!}"></script>
    <script>
        if (typeof toastr !== 'undefined') {
            toastr.options = Object.assign({}, toastr.options || {}, {
                positionClass: 'toast-bottom-center',
                closeButton: true,
                progressBar: false,
                newestOnTop: true,
                preventDuplicates: false,
                showMethod: 'fadeIn',
                hideMethod: 'fadeOut',
                showDuration: 300,
                hideDuration: 300,
                timeOut: 2000
            });
        }

        function logout() {
            document.getElementById('logout-form').submit();
        }
    </script>
    @include('admin.partials.errors')
    @stack('footer-js')
    @yield('footer-js')
</body>
</html>
