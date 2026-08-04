@php
    /** @var string $group accepted_by|certified_by|regulated_by */
    $group = $group ?? 'accepted_by';
    $column = $column ?? $group.'_images';
    $keepName = $group.'_keep[]';
    $uploadName = $group.'_uploads[]';
    $images = old($group.'_keep', $images ?? []);
    if (! is_array($images)) {
        $images = [];
    }
@endphp

<div
    class="settings-image-gallery"
    data-gallery="{{ $group }}"
    data-keep-name="{{ $keepName }}"
    data-upload-name="{{ $uploadName }}">
    <input type="hidden" name="{{ $group }}_managed" value="1">
    <div class="settings-image-grid" data-gallery-grid>
        @foreach ($images as $filename)
            @if (is_string($filename) && $filename !== '' && file_exists(uploadsDir('front').$filename))
                <div class="settings-image-tile" data-existing data-filename="{{ $filename }}">
                    <img src="{{ asset(uploadsDir('front').$filename) }}" alt="">
                    <input type="hidden" name="{{ $keepName }}" value="{{ $filename }}">
                    <button type="button" class="settings-image-tile__delete" data-gallery-remove aria-label="{!! __('general.delete') !!}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            @endif
        @endforeach

        <label class="settings-image-tile settings-image-tile--add" data-gallery-add>
            <input type="file" accept="image/jpeg,image/jpg,image/png,image/svg+xml,image/webp" multiple hidden data-gallery-picker>
            <span class="settings-image-tile__plus" aria-hidden="true">+</span>
            <span class="settings-image-tile__add-label">{!! __('general.add_image') !!}</span>
        </label>
    </div>
    <input type="file" name="{{ $uploadName }}" accept="image/jpeg,image/jpg,image/png,image/svg+xml,image/webp" multiple hidden data-gallery-files>
    <small class="text-muted d-block mt-2">{!! __('general.settings_gallery_hint') !!}</small>
</div>
