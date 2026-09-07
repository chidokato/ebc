<!doctype html>
<html lang="vi" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') | Elite Business Center</title>
    <link rel="shortcut icon" href="{{ asset('admin-assets/images/favicon.ico') }}">
    <script src="{{ asset('admin-assets/js/layout.js') }}"></script>
    <link href="{{ asset('admin-assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin-assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin-assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin-assets/css/custom.min.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<div id="layout-wrapper">
    <header id="page-topbar"><div class="layout-width"><div class="navbar-header">
        <a href="{{ route('admin.dashboard') }}" class="fw-bold fs-18 text-dark text-decoration-none">ELITE BUSINESS CENTER</a>
        <div class="dropdown ms-auto"><button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" type="button">{{ auth()->user()->name }}</button><div class="dropdown-menu dropdown-menu-end"><a class="dropdown-item" href="{{ route('admin.users.index') }}">Quản lý người dùng</a><form action="{{ route('admin.logout') }}" method="POST">@csrf<button class="dropdown-item text-danger" type="submit">Đăng xuất</button></form></div></div>
    </div></div></header>
    <div class="app-menu navbar-menu"><div class="navbar-brand-box"><a href="{{ route('admin.dashboard') }}" class="logo logo-light"><span class="logo-lg text-white fw-bold">EBC ADMIN</span></a></div><div id="scrollbar"><div class="container-fluid"><ul class="navbar-nav" id="navbar-nav">
        <li class="menu-title"><span>QUẢN TRỊ</span></li>
        <li class="nav-item"><a class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="ri-dashboard-2-line"></i><span>Tổng quan</span></a></li>
        <li class="nav-item"><a class="nav-link menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="ri-group-line"></i><span>Người dùng</span></a></li>
        <li class="nav-item"><a class="nav-link menu-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}" href="{{ route('admin.menus.index') }}"><i class="ri-menu-line"></i><span>Menu đa ngôn ngữ</span></a></li>
        <li class="nav-item"><a class="nav-link menu-link {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}" href="{{ route('admin.sliders.index') }}"><i class="ri-image-line"></i><span>Slider đa ngôn ngữ</span></a></li>
        <li class="nav-item"><a class="nav-link menu-link {{ request()->routeIs('admin.homepage-sections.*') ? 'active' : '' }}" href="{{ route('admin.homepage-sections.index') }}"><i class="ri-layout-masonry-line"></i><span>Trang chủ</span></a></li>
        <li class="nav-item"><a class="nav-link menu-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}" href="{{ route('admin.news.index') }}"><i class="ri-newspaper-line"></i><span>Tin tức đa ngôn ngữ</span></a></li>
        <li class="nav-item"><a class="nav-link menu-link" href="{{ route('home', ['locale' => 'vi']) }}" target="_blank"><i class="ri-external-link-line"></i><span>Xem website</span></a></li>
    </ul></div></div></div><div class="vertical-overlay"></div>
    <div class="main-content"><div class="page-content"><div class="container-fluid">
        <div class="row"><div class="col-12"><div class="page-title-box d-sm-flex align-items-center justify-content-between"><h4 class="mb-sm-0">@yield('page_title', 'Tổng quan')</h4></div></div></div>
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @yield('content')
    </div></div></div>
</div>
<script src="{{ asset('admin-assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin-assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('admin-assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('admin-assets/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('admin-assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
