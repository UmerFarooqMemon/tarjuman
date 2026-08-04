@extends('admin.auth.layouts.master')

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
  <div class="authentication-inner row">
    <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg d-flex justify-content-center align-items-center p-0">
        @include('admin.auth.partials.translation-cover')
      </div>
    </div>

    <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
      <div class="w-px-400 mx-auto">
        <div class="app-brand mb-4 d-flex justify-content-center align-items-center">
            <img src="{{ siteLogoUrl() }}" class="img-fluid" alt="{{ $siteSettings->site_title ?? config('app.name') }}">
        </div>
        <h3 class="mb-1">{{ __('general.welcome') }} {{ $siteSettings->site_title ?? config('app.name', 'Laravel') }}</h3>
        <p class="mb-4">{{ __('general.login_page_content') }}</p>

        <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('admin.auth.login') }}">
            @csrf
          <div class="mb-3">
            <label for="email" class="form-label">{{ __('general.email') }}</label>
            <input
              type="text"
              class="form-control"
              id="email"
              name="email"
              placeholder="{{ __('general.email') }}"
              autofocus />
          </div>
          <div class="mb-3 form-password-toggle">
            <div class="d-flex justify-content-between">
              <label class="form-label" for="password">{{ __('general.password') }}</label>
              <a href="{{ route('admin.password.request')  }}">
                <small>{{ __('general.forgot_password') }}</small>
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
              <input class="form-check-input" type="checkbox" id="remember-me" name="remember" value="1" />
              <label class="form-check-label" for="remember-me">{{ __('general.remember_me') }}</label>
            </div>
          </div>
          <button class="btn btn-primary d-grid w-100">{{ __('general.login') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
