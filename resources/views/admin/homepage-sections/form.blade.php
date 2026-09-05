@extends('admin.layouts.app')

@php($editing = $section->exists)
@section('title', $editing ? 'Sửa section' : 'Thêm section')
@section('page_title', $editing ? 'Sửa section trang chủ' : 'Thêm section trang chủ')

@section('content')
<div class="card"><div class="card-body"><form method="POST" novalidate enctype="multipart/form-data" action="{{ $editing ? route('admin.homepage-sections.update', $section) : route('admin.homepage-sections.store') }}">@csrf @if($editing) @method('PUT') @endif
@if(! $editing)<div class="alert alert-info">Khi thêm section, hệ thống sẽ tự tạo section tương ứng cho tiếng Việt, English, 中文 và 한국어. Nội dung ban đầu sẽ được sao chép từ form này.</div>@endif
<div class="row g-3"><div class="col-md-6"><label class="form-label" for="locale">Ngôn ngữ</label><select class="form-select" id="locale" name="locale">@foreach($locales as $code => $name)<option value="{{ $code }}" @selected(old('locale', $section->locale) === $code)>{{ $name }}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label" for="parent_id">Section cha</label><select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id"><option value="">Không có (section chính)</option>@foreach($parents as $parent)<option value="{{ $parent->id }}" @selected((string)old('parent_id', $section->parent_id) === (string)$parent->id)>{{ $parent->title }}</option>@endforeach</select>@error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-4"><label class="form-label" for="title">Tên section</label><input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $section->title) }}" required>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-4"><label class="form-label" for="sub_title">Sub section</label><input class="form-control @error('sub_title') is-invalid @enderror" id="sub_title" name="sub_title" value="{{ old('sub_title', $section->sub_title) }}" placeholder="Nhập tiêu đề phụ">@error('sub_title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-4"><label class="form-label" for="key">Mã định danh</label><input class="form-control @error('key') is-invalid @enderror" id="key" name="key" value="{{ old('key', $section->key) }}" placeholder="vd: about-us">@error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-4"><label class="form-label" for="sort_order">Thứ tự hiển thị</label><input class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $section->sort_order) }}" required>@error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-4"><label class="form-label" for="link_url">Liên kết (nếu có)</label><input class="form-control @error('link_url') is-invalid @enderror" id="link_url" name="link_url" value="{{ old('link_url', $section->link_url) }}" placeholder="#about hoặc https://example.com">@error('link_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-4"><label class="form-label" for="link_label">Tên nút (nếu có)</label><input class="form-control @error('link_label') is-invalid @enderror" id="link_label" name="link_label" value="{{ old('link_label', $section->link_label) }}" placeholder="vd: Đăng ký ngay">@error('link_label')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-12"><label class="form-label" for="content">Nội dung / mô tả</label><textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="5">{{ old('content', $section->content) }}</textarea><small class="text-muted">Có thể định dạng chữ, tạo danh sách và chèn liên kết.</small>@error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-6"><label class="form-label" for="image_files">Ảnh section</label><input class="form-control @error('image_files') is-invalid @enderror @error('image_files.*') is-invalid @enderror" id="image_files" name="image_files[]" type="file" accept="image/jpeg,image/png,image/webp" multiple><small class="text-muted">Chọn tối đa 20 ảnh, mỗi ảnh tối đa 20 MB và tự động giảm cạnh dài nhất về 1900px.</small>@error('image_files')<div class="invalid-feedback">{{ $message }}</div>@enderror @error('image_files.*')<div class="invalid-feedback">{{ $message }}</div>@enderror @if($section->images->isNotEmpty())<div class="d-flex flex-wrap gap-2 mt-2">@foreach($section->images as $image)<div class="position-relative"><img src="{{ asset($image->path) }}" alt="Ảnh section" class="rounded border" style="height:80px;width:120px;object-fit:cover"><button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 py-0 px-1" type="submit" form="delete-section-image-{{ $image->id }}" title="Xóa ảnh" onclick="return confirm('Bạn có chắc muốn xóa ảnh này?')">&times;</button></div>@endforeach</div>@elseif($section->image_path)<div class="mt-2"><img src="{{ asset($section->image_path) }}" alt="Ảnh section" class="rounded border" style="height:80px;width:120px;object-fit:cover"></div>@endif</div>
<div class="col-md-6"><label class="form-label" for="icon_file">Icon section</label><input class="form-control @error('icon_file') is-invalid @enderror" id="icon_file" name="icon_file" type="file" accept="image/png,image/webp,image/svg+xml"><small class="text-muted">Tối đa 20 MB; PNG/WebP tự động giảm về 1900px, SVG giữ nguyên.</small>@error('icon_file')<div class="invalid-feedback">{{ $message }}</div>@enderror @if($section->icon_path)<div class="mt-2"><img src="{{ asset($section->icon_path) }}" alt="Icon section" class="rounded border p-1" style="height:80px;width:80px;object-fit:contain"></div>@endif</div>
<div class="col-12"><div class="form-check"><input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $section->is_active))><label class="form-check-label" for="is_active">Hiển thị section này</label></div></div></div>
<div class="mt-4"><a href="{{ route('admin.homepage-sections.index', ['locale' => $section->locale]) }}" class="btn btn-light">Hủy</a><button class="btn btn-primary" type="submit">{{ $editing ? 'Lưu thay đổi' : 'Thêm section' }}</button></div>
</form>
@if($editing)
    @foreach($section->images as $image)
        <form id="delete-section-image-{{ $image->id }}" method="POST" action="{{ route('admin.homepage-sections.images.destroy', [$section, $image]) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
</div></div>
@endsection

@push('styles')
<style>
    #content + .ck-editor .ck-content i { font-family: 'High Spirited', Georgia, serif; font-size: 1.35em; font-style: normal; }
    #content + .ck-editor .ck-content strong { font-family: 'Wasted Vindey', Georgia, serif; font-size: 1.2em; font-weight: 400; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    const contentElement = document.querySelector('#content');

    if (contentElement && window.ClassicEditor) {
        ClassicEditor.create(contentElement, {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'undo', 'redo']
        }).then((editor) => {
            contentElement.closest('form').addEventListener('submit', () => editor.updateSourceElement());
        }).catch((error) => console.error('Không thể khởi tạo CKEditor:', error));
    }
</script>
@endpush
