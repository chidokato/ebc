<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Slider;
use App\Support\ImageResizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'vi');
        abort_unless(in_array($locale, Menu::LOCALES, true), 404);

        return view('admin.sliders.index', ['sliders' => Slider::where('locale', $locale)->orderBy('sort_order')->paginate(20)->withQueryString(), 'locale' => $locale, 'locales' => $this->locales()]);
    }

    public function create(Request $request)
    {
        return view('admin.sliders.form', ['slider' => new Slider(['locale' => $request->query('locale', 'vi'), 'is_active' => true]), 'locales' => $this->locales()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image_path'] = $request->hasFile('image') ? $this->storeImage($request) : 'frontend/images/capital.jpg';
        if ($request->hasFile('mobile_image')) {
            $data['mobile_image_path'] = $this->storeImage($request, 'mobile_image');
        }
        Slider::create($data);

        return redirect()->route('admin.sliders.index', ['locale' => $data['locale']])->with('success', 'Đã thêm slider.');
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.form', ['slider' => $slider, 'locales' => $this->locales()]);
    }

    public function update(Request $request, Slider $slider)
    {
        $data = $this->validated($request);
        $oldPaths = [];
        foreach (['image' => 'image_path', 'mobile_image' => 'mobile_image_path'] as $input => $field) {
            if ($request->hasFile($input)) {
                $data[$field] = $this->storeImage($request, $input);
                $oldPaths[] = $slider->$field;
            } elseif ($input === 'mobile_image' && $request->boolean('remove_mobile_image')) {
                $data[$field] = null;
                $oldPaths[] = $slider->$field;
            }
        }
        $slider->update($data);
        foreach ($oldPaths as $path) {
            $this->deleteUploadedImage($path);
        }

        return redirect()->route('admin.sliders.index', ['locale' => $slider->locale])->with('success', 'Đã cập nhật slider.');
    }

    public function destroy(Slider $slider)
    {
        $locale = $slider->locale;
        $this->deleteUploadedImage($slider->image_path);
        $this->deleteUploadedImage($slider->mobile_image_path);
        $slider->delete();

        return redirect()->route('admin.sliders.index', ['locale' => $locale])->with('success', 'Đã xóa slider.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'locale' => ['required', Rule::in(Menu::LOCALES)],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'button_label' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'remove_mobile_image' => ['nullable', 'boolean'],
        ]);

        $data['title'] = $data['title'] ?? '';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        unset($data['image'], $data['mobile_image'], $data['remove_mobile_image']);

        return $data;
    }

    private function storeImage(Request $request, string $input = 'image'): string
    {
        return ImageResizer::store($request->file($input), 'uploads/sliders');
    }

    private function deleteUploadedImage(?string $path): void
    {
        if ($path && Str::startsWith($path, 'uploads/sliders/')) {
            File::delete(base_path($path));
        }
    }

    private function locales(): array
    {
        return ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文', 'ko' => '한국어'];
    }
}
