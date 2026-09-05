@extends('admin.layouts.app')

@section('title', 'Tổng quan')
@section('page_title', 'Tổng quan')

@section('content')
<div class="row">
    <div class="col-md-6"><div class="card card-animate"><div class="card-body"><p class="text-muted mb-2">Tổng người dùng</p><h2 class="mb-0">{{ $userCount }}</h2></div></div></div>
    <div class="col-md-6"><div class="card card-animate"><div class="card-body"><p class="text-muted mb-2">Quản trị viên</p><h2 class="mb-0">{{ $adminCount }}</h2></div></div></div>
</div>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Người dùng mới</h5></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Tên</th><th>Email</th><th>Vai trò</th><th>Ngày tạo</th></tr></thead><tbody>@forelse($recentUsers as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td><span class="badge {{ $user->is_admin ? 'bg-success' : 'bg-secondary' }}">{{ $user->is_admin ? 'Quản trị viên' : 'Người dùng' }}</span></td><td>{{ $user->created_at->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Chưa có người dùng.</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
