@extends('admin.layouts.app')
@section('title', 'Cấu hình website')
@section('page_title', 'Cấu hình website')
@section('content')
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}">
@csrf @method('PUT')
<div class="card"><div class="card-body">
    <h5>Thông tin website</h5>
    <p class="text-muted">Cài đặt chung cho tất cả ngôn ngữ, áp dụng trên trang chủ và trang tin tức.</p>
    <div class="mb-3"><label class="form-label" for="title">Tiêu đề website</label><input class="form-control" id="title" name="title" maxlength="255" required value="{{ old('title', $settings->title) }}"></div>
    <div class="mb-3"><label class="form-label" for="booking_email">Email nhận đăng ký</label><input class="form-control" type="email" id="booking_email" name="booking_email" maxlength="255" value="{{ old('booking_email', $settings->booking_email) }}" aria-describedby="booking_email_help"><small class="text-muted" id="booking_email_help">Địa chỉ email nhận yêu cầu đăng ký đặt lịch từ website. Để trống để dùng email trong cấu hình máy chủ.</small></div>
    <div class="row g-3">
        @foreach(['logo' => 'Logo', 'white_logo' => 'Logo trắng', 'favicon' => 'Favicon'] as $field => $label)
        <div class="col-md-4">
            <label class="form-label" for="{{ $field }}_file">{{ $label }}</label>
            <input class="form-control" type="file" id="{{ $field }}_file" name="{{ $field }}_file" accept="{{ $field !== 'favicon' ? '.jpg,.jpeg,.png,.webp,.svg' : '.ico,.png,.svg' }}">
            <small class="text-muted">{{ $field !== 'favicon' ? 'JPG, PNG, WebP, SVG; tối đa 5 MB.' : 'ICO, PNG, SVG; tối đa 1 MB.' }} @if($field === 'white_logo')Dùng trên nền tối ở đầu và chân trang.@endif</small>
            @if($settings->{$field.'_path'})
            <div class="mt-2"><img src="{{ asset($settings->{$field.'_path'}) }}" alt="{{ $label }} hiện tại" class="rounded border bg-secondary p-2" style="max-width:220px;max-height:90px"></div>
            <label class="form-check mt-2"><input type="checkbox" class="form-check-input" name="remove_{{ $field }}" value="1" @checked(old('remove_'.$field))><span class="form-check-label">Xóa {{ $label }} đã tải lên, dùng mặc định</span></label>
            @endif
        </div>
        @endforeach
    </div>
</div></div>
<div class="card"><div class="card-body">
    <h5>SEO</h5>
    <div class="mb-3"><label class="form-label" for="seo_title">Tiêu đề SEO trang chủ</label><input class="form-control" id="seo_title" name="seo_title" maxlength="255" value="{{ old('seo_title', $settings->seo_title) }}"><small class="text-muted">Để trống để dùng tiêu đề website. Bài tin tức vẫn dùng tiêu đề bài viết.</small></div>
    <div class="mb-3"><label class="form-label" for="seo_description">Mô tả SEO</label><textarea class="form-control" id="seo_description" name="seo_description" rows="3" maxlength="2000">{{ old('seo_description', $settings->seo_description) }}</textarea></div>
    <div><label class="form-label" for="seo_keywords">Từ khóa SEO</label><input class="form-control" id="seo_keywords" name="seo_keywords" maxlength="1000" value="{{ old('seo_keywords', $settings->seo_keywords) }}" placeholder="Các từ khóa cách nhau bằng dấu phẩy"></div>
</div></div>
<div class="card"><div class="card-body">
    <h5>Mã chèn vào website</h5>
    <p class="text-muted">Dán đầy đủ thẻ HTML hoặc script, ví dụ mã đo lường, xác minh website, widget hỗ trợ. Mã được chạy trên website công khai sau khi lưu.</p>
    <div class="mb-3"><label class="form-label" for="head_code">Code heading — trước &lt;/head&gt;</label><textarea class="form-control font-monospace" id="head_code" name="head_code" rows="7" maxlength="50000" spellcheck="false">{{ old('head_code', $settings->head_code) }}</textarea></div>
    <div><label class="form-label" for="footer_code">Code footer — trước &lt;/body&gt;</label><textarea class="form-control font-monospace" id="footer_code" name="footer_code" rows="7" maxlength="50000" spellcheck="false">{{ old('footer_code', $settings->footer_code) }}</textarea></div>
</div></div>
<button class="btn btn-primary mb-4" type="submit">Lưu cấu hình</button>
</form>
@endsection
