@extends('admin.auth.layouts.master')

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
  <div class="authentication-inner row">
    <!-- /Left Text -->
    <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
        <img
          src="{!! asset('assets/img/illustrations/auth-login-illustration-light.png') !!}"
          alt="auth-login-cover"
          class="img-fluid my-5 auth-illustration"
          data-app-light-img="illustrations/auth-login-illustration-light.png"
          data-app-dark-img="illustrations/auth-login-illustration-dark.png" />

        <img
          src="{!! asset('assets/img/illustrations/bg-shape-image-light.png') !!}"
          alt="auth-login-cover"
          class="platform-bg"
          data-app-light-img="illustrations/bg-shape-image-light.png"
          data-app-dark-img="illustrations/bg-shape-image-dark.png" />
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Login -->
    <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
      <div class="w-px-400 mx-auto">
        <!-- Logo -->
        <div class="app-brand mb-4 d-flex justify-content-center align-items-center">
            @if (isset($siteSettings) && $siteSettings->logo && file_exists(uploadsDir('front') . $siteSettings->logo))
            <img src="{!! asset(uploadsDir('front') . $siteSettings->logo) !!}" class="img-fluid" style="">
            @else
            <img src="{!! asset('assets/img/logo-placeholder.png') !!}" class="img-fluid" style="max-height: 150px;">
            @endif
        </div>
        <!-- /Logo -->
        <h3 class="mb-1">Welcome to {{ $siteSettings->site_title ?? config('app.name', 'Laravel') }}! 👋</h3>
        <p class="mb-4">Please sign-in to your admin account.</p>

        <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('admin.auth.login') }}">
            @csrf
          <div class="mb-3">
            <label for="email" class="form-label">Email or Username</label>
            <input
              type="text"
              class="form-control"
              id="email"
              name="email"
              placeholder="Enter your email or username"
              autofocus />
          </div>
          <div class="mb-3 form-password-toggle">
            <div class="d-flex justify-content-between">
              <label class="form-label" for="password">Password</label>
              <a href="{{ route('admin.password.request')  }}">
                <small>Forgot Password?</small>
              </a>
            </div>
            <div class="input-group input-group-merge">
              <input
                type="password"
                id="password"
                class="form-control"
                name="password"
                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                aria-describedby="password" />
              <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
            </div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remember-me" />
              <label class="form-check-label" for="remember-me"> Remember Me </label>
            </div>
          </div>
          <button class="btn btn-primary d-grid w-100">Sign in</button>
        </form>

        <!-- <p class="text-center">
          <span>New on our platform?</span>
          <a href="auth-register-cover.html">
            <span>Create an account</span>
          </a>
        </p> -->

        <!-- <div class="divider my-4">
          <div class="divider-text">or</div>
        </div>

        <div class="d-flex justify-content-center">
          <a href="javascript:;" class="btn btn-icon btn-label-facebook me-3">
            <i class="tf-icons fa-brands fa-facebook-f fs-5"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon btn-label-google-plus me-3">
            <i class="tf-icons fa-brands fa-google fs-5"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon btn-label-twitter">
            <i class="tf-icons fa-brands fa-twitter fs-5"></i>
          </a>
        </div> -->
      </div>
    </div>
    <!-- /Login -->
  </div>
</div>
@endsection
