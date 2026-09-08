<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use App\Models\HomepageSectionImage;
use App\Models\Menu;
use App\Support\ImageResizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HomepageSectionController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'vi');
        abort_unless(in_array($locale, Menu::LOCALES, true), 404);

        return view('admin.homepage-sections.index', [
            'sections' => HomepageSection::where('locale', $locale)->whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
            'locale' => $locale,
            'locales' => $this->locales(),
        ]);
    }

    public function create(Request $request)
    {
        $locale = $request->query('locale', 'vi');
        abort_unless(in_array($locale, Menu::LOCALES, true), 404);
        $parent = $request->filled('parent') ? HomepageSection::where('locale', $locale)->findOrFail($request->integer('parent')) : null;
        $group = $request->query('group');
        $source = $group ? HomepageSection::where('translation_group', $group)->firstOrFail() : null;
        if ($source) {
            $existing = HomepageSection::where('translation_group', $group)->where('locale', $locale)->first();
            if ($existing) {
                return redirect()->route('admin.homepage-sections.edit', $existing);
            }
            if ($source->parent_id) {
                $parent = HomepageSection::where('translation_group', $source->parent->translation_group)->where('locale', $locale)->firstOrFail();
            }
        }

        return view('admin.homepage-sections.form', [
            'section' => new HomepageSection(['locale' => $locale, 'parent_id' => $parent?->id, 'translation_group' => $group, 'key' => $source?->key, 'sort_order' => $source?->sort_order ?? 0, 'is_active' => true]),
            'translations' => $group ? HomepageSection::where('translation_group', $group)->get()->keyBy('locale') : collect(),
            'parents' => HomepageSection::where('locale', $locale)->whereNull('parent_id')->orderBy('sort_order')->get(),
            'locales' => $this->locales(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if (! empty($data['translation_group'])) {
            abort_if(HomepageSection::where('translation_group', $data['translation_group'])->where('locale', $data['locale'])->exists(), 409, 'Bản ngôn ngữ này đã tồn tại.');
            $this->ensureParentMatchesLocale($data['parent_id'] ?? null, $data['locale']);
            $imagePaths = $this->storeImages($request);
            $data = $this->storeAssets($request, $data);
            $data['image_path'] = $imagePaths[0] ?? null;
            $section = DB::transaction(function () use ($data, $imagePaths, $request) {
                $section = HomepageSection::create($data);
                $this->attachImages($section, $imagePaths);
                $this->applyAssetsToTranslations($section, $request);
                return $section;
            });
            return redirect()->route('admin.homepage-sections.edit', $section)->with('success', 'Đã thêm bản ngôn ngữ.');
        }
        $imagePaths = $this->storeImages($request);
        $data = $this->storeAssets($request, $data);
        if ($imagePaths) {
            $data['image_path'] = $imagePaths[0];
        }
        $parent = $this->ensureParentMatchesLocale($data['parent_id'] ?? null, $data['locale']);
        $translationGroup = (string) Str::uuid();

        foreach (Menu::LOCALES as $locale) {
            $localized = $data;
            $shareImages = $locale === $data['locale'] || $request->boolean('apply_images_all', true);
            if (! $shareImages) {
                $localized['image_path'] = null;
            }
            if ($locale !== $data['locale'] && ! $request->boolean('apply_icon_all', true)) {
                $localized['icon_path'] = null;
            }
            if ($locale !== $data['locale'] && ! $request->boolean('apply_quick_items_all', true)) {
                $localized['quick_items'] = [];
            }
            $localized['locale'] = $locale;
            $localized['translation_group'] = $translationGroup;
            $localized['parent_id'] = $parent
                ? HomepageSection::where('translation_group', $parent->translation_group)->where('locale', $locale)->value('id')
                : null;

            $section = HomepageSection::create($localized);
            $this->attachImages($section, $shareImages ? $imagePaths : []);
        }

        $created = HomepageSection::where('translation_group', $translationGroup)->where('locale', $data['locale'])->firstOrFail();
        return redirect()->route('admin.homepage-sections.edit', $created)->with('success', 'Đã thêm section trang chủ.');
    }

    public function edit(HomepageSection $homepageSection)
    {
        return view('admin.homepage-sections.form', [
            'section' => $homepageSection,
            'translations' => $homepageSection->translation_group ? HomepageSection::where('translation_group', $homepageSection->translation_group)->get()->keyBy('locale') : collect([$homepageSection->locale => $homepageSection]),
            'parents' => HomepageSection::where('locale', $homepageSection->locale)->whereNull('parent_id')->whereKeyNot($homepageSection->id)->orderBy('sort_order')->get(),
            'locales' => $this->locales(),
        ]);
    }

    public function update(Request $request, HomepageSection $homepageSection)
    {
        $request->merge(['locale' => $homepageSection->locale, 'translation_group' => $homepageSection->translation_group]);
        $data = $this->validated($request);
        $imagePaths = $this->storeImages($request);
        $data = $this->storeAssets($request, $data, $homepageSection);
        if ($imagePaths && ! $homepageSection->image_path) {
            $data['image_path'] = $imagePaths[0];
        }
        $this->ensureParentMatchesLocale($data['parent_id'] ?? null, $data['locale']);

        if (($data['parent_id'] ?? null) === $homepageSection->id) {
            return back()->withErrors(['parent_id' => 'Một section không thể là section cha của chính nó.']);
        }

        $oldIcon = $homepageSection->icon_path;
        DB::transaction(function () use ($homepageSection, $data, $imagePaths, $request) {
            $homepageSection->update($data);
            $this->attachImages($homepageSection, $imagePaths);
            $this->applyAssetsToTranslations($homepageSection, $request);
        });
        $this->deleteUnusedAsset($oldIcon);

        return redirect()->route('admin.homepage-sections.edit', $homepageSection)->with('success', 'Đã cập nhật section trang chủ.');
    }

    public function destroy(HomepageSection $homepageSection)
    {
        $locale = $homepageSection->locale;
        $translations = $homepageSection->translation_group
            ? HomepageSection::where('translation_group', $homepageSection->translation_group)->get()
            : collect([$homepageSection]);

        foreach ($translations as $translation) {
            $this->deleteAssetsRecursively($translation);
        }

        foreach ($translations as $translation) {
            $translation->delete();
        }

        return redirect()->route('admin.homepage-sections.index', ['locale' => $locale])->with('success', 'Đã xóa section và các section con ở tất cả ngôn ngữ.');
    }

    public function destroyImage(HomepageSection $homepageSection, HomepageSectionImage $image)
    {
        abort_unless($image->homepage_section_id === $homepageSection->id, 404);

        $path = $image->path;
        $image->delete();

        if ($homepageSection->image_path === $path) {
            $homepageSection->update(['image_path' => $homepageSection->images()->value('path')]);
        }

        $isStillUsed = HomepageSectionImage::where('path', $path)->exists()
            || HomepageSection::where('image_path', $path)->exists();

        if (! $isStillUsed) {
            $this->deleteUploadedAsset($path);
        }

        return back()->with('success', 'Đã xóa ảnh section.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'locale' => ['required', Rule::in(Menu::LOCALES)],
            'translation_group' => ['nullable', 'string', 'max:64', 'exists:homepage_sections,translation_group'],
            'parent_id' => ['nullable', 'integer', 'exists:homepage_sections,id'],
            'key' => ['nullable', 'alpha_dash', 'max:80'],
            'title' => ['required', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:5000'],
            'image_files' => ['nullable', 'array', 'max:20'],
            'image_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'icon_file' => ['nullable', 'file', 'mimes:png,webp,svg', 'max:20480'],
            'apply_images_all' => ['nullable', 'boolean'],
            'apply_icon_all' => ['nullable', 'boolean'],
            'apply_quick_items_all' => ['nullable', 'boolean'],
            'link_url' => ['nullable', 'string', 'max:2048'],
            'link_label' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'quick_items_present' => ['sometimes', 'in:1'],
            'quick_items' => ['nullable', 'array', 'max:20'],
            'quick_items.*' => ['array:existing_index,name,sub,icon_file,remove_icon'],
            'quick_items.*.existing_index' => ['nullable', 'integer', 'min:0'],
            'quick_items.*.name' => ['required', 'string', 'max:100'],
            'quick_items.*.sub' => ['nullable', 'string', 'max:100'],
            'quick_items.*.icon_file' => ['nullable', 'file', 'mimes:png,webp,svg', 'max:2048'],
            'quick_items.*.remove_icon' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function ensureParentMatchesLocale(?int $parentId, string $locale): ?HomepageSection
    {
        if (! $parentId) {
            return null;
        }

        return HomepageSection::whereKey($parentId)->where('locale', $locale)->firstOrFail();
    }

    private function storeAssets(Request $request, array $data, ?HomepageSection $section = null): array
    {
        unset($data['image_files'], $data['icon_file']);
        unset($data['quick_items_present']);
        unset($data['apply_images_all'], $data['apply_icon_all'], $data['apply_quick_items_all']);

        if ($request->has('quick_items_present')) {
            $items = [];
            foreach ($data['quick_items'] ?? [] as $row) {
                $existing = isset($row['existing_index']) ? ($section?->quick_items[$row['existing_index']] ?? []) : [];
                $iconPath = ! empty($row['remove_icon']) ? null : ($existing['icon_path'] ?? null);
                if (! empty($row['icon_file'])) {
                    $iconPath = ImageResizer::store($row['icon_file'], 'uploads/homepage-sections/quick-icons');
                }
                $items[] = ['name' => $row['name'], 'sub' => $row['sub'] ?? '', 'icon_path' => $iconPath];
            }
            $data['quick_items'] = $items;
        } else {
            unset($data['quick_items']);
        }

        foreach (['icon' => 'icon_file'] as $field => $input) {
            if (! $request->hasFile($input)) {
                continue;
            }

            $data[$field . '_path'] = ImageResizer::store($request->file($input), 'uploads/homepage-sections/' . $field . 's');
        }

        return $data;
    }

    private function applyAssetsToTranslations(HomepageSection $source, Request $request): void
    {
        $shareQuickItems = $request->has('quick_items_present') && $request->boolean('apply_quick_items_all');
        if (! $source->translation_group || (! $request->boolean('apply_images_all') && ! $request->boolean('apply_icon_all') && ! $shareQuickItems)) {
            return;
        }

        $images = $source->images()->orderBy('id')->get(['path', 'sort_order']);
        $translations = HomepageSection::where('translation_group', $source->translation_group)->whereKeyNot($source->id)->get();
        foreach ($translations as $translation) {
            if ($request->boolean('apply_images_all')) {
                $translation->images()->delete();
                $translation->images()->createMany($images->toArray());
                $translation->image_path = $source->image_path;
            }
            if ($request->boolean('apply_icon_all')) {
                $translation->icon_path = $source->icon_path;
            }
            if ($shareQuickItems) {
                $translation->quick_items = $source->quick_items;
            }
            $translation->save();
        }
    }

    private function deleteUnusedAsset(?string $path): void
    {
        if ($path && ! HomepageSection::where('icon_path', $path)->orWhere('image_path', $path)->exists()
            && ! HomepageSectionImage::where('path', $path)->exists()) {
            $this->deleteUploadedAsset($path);
        }
    }

    private function storeImages(Request $request): array
    {
        if (! $request->hasFile('image_files')) {
            return [];
        }

        return collect($request->file('image_files'))
            ->filter()
            ->map(fn ($image) => ImageResizer::store($image, 'uploads/homepage-sections/images'))
            ->all();
    }

    private function attachImages(HomepageSection $section, array $paths): void
    {
        $nextSortOrder = ((int) $section->images()->max('sort_order')) + 1;

        foreach ($paths as $path) {
            $section->images()->create(['path' => $path, 'sort_order' => $nextSortOrder++]);
        }
    }

    private function deleteUploadedAsset(?string $path): void
    {
        if ($path && Str::startsWith($path, 'uploads/homepage-sections/')) {
            File::delete(base_path($path));
        }
    }

    private function deleteAssetsRecursively(HomepageSection $section): void
    {
        foreach ($section->children()->get() as $child) {
            $this->deleteAssetsRecursively($child);
        }

        $this->deleteUploadedAsset($section->image_path);
        $this->deleteUploadedAsset($section->icon_path);

        foreach ($section->images as $image) {
            $this->deleteUploadedAsset($image->path);
        }
    }

    private function locales(): array
    {
        return ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文', 'ko' => '한국어'];
    }
}
