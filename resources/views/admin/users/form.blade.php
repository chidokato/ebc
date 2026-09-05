@extends('admin.layouts.app')

@php($editing = $user->exists)
@section('title', $editing ? 'Sửa người dùng' : 'Thêm người dùng')
@section('page_title', $editing ? 'Sửa người dùng' : 'Thêm người dùng')

@section('content')
<div class="card"><div class="card-body"><form novalidate action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">@csrf @if($editing) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="name">Họ tên</label><input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $user->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label class="form-label" for="password">{{ $editing ? 'Mật khẩu mới (để trống nếu không đổi)' : 'Mật khẩu' }}</label><input id="password" class="form-control @error('password') is-invalid @enderror" name="password" type="password" {{ $editing ? '' : 'required' }}>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label class="form-label" for="password_confirmation">Xác nhận mật khẩu</label><input id="password_confirmation" class="form-control" name="password_confirmation" type="password"></div>
        <div class="col-12"><div class="form-check"><input class="form-check-input" id="is_admin" name="is_admin" type="checkbox" value="1" @checked(old('is_admin', $user->is_admin))><label class="form-check-label" for="is_admin">Cấp quyền quản trị viên</label></div>@error('is_admin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
    </div>
    <div class="mt-4"><a href="{{ route('admin.users.index') }}" class="btn btn-light">Hủy</a><button class="btn btn-primary" type="submit">{{ $editing ? 'Lưu thay đổi' : 'Thêm người dùng' }}</button></div>
</form></div></div>
@endsection
