@extends('admin.layouts.app')

@php($editing = $slider->exists)
@section('title', $editing ? 'Sửa slider' : 'Thêm slider')
@section('page_title', $editing ? 'Sửa slider' : 'Thêm slider')

@section('content')
<div class="card"><div class="card-body"><form method="POST" novalidate enctype="multipart/form-data" action="{{ $editing ? route('admin.sliders.update', $slider) : route('admin.sliders.store') }}">@csrf @if($editing) @method('PUT') @endif
    <div class="row g-3"><div class="col-md-6"><label class="form-label" for="locale">Ngôn ngữ</label><select id="locale" class="form-select" name="locale">@foreach($locales as $code => $name)<option value="{{ $code }}" @selected(old('locale', $slider->locale) === $code)>{{ $name }}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label" for="sort_order">Thứ tự hiển thị</label><input id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" name="sort_order" type="number" min="0" value="{{ old('sort_order', $slider->sort_order) }}">@error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-8"><label class="form-label" for="title">Tiêu đề</label><input id="title" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $slider->title) }}">@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-4"><label class="form-label" for="image">Ảnh slider {{ $editing ? '(để trống nếu giữ ảnh cũ)' : '' }}</label><input id="image" class="form-control @error('image') is-invalid @enderror" name="image" type="file" accept="image/jpeg,image/png,image/webp"><small class="text-muted">Tối đa 20 MB, tự động giảm cạnh dài nhất về 1900px.</small>@error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    @if($editing)<div class="col-12"><img src="{{ asset($slider->image_path) }}" alt="Slider hiện tại" class="rounded border" style="max-height:140px;max-width:280px;object-fit:cover"></div>@endif
    <div class="col-12"><label class="form-label" for="description">Mô tả</label><textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $slider->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label" for="button_label">Nhãn nút CTA</label><input id="button_label" class="form-control" name="button_label" value="{{ old('button_label', $slider->button_label) }}"></div><div class="col-md-6"><label class="form-label" for="button_url">Liên kết nút CTA</label><input id="button_url" class="form-control" name="button_url" value="{{ old('button_url', $slider->button_url) }}" placeholder="#consultation"></div>
    <div class="col-12"><div class="form-check"><input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $slider->is_active))><label class="form-check-label" for="is_active">Hiển thị slider này</label></div></div></div>
    <div class="mt-4"><a href="{{ route('admin.sliders.index', ['locale' => $slider->locale]) }}" class="btn btn-light">Hủy</a><button class="btn btn-primary" type="submit">{{ $editing ? 'Lưu thay đổi' : 'Thêm slider' }}</button></div>
</form></div></div>
@endsection
