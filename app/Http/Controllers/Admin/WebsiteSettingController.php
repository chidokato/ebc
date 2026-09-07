<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Support\ImageResizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebsiteSettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.form', ['settings' => WebsiteSetting::current()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:2000'],
            'seo_keywords' => ['nullable', 'string', 'max:1000'],
            'head_code' => ['nullable', 'string', 'max:50000'],
            'footer_code' => ['nullable', 'string', 'max:50000'],
            'logo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'favicon_file' => ['nullable', 'file', 'mimes:ico,png,svg', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
            'white_logo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'remove_white_logo' => ['nullable', 'boolean'],
        ]);
        $settings = WebsiteSetting::current();
        foreach (['logo', 'white_logo', 'favicon'] as $asset) {
            if ($request->boolean('remove_'.$asset)) $data[$asset.'_path'] = null;
            if ($request->hasFile($asset.'_file')) {
                $file = $request->file($asset.'_file');
                if ($asset === 'favicon') {
                    $extension = $file->extension();
                    $filename = Str::uuid().'.'.$extension;
                    $file->move(base_path('uploads/settings'), $filename);
                    $data['favicon_path'] = 'uploads/settings/'.$filename;
                } else {
                    $data[$asset.'_path'] = ImageResizer::store($file, 'uploads/settings');
                }
            }
            unset($data[$asset.'_file'], $data['remove_'.$asset]);
        }
        $settings->fill($data);
        $settings->id = 1;
        $settings->save();

        return redirect()->route('admin.settings.edit')->with('success', 'Đã lưu cấu hình website.');
    }
}
