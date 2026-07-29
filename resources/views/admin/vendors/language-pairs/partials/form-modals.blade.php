@php
    $languages = $languages ?? collect();
    $oldPairs = old('pairs');
    if (! is_array($oldPairs) || count($oldPairs) === 0) {
        $oldPairs = [
            ['source_language_id' => '', 'target_language_id' => ''],
        ];
    }
@endphp

{{-- Create (multi-row) --}}
<div class="modal fade" id="languagePairsCreateModal" tabindex="-1" aria-labelledby="languagePairsCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.vendors.language-pairs.store', $vendor) }}" id="languagePairsCreateForm">
                @csrf
                <input type="hidden" name="_method" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="languagePairsCreateModalLabel">{!! __('general.add_language_pairs') !!}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.cancel') }}"></button>
                </div>

                <div class="modal-body">
                    @if ($errors->any() && old('_method', 'POST') !== 'PUT')
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <p class="text-muted mb-0">{!! __('general.language_pairs_form_help') !!}</p>
                        <button
                            type="button"
                            class="btn btn-sm btn-icon btn-label-primary flex-shrink-0"
                            id="add-language-pair-row"
                            title="{!! __('general.add_another_language_pair') !!}"
                            aria-label="{!! __('general.add_another_language_pair') !!}">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>

                    <div class="row g-2 mb-1">
                        <div class="col">
                            <label class="form-label mb-0">{!! __('general.source_language') !!} <span class="required-fl">*</span></label>
                        </div>
                        <div class="col">
                            <label class="form-label mb-0">{!! __('general.target_language') !!} <span class="required-fl">*</span></label>
                        </div>
                        <div class="col-auto" style="width: 2.5rem;"></div>
                    </div>

                    <div id="language-pairs-rows">
                        @foreach ($oldPairs as $index => $pair)
                            <div class="row g-2 align-items-center mb-2 language-pair-row" data-pair-row>
                                <div class="col">
                                    <select class="form-select" name="pairs[{{ $index }}][source_language_id]" required data-pair-select>
                                        <option value="">{{ __('general.select') }}</option>
                                        @foreach ($languages as $language)
                                            <option value="{{ $language->id }}" @selected((string) data_get($pair, 'source_language_id') === (string) $language->id)>
                                                {{ $language->displayName() }} ({{ strtoupper($language->code) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <select class="form-select" name="pairs[{{ $index }}][target_language_id]" required data-pair-select>
                                        <option value="">{{ __('general.select') }}</option>
                                        @foreach ($languages as $language)
                                            <option value="{{ $language->id }}" @selected((string) data_get($pair, 'target_language_id') === (string) $language->id)>
                                                {{ $language->displayName() }} ({{ strtoupper($language->code) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-icon btn-label-danger"
                                        data-remove-pair-row
                                        title="{!! __('general.delete') !!}"
                                        aria-label="{!! __('general.delete') !!}"
                                        @disabled(count($oldPairs) <= 1)>
                                        <i class="ti ti-trash"></i>
                                    </button>
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

<template id="language-pair-row-template">
    <div class="row g-2 align-items-center mb-2 language-pair-row" data-pair-row>
        <div class="col">
            <select class="form-select" name="pairs[__INDEX__][source_language_id]" required data-pair-select>
                <option value="">{{ __('general.select') }}</option>
                @foreach ($languages as $language)
                    <option value="{{ $language->id }}">
                        {{ $language->displayName() }} ({{ strtoupper($language->code) }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col">
            <select class="form-select" name="pairs[__INDEX__][target_language_id]" required data-pair-select>
                <option value="">{{ __('general.select') }}</option>
                @foreach ($languages as $language)
                    <option value="{{ $language->id }}">
                        {{ $language->displayName() }} ({{ strtoupper($language->code) }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button
                type="button"
                class="btn btn-sm btn-icon btn-label-danger"
                data-remove-pair-row
                title="{!! __('general.delete') !!}"
                aria-label="{!! __('general.delete') !!}">
                <i class="ti ti-trash"></i>
            </button>
        </div>
    </div>
</template>

{{-- Edit (single) --}}
<div class="modal fade" id="languagePairEditModal" tabindex="-1" aria-labelledby="languagePairEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="" id="languagePairEditForm">
                @csrf
                <input type="hidden" name="_method" id="language_pair_edit_method" value="PUT">
                <input type="hidden" name="pair_id" id="language_pair_edit_id" value="{{ old('pair_id') }}">
                <input type="hidden" name="update_url" id="language_pair_update_url" value="{{ old('update_url') }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="languagePairEditModalLabel">{!! __('general.edit_language_pair') !!}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.cancel') }}"></button>
                </div>

                <div class="modal-body">
                    @if ($errors->any() && old('_method') === 'PUT')
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
                            <label class="form-label" for="edit_source_language_id">{!! __('general.source_language') !!} <span class="required-fl">*</span></label>
                            <select class="form-select" id="edit_source_language_id" name="source_language_id" required data-edit-pair-select>
                                <option value="">{{ __('general.select') }}</option>
                                @foreach ($languages as $language)
                                    <option value="{{ $language->id }}" @selected((string) old('source_language_id') === (string) $language->id)>
                                        {{ $language->displayName() }} ({{ strtoupper($language->code) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="edit_target_language_id">{!! __('general.target_language') !!} <span class="required-fl">*</span></label>
                            <select class="form-select" id="edit_target_language_id" name="target_language_id" required data-edit-pair-select>
                                <option value="">{{ __('general.select') }}</option>
                                @foreach ($languages as $language)
                                    <option value="{{ $language->id }}" @selected((string) old('target_language_id') === (string) $language->id)>
                                        {{ $language->displayName() }} ({{ strtoupper($language->code) }})
                                    </option>
                                @endforeach
                            </select>
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
