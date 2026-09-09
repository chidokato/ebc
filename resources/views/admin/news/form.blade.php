@extends('admin.layouts.app')
@section('title', 'Biên tập tin tức')
@section('page_title', 'Biên tập tin tức — ' . $locales[$article->locale])
@section('content')
@if ($article->translation_group)
<div class="d-flex flex-wrap gap-2 mb-3">@foreach ($locales as $code => $label)
    <a class="btn {{ $code === $article->locale ? 'btn-primary' : 'btn-light' }}" href="{{ isset($translations[$code]) ? route('admin.news.edit', $translations[$code]) : route('admin.news.create', ['locale' => $code, 'group' => $article->translation_group]) }}">{{ $label }}{{ isset($translations[$code]) ? '' : ' + Thêm bản dịch' }}</a>
@endforeach</div>
@endif
<form method="POST" enctype="multipart/form-data" action="{{ $article->exists ? route('admin.news.update', $article) : route('admin.news.store') }}">
@csrf @if ($article->exists) @method('PUT') @endif
<input type="hidden" name="locale" value="{{ $article->locale }}">
<input type="hidden" name="translation_group" value="{{ $article->translation_group }}">
<div class="card"><div class="card-body">
    <div class="mb-3"><label for="title" class="form-label">Tiêu đề</label><input id="title" class="form-control" name="title" maxlength="255" required value="{{ old('title', $article->title) }}"></div>
    <div class="mb-3"><label for="excerpt" class="form-label">Tóm tắt</label><textarea id="excerpt" class="form-control" name="excerpt" rows="3" maxlength="2000">{{ old('excerpt', $article->excerpt) }}</textarea></div>
    <div class="mb-3 news-editor"><label for="content" class="form-label">Nội dung</label><textarea id="content" class="form-control" name="content" rows="15" maxlength="20000" required>{{ old('content', $article->content) }}</textarea><input type="hidden" id="content-is-html" name="content_is_html" value="{{ old('content_is_html', (int) $article->content_is_html) }}"><small class="text-muted">Định dạng nội dung bằng thanh công cụ. Tối đa 20.000 ký tự, bao gồm mã định dạng.</small><div id="content-error" class="text-danger mt-1" role="alert"></div></div>
    <div class="mb-3">
        <label for="image_file" class="form-label">Ảnh đại diện (JPG, PNG, WebP; tối đa 10 MB)</label>
        <div class="d-flex flex-column flex-md-row gap-2">
            <input id="image_file" class="form-control" type="file" name="image_file" accept="image/jpeg,image/png,image/webp" aria-describedby="image-all-languages-help">
            <button class="btn btn-outline-primary flex-shrink-0" type="submit" name="apply_image_all_languages" value="1" aria-describedby="image-all-languages-help">Áp dụng cho tất cả ngôn ngữ</button>
        </div>
        <small id="image-all-languages-help" class="text-muted d-block mt-1">Lưu bài viết và dùng ảnh mới chọn (hoặc ảnh hiện tại) cho tất cả bản dịch đã có của bài này. Ảnh đại diện của các bản dịch sẽ được thay thế.</small>
        @if ($article->image_path)<img class="mt-2 rounded" src="{{ asset($article->image_path) }}" alt="Ảnh đại diện" style="max-width:240px;max-height:160px">@endif
    </div>
    <div class="mb-3"><label for="news-status" class="form-label">Trạng thái</label></div>
    <button class="btn btn-primary" type="submit">Lưu bài viết</button>
    <select id="news-status" class="btn btn-success" name="status"><option value="draft" @selected(old('status', $article->published_at ? 'published' : 'draft') === 'draft')>Bản nháp</option><option value="published" @selected(old('status', $article->published_at ? 'published' : 'draft') === 'published')>Đăng lên website</option></select>
    <a class="btn btn-light" href="{{ route('admin.news.index', ['locale' => $article->locale]) }}">Quay lại</a>
    @if ($article->published_at)<a class="btn btn-outline-primary" href="{{ route('news.show', ['locale' => $article->locale, 'news' => $article->id]) }}" target="_blank">Xem bài đã đăng</a>@endif
</div></div></form>
@endsection

@push('styles')
<style>.news-editor .ck-editor__editable_inline{min-height:320px;max-height:650px}.news-editor .ck-editor{width:100%}.news-editor .ck-content{font-size:15px;line-height:1.7}</style>
@endpush
@push('scripts')
<script src="{{ asset('admin-assets/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>
<script>
(() => {
    const field = document.getElementById('content');
    const format = document.getElementById('content-is-html');
    const error = document.getElementById('content-error');
    const initial = format.value === '1' ? field.value : '<p>' + field.value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\r?\n/g, '<br>') + '</p>';
    if (typeof ClassicEditor === 'undefined') return;
    ClassicEditor.create(field, {
        initialData: initial,
        toolbar: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo'],
        heading: { options: [
            { model: 'paragraph', title: 'Đoạn văn', class: 'ck-heading_paragraph' },
            { model: 'heading1', view: 'h2', title: 'Tiêu đề 2', class: 'ck-heading_heading1' },
            { model: 'heading2', view: 'h3', title: 'Tiêu đề 3', class: 'ck-heading_heading2' }
        ] }
    }).then(editor => {
        field.required = false;
        format.value = '1';
        editor.ui.view.editable.element.setAttribute('aria-label', 'Nội dung bài viết');
        field.form.addEventListener('submit', event => {
            field.value = editor.getData();
            const parsed = new DOMParser().parseFromString(field.value, 'text/html');
            const empty = !parsed.body.textContent.replace(/[\s\u00a0\u200b]/g, '');
            if (empty || Array.from(field.value).length > 20000) {
                event.preventDefault();
                error.textContent = empty ? 'Vui lòng nhập nội dung bài viết.' : 'Nội dung vượt quá 20.000 ký tự, bao gồm mã định dạng.';
                editor.editing.view.focus();
            } else {
                error.textContent = '';
            }
        });
    }).catch(() => { error.textContent = 'Không tải được trình soạn thảo. Bạn vẫn có thể nhập nội dung trong ô văn bản.'; });
})();
</script>
@endpush
