<?php

use App\Http\Middleware\SetLocale;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Models\Menu;
use App\Models\Slider;
use App\Models\HomepageSection;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminController::class, 'login'])->name('login');
        Route::post('login', [AdminController::class, 'authenticate'])->name('authenticate');
    });

    Route::middleware(['auth', 'is_admin'])->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('logout', [AdminController::class, 'logout'])->name('logout');
        Route::resource('users', UserController::class)->except('show');
        Route::resource('menus', MenuController::class)->except('show');
        Route::resource('sliders', SliderController::class)->except('show');
        Route::delete('homepage-sections/{homepageSection}/images/{image}', [HomepageSectionController::class, 'destroyImage'])->name('homepage-sections.images.destroy');
        Route::resource('homepage-sections', HomepageSectionController::class)->except('show');
    });
});

Route::get('/', function (Request $request) {
    $preferredLocale = strtolower(substr($request->getPreferredLanguage(SetLocale::SUPPORTED_LOCALES) ?: 'vi', 0, 2));

    return redirect()->route('home', ['locale' => $preferredLocale]);
});

Route::get('/{locale}', function (string $locale) {
    return view('home', [
        'locale' => $locale,
        'languages' => [
            'vi' => 'Tiếng Việt',
            'en' => 'English',
            'zh' => '中文',
            'ko' => '한국어',
        ],
        'translations' => $locale === 'vi' ? [] : trans('site'),
        'headerMenus' => Menu::query()
            ->where('locale', $locale)
            ->where('location', 'header')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(),
        'heroSliders' => Slider::query()
            ->where('locale', $locale)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(),
        'aboutSection' => HomepageSection::query()
            ->where('locale', $locale)
            ->where('key', 'about')
            ->where('is_active', true)
            ->with(['images', 'children' => fn ($query) => $query->where('is_active', true)->with('images')])
            ->first(),
        'ballroomSection' => HomepageSection::query()
            ->where('locale', $locale)
            ->where('key', 'ballroom')
            ->where('is_active', true)
            ->with(['images', 'children' => fn ($query) => $query->where('is_active', true)->with('images')])
            ->first(),
        'servicesSection' => HomepageSection::query()
            ->where('locale', $locale)
            ->where('key', 'services')
            ->where('is_active', true)
            ->with(['children' => fn ($query) => $query->where('is_active', true)->with('images')])
            ->first(),
        'amenitiesSection' => HomepageSection::query()
            ->where('locale', $locale)
            ->where('key', 'amenities')
            ->where('is_active', true)
            ->with(['children' => fn ($query) => $query->where('is_active', true)->with('images')])
            ->first(),
        'eliteClubSection' => HomepageSection::query()
            ->where('locale', $locale)
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('key', 'elite-club')->orWhere('title', 'ELITE CLUB'))
            ->with('images')
            ->first(),
        'supportSection' => HomepageSection::query()
            ->where('locale', $locale)
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('key', 'support')->orWhere('title', 'HỖ TRỢ TƯ VẤN'))
            ->with('images')
            ->first(),
    ]);
})->whereIn('locale', SetLocale::SUPPORTED_LOCALES)->middleware(SetLocale::class)->name('home');
