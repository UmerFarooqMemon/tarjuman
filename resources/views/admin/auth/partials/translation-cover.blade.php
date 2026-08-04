@php
    $coverTitle = $siteSettings->site_title ?? config('app.name', 'Tarjuman');
@endphp

<div class="auth-translation-cover" aria-hidden="true">
    <div class="auth-translation-cover__mesh"></div>
    <div class="auth-translation-cover__orb auth-translation-cover__orb--one"></div>
    <div class="auth-translation-cover__orb auth-translation-cover__orb--two"></div>

    <div class="auth-translation-cover__stage">
        <p class="auth-translation-cover__brand">{{ $coverTitle }}</p>
        <p class="auth-translation-cover__tagline">{{ __('general.auth_cover_tagline') }}</p>

        <div class="auth-translation-cover__exchange" data-auth-exchange>
            <div class="auth-translation-cover__lane auth-translation-cover__lane--en">
                <span class="auth-translation-cover__badge">EN</span>
                <p class="auth-translation-cover__phrase" data-auth-en>Welcome</p>
            </div>

            <div class="auth-translation-cover__bridge" aria-hidden="true">
                <span class="auth-translation-cover__pulse"></span>
                <span class="auth-translation-cover__arrow">⇄</span>
            </div>

            <div class="auth-translation-cover__lane auth-translation-cover__lane--ar" dir="rtl">
                <span class="auth-translation-cover__badge">AR</span>
                <p class="auth-translation-cover__phrase" data-auth-ar>مرحباً</p>
            </div>
        </div>

        <ul class="auth-translation-cover__chips list-unstyled mb-0">
            <li class="auth-translation-cover__chip" style="--d: 0s">Hello → مرحبا</li>
            <li class="auth-translation-cover__chip" style="--d: 0.4s">Document → وثيقة</li>
            <li class="auth-translation-cover__chip" style="--d: 0.8s">Certified → معتمد</li>
            <li class="auth-translation-cover__chip" style="--d: 1.2s">Estimate → تقدير</li>
        </ul>
    </div>
</div>
