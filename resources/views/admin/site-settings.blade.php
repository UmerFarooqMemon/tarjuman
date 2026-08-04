@extends('admin.layouts.app')

@section('css')
<link rel="stylesheet" href="{!! asset('assets/vendor/libs/intl-tel-input/css/intlTelInput.min.css') !!}" />
<link rel="stylesheet" href="{!! asset('assets/css/admin-site-settings-gallery.css') !!}" />
<style>
    .iti { width: 100%; }
    .iti .form-control { width: 100%; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{!! __('general.general_settings') !!}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.site-settings.update', $records->id) }}" id="site-settings-form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 col-6 d-none">
                        <label class="form-label" for="basic-default-fullname">{!! __('general.site_title') !!} <span class="required-fl">*</span></label>
                        <input type="text" class="form-control" id="basic-default-fullname" name="site_title" maxlength="190" value="{{ old('site_title', $records->site_title) }}" />
                    </div>

                    <ul class="nav nav-pills mb-4 flex-wrap gap-1" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link active" id="settings-general-tab" data-bs-toggle="pill" data-bs-target="#settings-general" role="tab" aria-controls="settings-general" aria-selected="true">
                                {!! __('general.settings_tab_general') !!}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" id="settings-branding-tab" data-bs-toggle="pill" data-bs-target="#settings-branding" role="tab" aria-controls="settings-branding" aria-selected="false">
                                {!! __('general.settings_tab_branding') !!}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" id="settings-socials-tab" data-bs-toggle="pill" data-bs-target="#settings-socials" role="tab" aria-controls="settings-socials" aria-selected="false">
                                {!! __('general.settings_tab_socials') !!}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" id="settings-accepted-by-tab" data-bs-toggle="pill" data-bs-target="#settings-accepted-by" role="tab" aria-controls="settings-accepted-by" aria-selected="false">
                                {!! __('general.settings_tab_accepted_by') !!}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" id="settings-certified-by-tab" data-bs-toggle="pill" data-bs-target="#settings-certified-by" role="tab" aria-controls="settings-certified-by" aria-selected="false">
                                {!! __('general.settings_tab_certified_by') !!}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" id="settings-regulated-by-tab" data-bs-toggle="pill" data-bs-target="#settings-regulated-by" role="tab" aria-controls="settings-regulated-by" aria-selected="false">
                                {!! __('general.settings_tab_regulated_by') !!}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- General --}}
                        <div class="tab-pane fade show active" id="settings-general" role="tabpanel" aria-labelledby="settings-general-tab" tabindex="0">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="legal_business_name">{!! __('general.legal_business_name') !!}</label>
                                    <input type="text" class="form-control" id="legal_business_name" name="legal_business_name" maxlength="190" value="{{ old('legal_business_name', $records->legal_business_name) }}" />
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="trade_license_number">{!! __('general.trade_license_number') !!}</label>
                                    <input type="text" class="form-control" id="trade_license_number" name="trade_license_number" maxlength="64" value="{{ old('trade_license_number', $records->trade_license_number) }}" />
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="contact_email">{!! __('general.contact_email') !!} <span class="required-fl">*</span></label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" maxlength="190" value="{{ old('contact_email', $records->contact_email) }}" />
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="contact_phone">{!! __('general.contact_phone') !!}</label>
                                    <input
                                        type="tel"
                                        id="contact_phone"
                                        class="form-control"
                                        name="contact_phone"
                                        autocomplete="tel"
                                        data-intl-phone
                                        data-initial-phone="{{ old('contact_phone', $records->contact_phone) }}"
                                        value="{{ old('contact_phone', $records->contact_phone) }}" />
                                    <div class="invalid-feedback" data-intl-phone-error hidden></div>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="address_en">{!! __('general.address_en') !!}</label>
                                    <input type="text" id="address_en" class="form-control" name="address" maxlength="190" value="{{ old('address', $records->address) }}" />
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="address_ar">{!! __('general.address_ar') !!}</label>
                                    <input type="text" id="address_ar" class="form-control" name="address_ar" maxlength="190" dir="rtl" value="{{ old('address_ar', $records->address_ar) }}" />
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="platform_currency">{!! __('general.platform_currency') !!} <span class="required-fl">*</span></label>
                                    <select class="form-select select2" id="platform_currency" name="currency" required data-currency-select>
                                        @foreach (gccCurrencies() as $code => $currency)
                                            <option
                                                value="{{ $code }}"
                                                data-icon="{{ currencyIconUrl($code) }}"
                                                data-native="{{ $currency['symbol_native'] }}"
                                                @selected(old('currency', $records->currency ?? platformCurrency()) === $code)>
                                                {{ $code }} — {{ app()->getLocale() === 'ar' ? $currency['name_ar'] : $currency['name_en'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text d-flex align-items-center gap-2 mt-2">
                                        <span>{!! __('general.platform_currency_help') !!}</span>
                                        <span class="currency-option ms-auto" id="platform-currency-preview">
                                            {!! currencyIconHtml(old('currency', $records->currency ?? platformCurrency()), 'currency-icon currency-icon--lg') !!}
                                            <strong>{{ old('currency', $records->currency ?? platformCurrency()) }}</strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-3 col-12">
                                    <label class="form-label" for="copyright_line">{!! __('general.copyright_line') !!}</label>
                                    <textarea id="copyright_line" class="form-control" name="copyright" maxlength="65000" rows="4">{{ old('copyright', $records->copyright) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Branding --}}
                        <div class="tab-pane fade" id="settings-branding" role="tabpanel" aria-labelledby="settings-branding-tab" tabindex="0">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="logo_en">{!! __('general.logo_en') !!}</label>
                                    <input type="hidden" name="previous_logo" value="{{ $records->logo }}" />
                                    <input type="file" id="logo_en" name="logo" class="form-control" accept="image/*">
                                    @if ($records->logo != '' && file_exists(uploadsDir('front') . $records->logo))
                                        <div class="mt-2">
                                            <img src="{!! asset(uploadsDir('front'). $records->logo) !!}" alt="" title="Logo EN" class="img-responsive" style="max-height: 80px;" />
                                        </div>
                                    @endif
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="logo_ar">{!! __('general.logo_ar') !!}</label>
                                    <input type="hidden" name="previous_logo_ar" value="{{ $records->logo_ar }}" />
                                    <input type="file" id="logo_ar" name="logo_ar" class="form-control" accept="image/*">
                                    @if ($records->logo_ar != '' && file_exists(uploadsDir('front') . $records->logo_ar))
                                        <div class="mt-2">
                                            <img src="{!! asset(uploadsDir('front'). $records->logo_ar) !!}" alt="" title="Logo AR" class="img-responsive" style="max-height: 80px;" />
                                        </div>
                                    @endif
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="favicon_logo">{!! __('general.favicon_logo') !!}</label>
                                    <input type="hidden" name="previous_favicon" value="{{ $records->favicon }}" />
                                    <input type="file" id="favicon_logo" name="favicon" class="form-control" accept="image/*">
                                    @if ($records->favicon != '' && file_exists(uploadsDir('front') . $records->favicon))
                                        <div class="avatar mr-1 avatar-xl mt-2">
                                            <img src="{!! asset(uploadsDir('front'). $records->favicon) !!}" alt="" title="Favicon" class="img-responsive rounded" />
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12 mb-3 mt-2">
                                    <h6 class="mb-1">{!! __('general.branding_colors') !!}</h6>
                                    <small class="text-muted">{!! __('general.branding_colors_hint') !!}</small>
                                </div>

                                @include('admin.partials.gradient-color-field', [
                                    'label' => __('general.primary_color'),
                                    'startName' => 'primary_color',
                                    'endName' => 'primary_color_end',
                                    'angleName' => 'primary_color_angle',
                                    'defaultStart' => '#000000',
                                    'defaultEnd' => '#000000',
                                    'records' => $records,
                                ])

                                @include('admin.partials.gradient-color-field', [
                                    'label' => __('general.secondary_color'),
                                    'startName' => 'secondary_color',
                                    'endName' => 'secondary_color_end',
                                    'angleName' => 'secondary_color_angle',
                                    'defaultStart' => '#FFFFFF',
                                    'defaultEnd' => '#FFFFFF',
                                    'records' => $records,
                                ])

                                @include('admin.partials.gradient-color-field', [
                                    'label' => __('general.primary_button_color'),
                                    'startName' => 'primary_button_color',
                                    'endName' => 'primary_button_color_end',
                                    'angleName' => 'primary_button_color_angle',
                                    'defaultStart' => '#000000',
                                    'defaultEnd' => '#000000',
                                    'records' => $records,
                                ])

                                @include('admin.partials.gradient-color-field', [
                                    'label' => __('general.secondary_button_color'),
                                    'startName' => 'secondary_button_color',
                                    'endName' => 'secondary_button_color_end',
                                    'angleName' => 'secondary_button_color_angle',
                                    'defaultStart' => '#FFFFFF',
                                    'defaultEnd' => '#FFFFFF',
                                    'records' => $records,
                                ])

                                <div class="col-12 mb-3 mt-2">
                                    <h6 class="mb-1">{!! __('general.button_text_border_colors') !!}</h6>
                                    <small class="text-muted">{!! __('general.button_text_border_colors_hint') !!}</small>
                                </div>

                                @include('admin.partials.solid-color-field', [
                                    'label' => __('general.primary_button_text_color'),
                                    'name' => 'primary_button_text_color',
                                    'default' => '#FFFFFF',
                                    'records' => $records,
                                ])

                                @include('admin.partials.solid-color-field', [
                                    'label' => __('general.secondary_button_text_color'),
                                    'name' => 'secondary_button_text_color',
                                    'default' => '#000000',
                                    'records' => $records,
                                ])

                                @include('admin.partials.solid-color-field', [
                                    'label' => __('general.primary_button_border_color'),
                                    'name' => 'primary_button_border_color',
                                    'default' => '#000000',
                                    'records' => $records,
                                ])

                                @include('admin.partials.solid-color-field', [
                                    'label' => __('general.secondary_button_border_color'),
                                    'name' => 'secondary_button_border_color',
                                    'default' => '#000000',
                                    'records' => $records,
                                ])
                            </div>
                        </div>

                        {{-- Socials --}}
                        <div class="tab-pane fade" id="settings-socials" role="tabpanel" aria-labelledby="settings-socials-tab" tabindex="0">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="instagram_url">{!! __('general.instagram_url') !!}</label>
                                    <input type="text" id="instagram_url" class="form-control" name="instagram" maxlength="190" value="{{ old('instagram', $records->instagram) }}" />
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="tiktok_url">{!! __('general.tiktok_url') !!}</label>
                                    <input type="text" id="tiktok_url" class="form-control" name="tiktok" maxlength="190" value="{{ old('tiktok', $records->tiktok) }}" />
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="facebook_url">{!! __('general.facebook_url') !!}</label>
                                    <input type="text" id="facebook_url" class="form-control" name="facebook" maxlength="190" value="{{ old('facebook', $records->facebook) }}" />
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="whatsapp_url">{!! __('general.whatsApp_url') !!}</label>
                                    <input type="text" id="whatsapp_url" class="form-control" name="whatsapp" maxlength="190" value="{{ old('whatsapp', $records->whatsapp) }}" />
                                </div>
                            </div>
                        </div>

                        {{-- Accepted By --}}
                        <div class="tab-pane fade" id="settings-accepted-by" role="tabpanel" aria-labelledby="settings-accepted-by-tab" tabindex="0">
                            <p class="text-muted mb-3">{!! __('general.settings_gallery_accepted_by_help') !!}</p>
                            @include('admin.site-settings.partials.image-gallery', [
                                'group' => 'accepted_by',
                                'images' => $records->galleryFilenames('accepted_by_images'),
                            ])
                        </div>

                        {{-- Certified By --}}
                        <div class="tab-pane fade" id="settings-certified-by" role="tabpanel" aria-labelledby="settings-certified-by-tab" tabindex="0">
                            <p class="text-muted mb-3">{!! __('general.settings_gallery_certified_by_help') !!}</p>
                            @include('admin.site-settings.partials.image-gallery', [
                                'group' => 'certified_by',
                                'images' => $records->galleryFilenames('certified_by_images'),
                            ])
                        </div>

                        {{-- Regulated By --}}
                        <div class="tab-pane fade" id="settings-regulated-by" role="tabpanel" aria-labelledby="settings-regulated-by-tab" tabindex="0">
                            <p class="text-muted mb-3">{!! __('general.settings_gallery_regulated_by_help') !!}</p>
                            @include('admin.site-settings.partials.image-gallery', [
                                'group' => 'regulated_by',
                                'images' => $records->galleryFilenames('regulated_by_images'),
                            ])
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">{!! __('general.save') !!}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.branding-color-modal')
@endsection

@section('footer-js')
<script src="https://cdn.jsdelivr.net/npm/@jaames/iro@5"></script>
<script src="{!! asset('assets/js/admin-branding-color-picker.js') !!}"></script>
<script>
  document.documentElement.setAttribute('data-iti-utils', @json(asset('assets/vendor/libs/intl-tel-input/js/utils.js')));
  document.documentElement.setAttribute('data-iti-invalid-msg', @json(__('general.invalid_phone_number')));
</script>
<script src="{!! asset('assets/vendor/libs/intl-tel-input/js/intlTelInput.min.js') !!}"></script>
<script src="{!! asset('assets/js/admin-vendor-phone.js') !!}"></script>
<script src="{!! asset('assets/js/admin-site-settings-gallery.js') !!}"></script>
<script>
(function ($) {
    var $select = $('#platform_currency');
    if ($select.length && $.fn.select2) {
        function currencyOption(option) {
            if (!option.id) {
                return option.text;
            }
            var icon = $(option.element).data('icon');
            var $wrap = $('<span class="currency-option"></span>');
            if (icon) {
                $wrap.append($('<img>', {
                    src: icon,
                    class: 'currency-icon',
                    alt: option.id,
                    width: 16,
                    height: 16
                }));
            }
            $wrap.append($('<span></span>').text(option.text));
            return $wrap;
        }

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
            if ($select.parent().hasClass('position-relative')) {
                $select.unwrap();
            }
        }

        $select.wrap('<div class="position-relative"></div>').select2({
            placeholder: "{!! __('general.select') !!}",
            width: '100%',
            dropdownParent: $select.parent(),
            templateResult: currencyOption,
            templateSelection: currencyOption,
            escapeMarkup: function (markup) { return markup; }
        });

        var icons = @json(collect(gccCurrencies())->mapWithKeys(fn ($c, $code) => [$code => currencyIconUrl($code)]));

        $select.on('change', function () {
            var code = $(this).val();
            var $preview = $('#platform-currency-preview');
            if (!$preview.length || !code) {
                return;
            }
            var icon = icons[code];
            $preview.empty();
            if (icon) {
                $preview.append($('<img>', {
                    src: icon,
                    class: 'currency-icon currency-icon--lg',
                    alt: code,
                    width: 24,
                    height: 24
                }));
            }
            $preview.append($('<strong></strong>').text(code));
        });
    }

    // Open the tab that contains the first validation error (if any).
    var errorFields = @json($errors->keys());
    if (errorFields.length) {
        var field = document.querySelector('[name="' + errorFields[0] + '"]');
        var pane = field ? field.closest('.tab-pane') : null;
        if (pane && pane.id) {
            var trigger = document.querySelector('[data-bs-target="#' + pane.id + '"]');
            if (trigger && typeof bootstrap !== 'undefined') {
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            }
        }
    }
})(jQuery);
</script>
@endsection
