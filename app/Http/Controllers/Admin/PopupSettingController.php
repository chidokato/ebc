<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopupSetting;
use App\Support\ImageResizer;
use Illuminate\Http\Request;

class PopupSettingController extends Controller
{
    public function edit()
    {
        return view('admin.popup-settings.form', ['settings' => PopupSetting::current()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'is_enabled' => ['required', 'boolean'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:5000'],
            'offer_content' => ['nullable', 'string', 'max:1000'],
            'launch_label' => ['required', 'string', 'max:100'],
            'submit_label' => ['required', 'string', 'max:100'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'offer_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'remove_offer_image' => ['nullable', 'boolean'],
        ]);
        foreach (['image', 'offer_image'] as $field) {
            if ($request->boolean('remove_'.$field)) $data[$field.'_path'] = null;
            if ($request->hasFile($field.'_file')) {
                $data[$field.'_path'] = ImageResizer::store($request->file($field.'_file'), 'uploads/popup');
            }
            unset($data[$field.'_file'], $data['remove_'.$field]);
        }
        $settings = PopupSetting::current();
        $settings->fill($data);
        $settings->id = 1;
        $settings->save();

        return redirect()->route('admin.popup-settings.edit')->with('success', 'Đã lưu cấu hình popup.');
    }
}
