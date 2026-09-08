@php
    $savedItems = collect($section->quick_items ?? [])->map(fn ($item, $index) => $item + ['existing_index' => $index])->all();
    $quickItems = old('quick_items_present') ? old('quick_items', []) : $savedItems;
@endphp
<div class="col-12">
    <div class="border rounded p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h5 class="mb-0">Thông tin nhanh — Icon, Name, Sub</h5>
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="apply_quick_items_all" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="apply_quick_items_all" name="apply_quick_items_all" value="1" @checked(old('apply_quick_items_all', !$editing && !$section->translation_group)) aria-describedby="share-quick-items-help">
                <label class="form-check-label" for="apply_quick_items_all">Áp dụng cho tất cả ngôn ngữ</label>
            </div>
            <button type="button" class="btn btn-soft-primary btn-sm" id="add-quick-item">+ Thêm dòng</button>
        </div>
        <small class="text-muted d-block mb-2" id="share-quick-items-help">Bật: khi lưu, toàn bộ Icon, Name, Sub và thứ tự các dòng thay thế thông tin nhanh ở các ngôn ngữ khác, kể cả khi xóa hết dòng. Tắt: chỉ sửa ngôn ngữ này.</small>
        <p class="text-muted">Ví dụ: icon diện tích · Area · 430m². Tối đa 20 dòng, hiển thị theo thứ tự nhập và lưu cùng section. Icon PNG, WebP hoặc SVG, tối đa 2 MB.</p>
        <input type="hidden" name="quick_items_present" value="1">
        @error('quick_items')<div class="text-danger">{{ $message }}</div>@enderror
        <div id="quick-items">
            @foreach($quickItems as $index => $item)
                @include('admin.homepage-sections.quick-item-row', ['index' => $index, 'item' => $item])
            @endforeach
        </div>
        <p id="quick-items-empty" class="text-muted mb-0" @if(count($quickItems)) hidden @endif>Chưa có thông tin nhanh. Bấm “Thêm dòng” để bắt đầu.</p>
    </div>
</div>
<template id="quick-item-template">
    @include('admin.homepage-sections.quick-item-row', ['index' => '__INDEX__', 'item' => []])
</template>
@push('scripts')
<script src="{{ asset('admin-assets/js/section-quick-items.js') }}"></script>
@endpush
