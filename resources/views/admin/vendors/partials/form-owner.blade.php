@php
    $owner = $owner ?? null;
    $requirePassword = $requirePassword ?? true;
@endphp

<div class="col-12 mt-2">
    <h6 class="mb-3">{!! __('general.company_admin_user') !!}</h6>
</div>

@if ($owner)
    <input type="hidden" name="owner[id]" value="{{ $owner->id }}">
@endif

<div class="mb-3 col-md-6">
    <label class="form-label" for="owner_first_name">{!! __('general.first_name') !!} <span class="required-fl">*</span></label>
    <input type="text" class="form-control" id="owner_first_name" name="owner[first_name]" value="{{ old('owner.first_name', $owner->first_name ?? '') }}" required>
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="owner_last_name">{!! __('general.last_name') !!}</label>
    <input type="text" class="form-control" id="owner_last_name" name="owner[last_name]" value="{{ old('owner.last_name', $owner->last_name ?? '') }}">
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="owner_email">{!! __('general.email') !!} <span class="required-fl">*</span></label>
    <input type="email" class="form-control" id="owner_email" name="owner[email]" value="{{ old('owner.email', $owner->email ?? '') }}" required>
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="owner_phone">{!! __('general.contact_number') !!}</label>
    <input type="text" class="form-control phone-mask" id="owner_phone" name="owner[phone]" value="{{ old('owner.phone', $owner->phone ?? '') }}">
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="owner_password">{!! __('general.password') !!} @if ($requirePassword)<span class="required-fl">*</span>@endif</label>
    <input type="password" class="form-control" id="owner_password" name="owner[password]" @if ($requirePassword) required @endif>
</div>
<div class="mb-3 col-md-6">
    <label class="form-label" for="owner_password_confirmation">{!! __('general.confirm_password') !!} @if ($requirePassword)<span class="required-fl">*</span>@endif</label>
    <input type="password" class="form-control" id="owner_password_confirmation" name="owner[password_confirmation]" @if ($requirePassword) required @endif>
</div>
