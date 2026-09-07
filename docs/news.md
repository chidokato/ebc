# Tin tức đa ngôn ngữ

Vào Admin → Tin tức đa ngôn ngữ. Chọn ngôn ngữ để thêm, sửa hoặc xóa một bản tin.

## Nhập bản dịch thủ công

1. Tạo và lưu bài ở ngôn ngữ mong muốn.
2. Trên màn hình sửa bài, chọn tab ngôn ngữ có “+ Thêm bản dịch”.
3. Nhập tiêu đề, tóm tắt, nội dung và ảnh cho bản ngôn ngữ đó.
4. Chọn “Bản nháp” hoặc “Đăng lên website”, rồi lưu.

Mỗi bản ngôn ngữ có nội dung, ảnh và trạng thái riêng. Sửa hoặc xóa một bản không thay đổi các bản ngôn ngữ khác. Hệ thống không tự dịch và không gửi nội dung đến dịch vụ dịch bên ngoài.

Phần nội dung sử dụng trình soạn thảo với tiêu đề, chữ đậm/nghiêng, danh sách, trích dẫn và liên kết. Định dạng được lưu và hiển thị trên website. Nội dung cũ vẫn giữ xuống dòng. Chỉ các thẻ và liên kết an toàn được giữ lại; giới hạn 20.000 ký tự bao gồm mã định dạng. Trang danh sách: `/{locale}/news`; trang chi tiết: `/{locale}/news/{id}`. Trang chủ chỉ lấy bài đã đăng đúng ngôn ngữ, tối đa 9 bài mới nhất.

## Kiểm tra

Đặt `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` và `APP_URL=http://localhost`, rồi chạy `php artisan test --filter=NewsTest` để kiểm tra với cơ sở dữ liệu trong bộ nhớ.