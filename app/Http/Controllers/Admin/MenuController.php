<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'vi');

        abort_unless(in_array($locale, Menu::LOCALES, true), 404);

        return view('admin.menus.index', [
            'menus' => Menu::where('locale', $locale)->orderBy('location')->orderBy('sort_order')->paginate(20)->withQueryString(),
            'locale' => $locale,
            'locales' => $this->locales(),
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.menus.form', [
            'menu' => new Menu(['locale' => $request->query('locale', 'vi'), 'location' => 'header', 'is_active' => true]),
            'locales' => $this->locales(),
        ]);
    }

    public function store(Request $request)
    {
        Menu::create($this->validated($request));

        return redirect()->route('admin.menus.index', ['locale' => $request->input('locale')])->with('success', 'Đã thêm mục menu.');
    }

    public function edit(Menu $menu)
    {
        return view('admin.menus.form', ['menu' => $menu, 'locales' => $this->locales()]);
    }

    public function update(Request $request, Menu $menu)
    {
        $menu->update($this->validated($request));

        return redirect()->route('admin.menus.index', ['locale' => $menu->locale])->with('success', 'Đã cập nhật mục menu.');
    }

    public function destroy(Menu $menu)
    {
        $locale = $menu->locale;
        $menu->delete();

        return redirect()->route('admin.menus.index', ['locale' => $locale])->with('success', 'Đã xóa mục menu.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'locale' => ['required', Rule::in(Menu::LOCALES)],
            'label' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:2048'],
            'location' => ['required', Rule::in(['header', 'footer'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function locales(): array
    {
        return ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文', 'ko' => '한국어'];
    }
}
