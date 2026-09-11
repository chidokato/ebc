@extends('admin.layouts.app')
@section('title', 'Cấu hình popup')
@section('page_title', 'Cấu hình popup')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.popup-settings.update') }}">
    @csrf @method('PUT')
    <div class="card"><div class="card-body">
        <h5>Hiển thị popup</h5>
        <p class="text-muted">Cài đặt chung cho trang chủ ở tất cả ngôn ngữ.</p>
        <input type="hidden" name="is_enabled" value="0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="is_enabled" name="is_enabled" value="1" @checked(old('is_enabled', $settings->is_enabled))>
            <label class="form-check-label" for="is_enabled">Bật popup</label>
        </div>
        <small class="text-muted">Khi tắt, popup và nút ưu đãi nổi sẽ ẩn. Các nút đặt lịch sẽ dẫn xuống phần Hỗ trợ tư vấn.</small>
    </div></div>
    <div class="card"><div class="card-body">
        <h5>Nội dung popup</h5>
        <div class="mb-3"><label class="form-label" for="title">Tiêu đề</label><input class="form-control" id="title" name="title" maxlength="255" required value="{{ old('title', $settings->title) }}"></div>
        <div class="mb-3"><label class="form-label" for="offer_content">Nội dung ưu đãi</label><textarea class="form-control" id="offer_content" name="offer_content" rows="4" maxlength="1000">{{ old('offer_content', $settings->offer_content) }}</textarea><small class="text-muted">Hiển thị cạnh ảnh ưu đãi. Có thể xuống dòng.</small></div>
        <div class="mb-3"><label class="form-label" for="content">Nội dung mô tả</label><textarea class="form-control" id="content" name="content" rows="4" maxlength="5000">{{ old('content', $settings->content) }}</textarea><small class="text-muted">Hiển thị phía trên biểu mẫu đăng ký. Có thể xuống dòng.</small></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="launch_label">Chữ trên nút ưu đãi nổi</label><input class="form-control" id="launch_label" name="launch_label" maxlength="100" required value="{{ old('launch_label', $settings->launch_label) }}"></div>
            <div class="col-md-6"><label class="form-label" for="submit_label">Chữ trên nút gửi đăng ký</label><input class="form-control" id="submit_label" name="submit_label" maxlength="100" required value="{{ old('submit_label', $settings->submit_label) }}"></div>
        </div>
    </div></div>
    <div class="card"><div class="card-body">
        <h5>Hình ảnh</h5>
        <div class="row g-3">
            @foreach(['image' => 'Ảnh chính', 'offer_image' => 'Ảnh ưu đãi (hiện tại là 50%)'] as $field => $label)
            <div class="col-md-6">
                <label class="form-label" for="{{ $field }}_file">{{ $label }}</label>
                <input class="form-control" type="file" id="{{ $field }}_file" name="{{ $field }}_file" accept=".jpg,.jpeg,.png,.webp">
                <small class="text-muted">JPG, PNG, WebP; tối đa 5 MB.</small>
                <div class="mt-2"><img src="{{ $settings->{$field.'_url'} }}" alt="{{ $label }}" class="rounded border" style="max-width:100%;max-height:220px;object-fit:contain"></div>
                @if($settings->{$field.'_path'})
                <label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_{{ $field }}" value="1" @checked(old('remove_'.$field))><span class="form-check-label">Xóa ảnh đã tải lên, dùng ảnh mặc định</span></label>
                @endif
            </div>
            @endforeach
        </div>
    </div></div>
    <button class="btn btn-primary mb-4" type="submit">Lưu cấu hình popup</button>
</form>
@endsection
