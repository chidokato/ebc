<div class="row g-2 align-items-start mb-3 quick-item-row">
    <input type="hidden" name="quick_items[{{ $index }}][existing_index]" value="{{ $item['existing_index'] ?? '' }}">
    <div class="col-md-4">
        <label class="form-label" for="quick-icon-{{ $index }}">Icon</label>
        <input id="quick-icon-{{ $index }}" class="form-control" type="file" name="quick_items[{{ $index }}][icon_file]" accept="image/png,image/webp,image/svg+xml">
        @php($savedIcon = isset($item['existing_index']) ? ($section->quick_items[$item['existing_index']]['icon_path'] ?? null) : null)
        <img class="quick-icon-preview mt-2" @if($savedIcon) src="{{ asset($savedIcon) }}" @else hidden @endif alt="Xem trước icon" style="width:36px;height:36px;object-fit:contain">
        @if($savedIcon)<label class="d-block mt-1"><input type="checkbox" name="quick_items[{{ $index }}][remove_icon]" value="1" @checked(!empty($item['remove_icon']))> Xóa icon hiện tại</label>@endif
        @error("quick_items.$index.icon_file")<div class="text-danger">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="quick-name-{{ $index }}">Name</label>
        <input id="quick-name-{{ $index }}" class="form-control" name="quick_items[{{ $index }}][name]" value="{{ $item['name'] ?? '' }}" maxlength="100" placeholder="Area" required>
        @error("quick_items.$index.name")<div class="text-danger">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="quick-sub-{{ $index }}">Sub</label>
        <input id="quick-sub-{{ $index }}" class="form-control" name="quick_items[{{ $index }}][sub]" value="{{ $item['sub'] ?? '' }}" maxlength="100" placeholder="430m²">
        @error("quick_items.$index.sub")<div class="text-danger">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2"><button type="button" class="btn btn-soft-danger mt-md-4 remove-quick-item">Xóa dòng</button></div>
</div>
