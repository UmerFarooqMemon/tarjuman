@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{!! __('general.cms_pages') !!}</h5>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{!! __('general.name') !!}</th>
                    <th>Slug</th>
                    <th>{!! __('general.cms_preview_path') !!}</th>
                    <th>{!! __('general.cms_sections_count') !!}</th>
                    <th>{!! __('general.status') !!}</th>
                    <th>{!! __('general.actions') !!}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pages as $page)
                    <tr>
                        <td>{{ $page->title }}</td>
                        <td><code>{{ $page->slug }}</code></td>
                        <td><code>{{ $page->preview_path }}</code></td>
                        <td>{{ $page->sections_count }}</td>
                        <td>
                            @if ($page->is_published)
                                <span class="badge bg-label-success">Published</span>
                            @else
                                <span class="badge bg-label-secondary">Draft</span>
                            @endif
                        </td>
                        <td>
                            <a
                                href="{{ route('admin.cms.pages.show', $page) }}"
                                class="btn btn-sm btn-icon btn-dark"
                                title="{{ $page->title }}">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{!! __('general.cms_no_pages') !!}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
