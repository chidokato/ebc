@if($popupSettings->is_enabled)
<button class="booking-popup-launch" type="button" data-open-booking>{{ $popupSettings->launch_label }}</button>
<dialog class="booking-popup" id="booking-popup" aria-labelledby="booking-popup-title">
    <div class="booking-popup-layout">
        <img class="booking-popup-photo" src="{{ $popupSettings->image_url }}" alt="{{ $popupSettings->title }}">
        <div class="booking-popup-card">
            <button class="booking-popup-close" type="button" aria-label="Đóng popup" data-close-booking autofocus>&times;</button>
            <h2 id="booking-popup-title">{{ $popupSettings->title }}</h2>
            <div class="booking-popup-offer">
                <div class="booking-popup-hours booking-popup-custom-offer">{{ $popupSettings->offer_content }}</div>
                <img class="booking-popup-discount" src="{{ $popupSettings->offer_image_url }}" alt="Ưu đãi" width="435" height="257">
            </div>
            @if($popupSettings->content)<p class="booking-popup-subtitle">{{ $popupSettings->content }}</p>@endif
            <form id="booking-popup-form" action="{{ route('booking.store') }}" method="post">
                @csrf
                <label for="booking-name">Họ và tên</label>
                <input id="booking-name" name="name" autocomplete="name" placeholder="Họ và tên" maxlength="100" required>
                <label for="booking-phone">Số điện thoại</label>
                <input id="booking-phone" name="phone" type="tel" autocomplete="tel" placeholder="Số điện thoại" pattern="[+0-9 ()\.\-]{8,20}" maxlength="20" required>
                <div class="booking-popup-fields">
                    <div><label for="booking-guests">Số lượng khách mời</label><input id="booking-guests" name="guests" type="number" min="1" max="100000" placeholder="Số lượng khách" required></div>
                    <div><label for="booking-date">Ngày dự kiến tổ chức</label><input id="booking-date" name="date" type="date" required></div>
                </div>
                <button class="booking-popup-submit" type="submit">{{ $popupSettings->submit_label }}</button>
                <p class="booking-popup-status" role="status" hidden></p>
            </form>
        </div>
    </div>
</dialog>
@endif
