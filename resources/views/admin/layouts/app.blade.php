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
    <meta name="description" content="{{ ($siteSettings->site_title ?? config('app.name', 'Tarjuman')) }} admin — manage estimates, vendors, pricing, and translation operations.">
    <meta name="keywords" content="Tarjuman, translation, admin, estimates, vendors, pricing, document translation, UAE">
    <meta name="author" content="{{ $siteSettings->site_title ?? config('app.name', 'Tarjuman') }}">
    <title>{{ $siteSettings->site_title ?? config('app.name', 'Laravel') }}</title>
    @if ($siteSettings?->favicon && file_exists(uploadsDir('front') . $siteSettings->favicon))
    <link rel="apple-touch-icon" href="{!! asset(uploadsDir('front') . $siteSettings->favicon) !!}">
    <link rel="shortcut icon" type="image/x-icon" href="{!! asset(uploadsDir('front') . $siteSettings->favicon) !!}">
    <link rel="icon" type="image/x-icon" href="{!! asset(uploadsDir('front') . $siteSettings->favicon) !!}" />
    @else
    <link rel="apple-touch-icon" href="{!! asset('assets/admin/app-assets/images/ico/apple-icon-120.png') !!}">
    <link rel="shortcut icon" type="image/x-icon" href="{!! $siteSettings->favicon_path ?? asset('assets/admin/app-assets/images/ico/favicon.ico') !!}">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{!! $siteSettings->favicon_path ?? asset('assets/admin/app-assets/images/ico/apple-icon-120.png') !!}" />
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
      rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{!! asset('assets/vendor/fonts/fontawesome.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/fonts/tabler-icons.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/fonts/flag-icons.css') !!}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{!! asset('assets/vendor/css/rtl/core.css') !!}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/css/rtl/theme-semi-dark.css') !!}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{!! asset('assets/css/demo.css') !!}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/node-waves/node-waves.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/typeahead-js/typeahead.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/apex-charts/apex-charts.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/swiper/swiper.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') !!}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{!! asset('assets/vendor/css/pages/cards-advance.css') !!}" />

    <script>
        const currentLocale = "{{ app()->getLocale() }}";
        let langUrl = '';

        if (currentLocale === 'ar') {
            langUrl = "{!! asset('assets/json/locales/ar.json') !!}";
        }
    </script>

    <!-- Helpers -->
    <script src="{!! asset('assets/vendor/js/helpers.js') !!}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{!! asset('assets/vendor/js/template-customizer.js') !!}"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{!! asset('assets/js/config.js') !!}"></script>

    {{-- Apply nav layout + card style before paint to avoid FOUC --}}
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

    <!-- DataTables -->
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-select-bs5/select.bootstrap5.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-fixedcolumns-bs5/fixedcolumns.bootstrap5.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/select2/select2.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') !!}" />

    <!-- BEGIN: Toastr CSS-->
    <link rel="stylesheet" type="text/css" href="{!! asset('assets/admin/app-assets/vendors/css/extensions/toastr.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! asset('assets/admin/app-assets/css/plugins/extensions/toastr.css') !!}">
    <link rel="stylesheet" href="{!! asset('assets/css/admin-toastr.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/css/currency-icons.css') !!}" />
    <!-- END: Toastr CSS-->

    <!-- BEGIN: DataTables CSS-->
    <link rel="stylesheet" type="text/css" href="{!! asset('assets/admin/app-assets/vendors/css/tables/datatable/datatables.min.css') !!}">
    <!-- END: Toastr CSS-->

    <!-- BEGIN: SweetAlert CSS-->
    <!-- <link rel="stylesheet" type="text/css" href="{!! asset('assets/admin/app-assets/vendors/css/extensions/sweetalert2.min.css') !!}"> -->
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/sweetalert2/sweetalert2.css') !!}" />
    <!-- END: Toastr CSS-->

    <script src="{!! asset('assets/vendor/js/helpers.js') !!}"></script>
    <script src="{!! asset('assets/vendor/js/template-customizer.js') !!}"></script>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <!-- <link rel="dns-prefetch" href="//fonts.gstatic.com"> -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet"> -->
    <style>
        .tippy-popper {
            display: none;
        }
        .feather {
            font-size: 14px;
        }

        .required-fl { color: red; font-weight: 500 }

        .dataTables_length {
            padding-left: 15px;
        }

        .template-customizer-open-btn {
            display: none !important;
        }

        /* Compatibility for legacy module views copied from older Vuexy */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group > label {
            display: inline-block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        fieldset {
            border: 0;
            margin: 0 0 1rem;
            padding: 0;
        }

        .card-content {
            padding: 0;
        }

        .card .card-header h4.card-title {
            margin-bottom: 0;
        }

        .table .form-switch .form-check-input {
            width: 2.5em;
            height: 1.25em;
            margin-top: 0;
            cursor: pointer;
        }

        .table .form-switch .form-check-label {
            cursor: pointer;
        }
    </style>
    @include('admin.partials.branding-styles')
    <link rel="stylesheet" href="{!! asset('assets/css/admin-dock-nav.css') !!}?v=20260807i" />
    <link rel="stylesheet" href="{!! asset('assets/css/admin-appearance.css') !!}?v=20260807e" />
    @yield('css')
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('admin.layouts.partials.header')
            <div class="layout-page">
                @include('admin.layouts.partials.navigation')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    @include('admin.layouts.partials.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>

    @include('admin.layouts.partials.dock-nav.index')
    @include('admin.layouts.partials.appearance-modal')
    @include('partials.notifications-modal')
    <form id="logout-form" action="{{ route('admin.auth.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    <!-- BEGIN: Vendor JS-->
    <script src="{!! asset('assets/vendor/libs/jquery/jquery.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/popper/popper.js') !!}"></script>
    <script src="{!! asset('assets/vendor/js/bootstrap.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/node-waves/node-waves.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/hammer/hammer.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/i18n/i18n.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/typeahead-js/typeahead.js') !!}"></script>
    <script src="{!! asset('assets/vendor/js/menu.js') !!}"></script>

    <!-- Main JS -->
    <script src="{!! asset('assets/js/main.js') !!}"></script>

    <!-- Vendors JS -->
    <script src="{!! asset('assets/vendor/libs/apex-charts/apexcharts.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/swiper/swiper.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') !!}"></script>

    <script src="{!! asset('assets/vendor/libs/moment/moment.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/flatpickr/flatpickr.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') !!}"></script>

    <!-- Page JS -->
    <script src="{!! asset('assets/js/admin-list-datatable.js') !!}"></script>

    <!-- Page JS -->
    <script src="{!! asset('assets/js/dashboards-analytics.js') !!}"></script>
    <!-- SweetAlert -->
    <!-- <script src="{!! asset('assets/admin/app-assets/js/scripts/extensions/sweet-alerts.js') !!}"></script>
    <script src="{!! asset('assets/admin/app-assets/vendors/js/extensions/sweetalert2.all.min.js') !!}"></script> -->
    <script src="{!! asset('assets/vendor/libs/sweetalert2/sweetalert2.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') !!}"></script>

    <!-- Page JS -->
    <script src="{!! asset('assets/js/extended-ui-sweetalert2.js') !!}"></script>
    <script src="{!! asset('assets/js/forms-selects.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/select2/select2.js') !!}"></script>
    <!-- Toastr -->
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
    </script>
    <script>
        function logout() {
            document.getElementById("logout-form").submit();
        }

        function deleteConfirmation(id) {

            Swal.fire({
                title: "{!! __('general.are_you_sure') !!}",
                text: "{!! __('general.you_wont_be_able_to_revert_this') !!}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "{!! __('general.yes_delete_it') !!}",
                cancelButtonText: "{!! __('general.cancel') !!}",
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then(function (result) {
                if (result.value) {
                    document.getElementById("deleteForm" + id + "").submit();
                    Swal.fire({
                        icon: 'success',
                        title: "{!! __('general.deleted') !!}",
                        text: "{!! __('general.record_has_been_deleted') !!}",
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: "{!! __('general.cancelled') !!}",
                        text: "{!! __('general.your_data_is_safe') !!}",
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    });
                }
            });
        }
    </script>
    @include('admin.partials.errors')
    <script src="{!! asset('assets/js/admin-nav-layout.js') !!}"></script>
    <script src="{!! asset('assets/js/admin-appearance.js') !!}"></script>
    <script src="{!! asset('assets/js/admin-dock-nav.js') !!}?v=20260807d"></script>

    @auth('admin')
    @php($notificationsDropdown = notificationsDropdownConfig('admin'))
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
