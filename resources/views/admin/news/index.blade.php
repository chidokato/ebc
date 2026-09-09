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
    <td>
        <form method="POST" action="{{ route('admin.news.status', $article) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="page" value="{{ $articles->currentPage() }}">
            <input type="hidden" name="is_published" value="0">
            <div class="form-check form-switch mb-0 text-nowrap">
                <input class="form-check-input" type="checkbox" role="switch" id="news-status-{{ $article->id }}" name="is_published" value="1" @checked($article->published_at) onchange="this.form.requestSubmit()" aria-label="Đăng lên website: {{ $article->title }}">
                <label class="form-check-label" for="news-status-{{ $article->id }}"><span class="badge {{ $article->published_at ? 'bg-success' : 'bg-secondary' }}">{{ $article->published_at ? 'Đã đăng' : 'Bản nháp' }}</span></label>
            </div>
            <noscript><button class="btn btn-sm btn-primary mt-1" type="submit">Lưu trạng thái</button></noscript>
        </form>
    </td>
    <td>{{ $article->published_at?->format('d/m/Y H:i') ?: '—' }}</td>
    <td><a class="btn btn-sm btn-light" href="{{ route('admin.news.edit', $article) }}">Sửa / Bản dịch</a>
        <form class="d-inline" method="POST" action="{{ route('admin.news.destroy', $article) }}" onsubmit="return confirm('Xóa bản tin của ngôn ngữ này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Xóa</button></form>
    </td>
</tr>@empty<tr><td colspan="4" class="text-center py-4">Chưa có tin tức ở ngôn ngữ này.</td></tr>@endforelse</tbody>
</table>{{ $articles->links() }}</div></div>
@endsection
