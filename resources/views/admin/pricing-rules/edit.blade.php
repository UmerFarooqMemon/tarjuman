@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-1">{!! __('general.edit_pricing_rule') !!}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.pricing-rules.update', $pricingRule) }}" class="row">
                    @csrf
                    @method('PUT')
                    @include('admin.pricing-rules.partials.form', ['pricingRule' => $pricingRule])
                    <div class="mb-3 col-12">
                        <button type="submit" class="btn btn-primary">{!! __('general.save') !!}</button>
                        <a href="{{ route('admin.pricing-rules.index') }}" class="btn btn-label-secondary">{!! __('general.cancel') !!}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
