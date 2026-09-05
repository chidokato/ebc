@extends('admin.layouts.app')

@section('title', 'Người dùng')
@section('page_title', 'Quản lý người dùng')

@section('content')
<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="card-title mb-0">Danh sách người dùng</h5><a href="{{ route('admin.users.create') }}" class="btn btn-primary">Thêm người dùng</a></div><div class="card-body"><div class="table-responsive"><table class="table align-middle table-hover"><thead class="table-light"><tr><th>Tên</th><th>Email</th><th>Vai trò</th><th>Ngày tạo</th><th class="text-end">Thao tác</th></tr></thead><tbody>@forelse($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td><span class="badge {{ $user->is_admin ? 'bg-success' : 'bg-secondary' }}">{{ $user->is_admin ? 'Quản trị viên' : 'Người dùng' }}</span></td><td>{{ $user->created_at->format('d/m/Y H:i') }}</td><td class="text-end"><a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-soft-warning">Sửa</a>@if(! $user->is(auth()->user()))<form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger" type="submit" onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')">Xóa</button></form>@endif</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">Chưa có người dùng.</td></tr>@endforelse</tbody></table></div>{{ $users->links('pagination::bootstrap-4') }}</div></div>
@endsection
