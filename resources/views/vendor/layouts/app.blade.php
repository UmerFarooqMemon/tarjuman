<!doctype html>
<html
  lang="{{ str_replace('_', '-', app()->getLocale()) }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}"
  data-theme="theme-default"
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
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') !!}" />

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

    {{-- Apply nav layout + card style before paint (same keys/defaults as Admin; dock is default) --}}
    <script>
      (function () {
        try {
          var mode = localStorage.getItem('admin-nav-layout');
          if (mode !== 'sidebar' && mode !== 'dock') {
            mode = 'dock';
          }
          document.documentElement.setAttribute('data-admin-nav-layout', mode);

          var cardStyle = localStorage.getItem('admin-card-style');
          if (cardStyle !== 'glass' && cardStyle !== 'classic') {
            cardStyle = 'classic';
          }
          document.documentElement.setAttribute('data-admin-card-style', cardStyle);
        } catch (e) {
          document.documentElement.setAttribute('data-admin-nav-layout', 'dock');
          document.documentElement.setAttribute('data-admin-card-style', 'classic');
        }
      })();
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .template-customizer-open-btn { display: none !important; }
        .required-fl { color: red; font-weight: 500 }
    </style>
    @include('admin.partials.branding-styles')
    <link rel="stylesheet" href="{!! asset('assets/css/admin-dock-nav.css') !!}?v=20260807i" />
    <link rel="stylesheet" href="{!! asset('assets/css/admin-appearance.css') !!}?v=20260807e" />
    <link rel="stylesheet" href="{!! asset('assets/css/currency-icons.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/css/vendor-order-details.css') !!}?v=20260807h" />
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

    @include('vendor.layouts.partials.dock-nav.index')
    @include('admin.layouts.partials.appearance-modal')
    @include('partials.notifications-modal')
    @include('vendor.layouts.partials.order-details-modal')

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
    <script src="{!! asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') !!}"></script>
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
    <script src="{!! asset('assets/js/admin-nav-layout.js') !!}"></script>
    <script src="{!! asset('assets/js/admin-appearance.js') !!}"></script>
    <script src="{!! asset('assets/js/admin-dock-nav.js') !!}?v=20260807d"></script>
    <script src="{!! asset('assets/js/vendor-order-modal.js') !!}?v=20260807g"></script>

    @auth('vendor')
    @php($notificationsDropdown = notificationsDropdownConfig('vendor'))
    @if ($notificationsDropdown)
    <script>
    window.__notificationsNewLabel = @json(__('general.new_notification'));
    window.__notificationsI18n = {
      markAsRead: @json(__('general.mark_as_read')),
      noUnread: @json(__('general.no_unread_notifications')),
      noRead: @json(__('general.no_read_notifications')),
      seeAll: @json(__('general.see_all')),
    };
    </script>
    @if (config('broadcasting.default') === 'pusher' && filled(config('broadcasting.connections.pusher.key')))
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
    window.__notificationsBroadcast = {
      key: @json(config('broadcasting.connections.pusher.key')),
      cluster: @json(config('broadcasting.connections.pusher.options.cluster')),
    };
    window.Echo = new Echo({
      broadcaster: 'pusher',
      key: window.__notificationsBroadcast.key,
      cluster: window.__notificationsBroadcast.cluster || 'mt1',
      forceTLS: true,
      authEndpoint: @json($notificationsDropdown['broadcastAuthUrl']),
      auth: { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }
    });
    </script>
    @endif
    <script src="{{ asset('assets/js/admin-notifications.js') }}?v=20260807f"></script>
    @endif
    @endauth
    @stack('footer-js')
    @yield('footer-js')
</body>
</html>
