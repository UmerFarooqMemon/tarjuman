@php
    $crudLocales = $crudLocales ?? crudLocales();
@endphp

<div class="modal fade" id="authorityFormModal" tabindex="-1" aria-labelledby="authorityFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.authorities.store') }}" id="authorityForm">
                @csrf
                <input type="hidden" name="_method" id="authority_form_method" value="POST">
                <input type="hidden" name="authority_id" id="authority_id" value="{{ old('authority_id') }}">
                <input type="hidden" name="update_url" id="authority_update_url" value="{{ old('update_url') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="authorityFormModalLabel">{!! __('general.create_authority') !!}</h5>
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
                        @foreach ($crudLocales as $locale)
                        <div class="col-md-6">
                            <div class="card border mb-3">
                                <div class="card-header py-2">
                                    <strong>{{ $locale->native_name ?: $locale->displayName() }}</strong>
                                    <small class="text-muted">({{ strtoupper($locale->code) }})</small>
                                </div>
                                <div class="card-body">
                                    <label class="form-label" for="name_{{ $locale->code }}">{!! __('general.name') !!} <span class="required-fl">*</span></label>
                                    <input
                                        type="text"
                                        class="form-control @error("translations.{$locale->code}.name") is-invalid @enderror"
                                        id="name_{{ $locale->code }}"
                                        name="translations[{{ $locale->code }}][name]"
                                        value="{{ old("translations.{$locale->code}.name") }}"
                                        required
                                        @if($locale->isRtl()) dir="rtl" @endif>
                                    @error("translations.{$locale->code}.name")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endforeach
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
