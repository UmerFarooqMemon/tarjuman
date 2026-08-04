@extends('admin.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/admin-cms-preview.css') }}">
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div
    class="admin-cms-editor"
    data-cms-editor
    data-section-type="{{ $section->type }}"
    data-page-slug="{{ $page->slug }}"
    data-preview-base="{{ cms_frontend_url($page->preview_path) }}"
    data-frontend-origin="{{ rtrim(config('cms.frontend_url'), '/') }}"
    data-locale="{{ $locale ?? 'en' }}">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-0">{{ $schema->label() }}</h4>
            <small class="text-muted">{{ $page->title }} · <code>{{ $section->type }}</code></small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.cms.pages.show', $page) }}" class="btn btn-label-secondary">{!! __('general.cancel') !!}</a>
            <button type="submit" form="cms-section-form" class="btn btn-primary">{!! __('general.save') !!}</button>
        </div>
    </div>

    <div class="row g-3 admin-cms-editor__row">
        <div class="col-12 col-xl-5 admin-cms-editor__col">
            <div class="card admin-cms-form-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">{!! __('general.cms_edit_section') !!}</h5>
                    <div class="btn-group btn-group-sm" role="group" data-cms-form-locale>
                        <button type="button" class="btn btn-outline-primary {{ ($locale ?? 'en') === 'en' ? 'active' : '' }}" data-locale="en">{!! __('general.cms_locale_en') !!}</button>
                        <button type="button" class="btn btn-outline-primary {{ ($locale ?? 'en') === 'ar' ? 'active' : '' }}" data-locale="ar">{!! __('general.cms_locale_ar') !!}</button>
                    </div>
                </div>
                <div class="card-body admin-cms-form-card__body">
                    <form
                        id="cms-section-form"
                        method="post"
                        action="{{ route('admin.cms.pages.sections.update', [$page, $section]) }}"
                        enctype="multipart/form-data"
                        data-cms-form>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="preview_locale" value="{{ $locale ?? 'en' }}" data-cms-preview-locale-input>

                        <div class="form-check form-switch mb-4">
                            <input type="hidden" name="is_enabled" value="0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="cms_is_enabled"
                                name="is_enabled"
                                value="1"
                                @checked(old('is_enabled', $section->is_enabled))>
                            <label class="form-check-label" for="cms_is_enabled">{!! __('general.cms_enabled') !!}</label>
                        </div>

                        @include('admin.cms.partials.fields', [
                            'fields' => $schema->fields(),
                            'values' => old('content', $content),
                            'namePrefix' => 'content',
                            'pathPrefix' => '',
                        ])
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7 admin-cms-editor__col">
            <div class="card admin-cms-preview-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0">{!! __('general.cms_preview') !!}</h5>
                        <small class="text-muted">{!! __('general.cms_preview_live_hint') !!}</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="btn-group btn-group-sm" role="group" data-cms-preview-locale>
                            <button type="button" class="btn btn-outline-secondary {{ ($locale ?? 'en') === 'en' ? 'active' : '' }}" data-locale="en">{!! __('general.cms_locale_en') !!}</button>
                            <button type="button" class="btn btn-outline-secondary {{ ($locale ?? 'en') === 'ar' ? 'active' : '' }}" data-locale="ar">{!! __('general.cms_locale_ar') !!}</button>
                        </div>
                        <div class="btn-group btn-group-sm" role="group" data-cms-preview-device>
                            <button type="button" class="btn btn-outline-secondary active" data-device="desktop">{!! __('general.cms_device_desktop') !!}</button>
                            <button type="button" class="btn btn-outline-secondary" data-device="mobile">{!! __('general.cms_device_mobile') !!}</button>
                        </div>
                        <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-label-primary" data-cms-open-preview>
                            <i class="ti ti-external-link"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0 admin-cms-preview-card__body">
                    <div class="admin-cms-preview-frame is-desktop" data-cms-preview-frame>
                        <iframe
                            title="CMS preview"
                            data-cms-preview-iframe
                            src="{{ $previewUrl }}"
                            loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-js')
<script src="{{ asset('assets/js/admin-cms-preview.js') }}"></script>
@endsection
