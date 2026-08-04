@extends('admin.auth.layouts.master')

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
  <div class="authentication-inner row">
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

    <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
      <div class="w-px-400 mx-auto">
        <div class="app-brand mb-4 d-flex justify-content-center align-items-center">
            <img src="{{ siteLogoUrl() }}" class="img-fluid" alt="" style="max-height: 150px;">
        </div>

        <h3 class="mb-1">{{ __('general.vendor_login_welcome', ['site' => $siteSettings->site_title ?? config('app.name', 'Tarjuman')]) }}</h3>
        <p class="mb-4">{{ __('general.vendor_login_hint') }}</p>

        <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('vendor.auth.login') }}">
            @csrf
          <div class="mb-3">
            <label for="email" class="form-label">{{ __('general.email') }}</label>
            <input
              type="email"
              class="form-control @error('email') is-invalid @enderror"
              id="email"
              name="email"
              value="{{ old('email') }}"
              placeholder="{{ __('general.email') }}"
              autofocus />
            @error('email')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3 form-password-toggle">
            <div class="d-flex justify-content-between">
              <label class="form-label" for="password">{{ __('general.password') }}</label>
            </div>
            <div class="input-group input-group-merge">
              <input
                type="password"
                id="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password"
                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                aria-describedby="password" />
              <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
            </div>
            @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" @checked(old('remember')) />
              <label class="form-check-label" for="remember">{{ __('general.remember_me') }}</label>
            </div>
          </div>
          <button type="submit" class="btn btn-primary d-grid w-100">{{ __('general.login') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
