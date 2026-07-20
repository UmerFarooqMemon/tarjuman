@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Users</h5>
    </div>
    <div class="card-datatable text-nowrap">
        <table class="dt-scrollableTable table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Created At</th>
                    <th class="status-toggle-col">Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td><span class="d-none">{{ strtotime($user->created_at) }}</span>{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            @include('admin.partials.status-toggle-inline', [
                                'id' => $user->id,
                                'column' => 'is_active',
                                'checked' => $user->is_active,
                                'permission' => 'users.toggle_status',
                            ])
                        </td>
                        <td>
                            <div class="d-inline-block">
                                @can('users.delete')
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" id="deleteForm{{ $user->id }}">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endcan
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-icon btn-label-info"><i class="ti ti-eye"></i></a>
                                @can('users.delete')
                                <button type="button" class="btn btn-sm btn-icon btn-label-danger" onclick="deleteConfirmation({{ $user->id }})"><i class="ti ti-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
