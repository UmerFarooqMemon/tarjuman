<div class="modal fade" id="languageFormModal" tabindex="-1" aria-labelledby="languageFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.languages.store') }}" id="languageForm">
                @csrf
                <input type="hidden" name="_method" id="language_form_method" value="POST">
                <input type="hidden" name="language_id" id="language_id" value="{{ old('language_id') }}">
                <input type="hidden" name="update_url" id="language_update_url" value="{{ old('update_url') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="languageFormModalLabel">{!! __('general.create_language') !!}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.cancel') }}"></button>
                </div>

                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="code">{!! __('general.code') !!} <span class="required-fl">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('code') is-invalid @enderror"
                                id="code"
                                name="code"
                                value="{{ old('code') }}"
                                maxlength="16"
                                required
                                placeholder="en, ar, ur">
                            <div class="form-text text-body" id="code_locked_help" hidden style="color: #000 !important;">{!! __('general.language_system_locale_code_locked') !!}</div>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="native_name">{!! __('general.native_name') !!} <span class="required-fl">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('native_name') is-invalid @enderror"
                                id="native_name"
                                name="native_name"
                                value="{{ old('native_name') }}"
                                required
                                placeholder="English, العربية, اردو">
                            @error('native_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="direction">{!! __('general.direction') !!} <span class="required-fl">*</span></label>
                            <select class="form-select @error('direction') is-invalid @enderror" id="direction" name="direction" required>
                                <option value="ltr" @selected(old('direction', 'ltr') === 'ltr')>LTR</option>
                                <option value="rtl" @selected(old('direction', 'ltr') === 'rtl')>RTL</option>
                            </select>
                            @error('direction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="sort_order">{!! __('general.sort_order') !!}</label>
                            <input
                                type="number"
                                class="form-control @error('sort_order') is-invalid @enderror"
                                id="sort_order"
                                name="sort_order"
                                min="0"
                                value="{{ old('sort_order', 0) }}">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{!! __('general.cancel') !!}</button>
                    <button type="submit" class="btn btn-primary">{!! __('general.save') !!}</button>
                </div>
            </form>
        </div>
    </div>
</div>
