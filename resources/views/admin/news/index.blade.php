@extends('admin.layouts.app')
@section('title', 'Tin tức đa ngôn ngữ')
@section('page_title', 'Tin tức đa ngôn ngữ')
@section('content')
<div class="d-flex flex-wrap gap-2 mb-3">
    @foreach ($locales as $code => $label)<a class="btn {{ $locale === $code ? 'btn-primary' : 'btn-light' }}" href="{{ route('admin.news.index', ['locale' => $code]) }}">{{ $label }}</a>@endforeach
    <a class="btn btn-success ms-auto" href="{{ route('admin.news.create', ['locale' => $locale]) }}">Thêm tin tức</a>
</div>
<div class="card"><div class="card-body table-responsive"><table class="table align-middle">
<thead><tr><th>Tiêu đề</th><th>Trạng thái</th><th>Ngày đăng</th><th>Thao tác</th></tr></thead>
<tbody>@forelse ($articles as $article)<tr>
    <td><a href="{{ route('admin.news.edit', $article) }}">{{ $article->title }}</a></td>
    <td><span class="badge {{ $article->published_at ? 'bg-success' : 'bg-secondary' }}">{{ $article->published_at ? 'Đã đăng' : 'Bản nháp' }}</span></td>
    <td>{{ $article->published_at?->format('d/m/Y H:i') ?: '—' }}</td>
    <td><a class="btn btn-sm btn-light" href="{{ route('admin.news.edit', $article) }}">Sửa / Bản dịch</a>
        <form class="d-inline" method="POST" action="{{ route('admin.news.destroy', $article) }}" onsubmit="return confirm('Xóa bản tin của ngôn ngữ này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Xóa</button></form>
    </td>
</tr>@empty<tr><td colspan="4" class="text-center py-4">Chưa có tin tức ở ngôn ngữ này.</td></tr>@endforelse</tbody>
</table>{{ $articles->links() }}</div></div>
@endsection
