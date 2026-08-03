@extends('admin.layouts.app')

@section('css')
<style>
    .signature-pad {
        border: 1px solid #ccc;
    }
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
                <form method="POST" action="{{ route('admin.site-settings.update', $records->id) }}" class="number-tab-steps row wizard-circle" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                    <div class="mb-3 col-6 d-none">
                        <label class="form-label" for="basic-default-fullname">{!! __('general.site_title') !!} <span class="required-fl">*</span></label>
                        <input type="text" class="form-control" id="basic-default-fullname"  name="site_title" maxlength="190" value="{{ old('site_title', $records->site_title) }}" />
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-default-company">{!! __('general.contact_email') !!} <span class="required-fl">*</span></label>
                        <input type="text" class="form-control" id="basic-default-company" name="contact_email" maxlength="190" value="{{ old('contact_email', $records->contact_email) }}" />
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-default-phone">{!! __('general.address') !!} <span class="required-fl">*</span></label>
                        <input type="text" id="basic-default-phone" class="form-control phone-mask" name="address" maxlength="190" value="{{ old('address', $records->address) }}" />
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-default-email">{!! __('general.contact_phone') !!}</label>
                        <div class="input-group input-group-merge">
                            <input type="text" id="basic-default-email" class="form-control" name="contact_phone" maxlength="190" value="{{ old('contact_phone', $records->contact_phone) }}"  />
                        </div>
                    </div>
                    <div class="mb-3 col-6">
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
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-default-phone">{!! __('general.instagram_url') !!}</label>
                        <input type="text" class="form-control" name="instagram" maxlength="190" value="{{ old('instagram', $records->instagram) }}" />
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-default-phone">{!! __('general.tiktok_url') !!}</label>
                        <input type="text" class="form-control" name="tiktok" maxlength="190" value="{{ old('tiktok', $records->tiktok) }}" />
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-default-phone">{!! __('general.facebook_url') !!}</label>
                        <input type="text" class="form-control" name="facebook" maxlength="190" value="{{ old('facebook', $records->facebook) }}" />
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-default-phone">{!! __('general.whatsApp_url') !!}</label>
                        <input type="text" class="form-control" name="whatsapp" maxlength="190" value="{{ old('whatsapp', $records->whatsapp) }}" />
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-default-phone">{!! __('general.logo') !!}</label>
                        <input type="hidden" name="previous_logo" value="{{ $records->logo }}" />
                        <input type="file" name="logo" class="form-control">
                        @if ($records->logo != '' && file_exists(uploadsDir('front') . $records->logo))
                            <div class="mt-2">
                                <img src="{!! asset(uploadsDir('front'). $records->logo) !!}" alt="" title="Logo" class="img-responsive" />
                            </div>
                        @endif
                    </div>
                    <div class="mb-3 col-6">
                        <label class="form-label" for="basic-default-phone">{!! __('general.favicon_logo') !!}</label>
                        <input type="hidden" name="previous_favicon" value="{{ $records->favicon }}" />
                        <input type="file" name="favicon" class="form-control">
                        @if ($records->favicon != '' && file_exists(uploadsDir('front') . $records->favicon))
                            <div class="avatar mr-1 avatar-xl">
                                <img src="{!! asset(uploadsDir('front'). $records->favicon) !!}" alt="" title="Logo" class="img-responsive" />
                            </div>
                        @endif
                    </div>

                    <div class="col-12 mb-2">
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

                    <div class="col-12 mb-2 mt-2">
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

                    <div class="mb-3">
                        <label class="form-label" for="basic-default-message">{!! __('general.copyright_line') !!}</label>
                        <textarea id="basic-default-message" class="form-control" name="copyright" maxlength="65000" rows="5">{{ old('copyright', $records->copyright) }}</textarea>
                    </div>
                    <div class="mb-3 col-3">
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
(function ($) {
    var $select = $('#platform_currency');
    if (!$select.length || !$.fn.select2) {
        return;
    }

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

    // Re-init select2 with icon templates (forms-selects may have already initialized it)
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
})(jQuery);
</script>
@endsection
