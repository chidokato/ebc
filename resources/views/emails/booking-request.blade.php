<h2>Yêu cầu đặt lịch — Ưu đãi 50%</h2>
<p><strong>Họ và tên:</strong> {{ $booking['name'] }}</p>
<p><strong>Số điện thoại:</strong> {{ $booking['phone'] }}</p>
<p><strong>Số lượng khách mời:</strong> {{ $booking['guests'] }}</p>
<p><strong>Ngày dự kiến tổ chức:</strong> {{ \Carbon\Carbon::parse($booking['date'])->format('d/m/Y') }}</p>
<p>Yêu cầu từ popup trên website Elite Business Center. Vui lòng liên hệ khách hàng để xác nhận lịch.</p>
