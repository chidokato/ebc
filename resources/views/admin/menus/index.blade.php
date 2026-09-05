@extends('admin.layouts.app')

@section('title', 'Menu đa ngôn ngữ')
@section('page_title', 'Quản lý menu đa ngôn ngữ')

@section('content')
<div class="card"><div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center"><div><h5 class="card-title mb-1">Danh sách menu</h5><p class="text-muted mb-0">Mỗi ngôn ngữ có bộ menu và thứ tự hiển thị riêng.</p></div><a href="{{ route('admin.menus.create', ['locale' => $locale]) }}" class="btn btn-primary">Thêm mục menu</a></div><div class="card-body">
    <ul class="nav nav-pills mb-4">@foreach($locales as $code => $name)<li class="nav-item"><a class="nav-link {{ $locale === $code ? 'active' : '' }}" href="{{ route('admin.menus.index', ['locale' => $code]) }}">{{ $name }}</a></li>@endforeach</ul>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead class="table-light"><tr><th>Thứ tự</th><th>Nhãn</th><th>Liên kết</th><th>Vị trí</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>@forelse($menus as $menu)<tr><td>{{ $menu->sort_order }}</td><td class="fw-medium">{{ $menu->label }}</td><td><code>{{ $menu->url }}</code></td><td>{{ $menu->location === 'header' ? 'Thanh đầu trang' : 'Chân trang' }}</td><td><span class="badge {{ $menu->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $menu->is_active ? 'Hiển thị' : 'Đang ẩn' }}</span></td><td class="text-end"><a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-soft-warning">Sửa</a><form class="d-inline" method="POST" action="{{ route('admin.menus.destroy', $menu) }}">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-soft-danger" onclick="return confirm('Bạn có chắc muốn xóa mục menu này?')">Xóa</button></form></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">Chưa có menu cho ngôn ngữ này.</td></tr>@endforelse</tbody></table></div>
    {{ $menus->links('pagination::bootstrap-4') }}
</div></div>
@endsection
