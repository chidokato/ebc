<button class="booking-popup-launch" type="button" data-open-booking>Nhận ưu đãi 50%</button>
<dialog class="booking-popup" id="booking-popup" aria-labelledby="booking-popup-title">
    <div class="booking-popup-layout">
        {{-- Thay đường dẫn ảnh tại đây khi có ảnh chương trình chính thức. --}}
        <img class="booking-popup-photo" src="{{ asset('frontend/img/nghethuat.jpg') }}" alt="Không gian biểu diễn nghệ thuật tại Elite Business Center">
        <div class="booking-popup-card">
            <button class="booking-popup-close" type="button" aria-label="Đóng popup" data-close-booking autofocus>&times;</button>
            <h2 id="booking-popup-title"><em>Nhận</em> ƯU ĐÃI</h2>
            <div class="booking-popup-offer">
                <div class="booking-popup-hours"><em>Giảm</em><strong>ÁP DỤNG TRONG<br>KHUNG GIỜ</strong><span>✦ 07:00 – 22:00</span><span>✦ 07:00 – 22:00</span></div>
                <div class="booking-popup-discount">50<span>%</span></div>
            </div>
            <p class="booking-popup-subtitle">HỘI TRƯỜNG SỰ KIỆN &amp; PHÒNG HỘI THẢO</p>
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
                <button class="booking-popup-submit" type="submit">ĐẶT LỊCH NGAY</button>
                <p class="booking-popup-status" role="status" hidden></p>
            </form>
        </div>
    </div>
</dialog>
