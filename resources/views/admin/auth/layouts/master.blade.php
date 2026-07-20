<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-wide customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{!! asset('assets') !!}/"
  data-template="vertical-menu-template">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Vuexy admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Vuexy admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="PIXINVENT">
    <title>{!! $siteSettings->site_title ?? config('app.name', 'Laravel') !!} - Login</title>
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
    <link rel="stylesheet" href="{!! asset('assets/vendor/css/rtl/theme-default.css') !!}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{!! asset('assets/css/demo.css') !!}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/node-waves/node-waves.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') !!}" />
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/typeahead-js/typeahead.css') !!}" />
    <!-- Vendor -->
    <link rel="stylesheet" href="{!! asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') !!}" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{!! asset('assets/vendor/css/pages/page-auth.css') !!}" />

    <!-- Helpers -->
    <script src="{!! asset('assets/vendor/js/helpers.js') !!}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{!! asset('assets/vendor/js/template-customizer.js') !!}"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{!! asset('assets/js/config.js') !!}"></script>

    <!-- BEGIN: Toastr CSS-->
    <link rel="stylesheet" type="text/css" href="{!! asset('assets/admin/app-assets/vendors/css/extensions/toastr.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! asset('assets/admin/app-assets/css/plugins/extensions/toastr.css') !!}">
    <!-- END: Toastr CSS-->

    @include('admin.partials.branding-styles')
    @yield('css')

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body>
    <!-- BEGIN: Content-->
    <div class="app-content content">
        @yield('content')
    </div>
    <!-- END: Content-->


    <script src="{!! asset('assets/vendor/libs/jquery/jquery.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/popper/popper.js') !!}"></script>
    <script src="{!! asset('assets/vendor/js/bootstrap.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/node-waves/node-waves.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/hammer/hammer.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/i18n/i18n.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/typeahead-js/typeahead.js') !!}"></script>
    <script src="{!! asset('assets/vendor/js/menu.js') !!}"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{!! asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') !!}"></script>
    <script src="{!! asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') !!}"></script>

    <!-- Main JS -->
    <script src="{!! asset('assets/js/main.js') !!}"></script>

    <!-- Page JS -->
    <script src="{!! asset('assets/js/pages-auth.js') !!}"></script>
    <!-- END: Theme JS-->
    
    <!-- Toastr -->
    <script src="{!! asset('assets/admin/app-assets/vendors/js/extensions/toastr.min.js') !!}"></script>
    <script src="{!! asset('assets/admin/app-assets/js/scripts/extensions/toastr.js') !!}"></script>
    <!-- END: Page JS-->
    @include('admin.partials.errors')
</body>
<!-- END: Body-->

</html>