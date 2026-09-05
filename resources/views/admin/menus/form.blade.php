@extends('admin.layouts.app')

@php($editing = $menu->exists)
@section('title', $editing ? 'Sửa mục menu' : 'Thêm mục menu')
@section('page_title', $editing ? 'Sửa mục menu' : 'Thêm mục menu')

@section('content')
<div class="card"><div class="card-body"><form method="POST" novalidate action="{{ $editing ? route('admin.menus.update', $menu) : route('admin.menus.store') }}">@csrf @if($editing) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="locale">Ngôn ngữ</label><select id="locale" class="form-select @error('locale') is-invalid @enderror" name="locale" required>@foreach($locales as $code => $name)<option value="{{ $code }}" @selected(old('locale', $menu->locale) === $code)>{{ $name }}</option>@endforeach</select>@error('locale')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label class="form-label" for="location">Vị trí</label><select id="location" class="form-select" name="location"><option value="header" @selected(old('location', $menu->location) === 'header')>Thanh đầu trang</option><option value="footer" @selected(old('location', $menu->location) === 'footer')>Chân trang</option></select></div>
        <div class="col-md-8"><label class="form-label" for="label">Nhãn menu</label><input id="label" class="form-control @error('label') is-invalid @enderror" name="label" value="{{ old('label', $menu->label) }}" required maxlength="100">@error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-4"><label class="form-label" for="sort_order">Thứ tự hiển thị</label><input id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" name="sort_order" type="number" min="0" value="{{ old('sort_order', $menu->sort_order) }}" required>@error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><label class="form-label" for="url">Liên kết</label><input id="url" class="form-control @error('url') is-invalid @enderror" name="url" value="{{ old('url', $menu->url) }}" placeholder="#about hoặc https://example.com" required maxlength="2048">@error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-12"><div class="form-check"><input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $menu->is_active))><label class="form-check-label" for="is_active">Hiển thị mục menu này</label></div></div>
    </div>
    <div class="mt-4"><a href="{{ route('admin.menus.index', ['locale' => $menu->locale]) }}" class="btn btn-light">Hủy</a><button class="btn btn-primary" type="submit">{{ $editing ? 'Lưu thay đổi' : 'Thêm mục menu' }}</button></div>
</form></div></div>
@endsection
