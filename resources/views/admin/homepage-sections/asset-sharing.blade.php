<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
    <label class="form-label mb-0" for="{{ $inputId }}">{{ $assetLabel }}</label>
    <div class="form-check form-switch mb-0">
        <input type="hidden" name="apply_{{ $assetField }}_all" value="0">
        <input class="form-check-input" type="checkbox" role="switch" id="apply_{{ $assetField }}_all" name="apply_{{ $assetField }}_all" value="1" @checked(old('apply_'.$assetField.'_all', !$editing && !$section->translation_group)) aria-describedby="share-{{ $assetField }}-help">
        <label class="form-check-label" for="apply_{{ $assetField }}_all">Áp dụng cho tất cả ngôn ngữ</label>
    </div>
</div>
<small class="text-muted d-block mb-2" id="share-{{ $assetField }}-help">
    Bật: khi lưu, {{ $assetField === 'images' ? 'toàn bộ ảnh hiện tại thay thế danh sách ảnh' : 'icon hiện tại thay thế icon' }} ở các ngôn ngữ khác. Tắt: chỉ sửa ngôn ngữ này. Mỗi bản ngôn ngữ vẫn có thể sửa riêng.
</small>
