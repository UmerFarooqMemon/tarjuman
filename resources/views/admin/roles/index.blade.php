@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">{!! __('general.roles_and_permissions') !!}</h5>
                    <p class="mb-0 text-muted">{!! __('general.roles_and_permissions_para') !!}</p>
                </div>
                @can('roles.create')
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    {!! __('general.add_new_role') !!}
                </a>
                @endcan
            </div>
        </div>
    </div>

    @forelse ($roles as $role)
    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">{{ $role->name }}</h5>
                        <span class="badge bg-label-primary">{{ $role->users_count }} {!! __('general.total_users') !!}</span>
                    </div>
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            @can('roles.edit')
                            <a class="dropdown-item" href="{{ route('admin.roles.edit', $role->id) }}">
                                <i class="ti ti-edit me-1"></i> {!! __('general.manage_permissions') !!}
                            </a>
                            @endcan
                            @can('roles.delete')
                            @if ($role->name !== config('admin_permissions.default_role'))
                            <a class="dropdown-item text-danger" href="javascript:;" onclick="deleteConfirmation({{ $role->id }})">
                                <i class="ti ti-trash me-1"></i> {!! __('general.delete') !!}
                            </a>
                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" id="deleteForm{{ $role->id }}">
                                @csrf
                                @method('DELETE')
                            </form>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>

                <h6 class="text-muted text-uppercase mb-2 small">{!! __('general.permissions') !!}</h6>
                <div class="d-flex flex-wrap gap-1">
                    @forelse ($role->permissions->take(8) as $permission)
                        <span class="badge bg-label-secondary text-wrap text-start" title="{{ $permission->label }}">
                            {{ $permission->label }}
                        </span>
                    @empty
                        <span class="text-muted small">{!! __('general.no_permissions_assigned') !!}</span>
                    @endforelse
                    @if ($role->permissions->count() > 8)
                        <span class="badge bg-label-primary">+{{ $role->permissions->count() - 8 }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-secondary mb-0">{!! __('general.no_roles_found') !!}</div>
    </div>
    @endforelse
</div>
@endsection
