@if($quickSection->quick_items)
<div class="section-quick-items">
    @foreach($quickSection->quick_items as $item)
        <div class="section-quick-item">
            @if(!empty($item['icon_path']))<img src="{{ asset($item['icon_path']) }}" alt="" loading="lazy">@endif
            <span>{{ $item['name'] }}</span>
            <strong>{{ $item['sub'] ?? '' }}</strong>
        </div>
    @endforeach
</div>
@endif
