# TÀI LIỆU HƯỚNG DẪN VẬN HÀNH HỆ THỐNG SỰ KIỆN

_(Dành cho Khách hàng & Ban Tổ chức)_

Tài liệu này mô tả chi tiết quy trình trải nghiệm và vận hành trên hệ thống, giúp người dùng dễ dàng hình dung các bước từ lúc tạo sự kiện đến khi kết thúc.

---

## MỤC LỤC

1. [Tổng Quan Hệ Thống](#1-tổng-quan-hệ-thống)
2. [Quy Trình 1: Thiết Lập Sự Kiện (Dành cho Ban Tổ Chức)](#2-quy-trình-1-thiết-lập-sự-kiện-dành-cho-ban-tổ-chức)
3. [Quy Trình 2: Đặt Vé & Thanh Toán (Dành cho Khách Tham Dự)](#3-quy-trình-2-đặt-vé--thanh-toán-dành-cho-khách-tham-dự)
4. [Quy Trình 3: Check-in Tại Sự Kiện](#4-quy-trình-3-check-in-tại-sự-kiện)
5. [Quy Trình 4: Báo Cáo & Sau Sự Kiện](#5-quy-trình-4-báo-cáo--sau-sự-kiện)

---

## 1. Tổng Quan Hệ Thống

Hệ thống cung cấp giải pháp toàn diện cho việc tổ chức sự kiện, bao gồm:

- **Website bán vé**: Giao diện đẹp, dễ sử dụng cho khách hàng.
- **Hệ thống quản trị (Admin)**: Dành cho ban tổ chức để quản lý vé, doanh thu.
- **Ứng dụng soát vé (Check-in)**: Kiểm soát ra vào nhanh chóng.

---

## 2. Quy Trình 1: Thiết Lập Sự Kiện (Dành cho Ban Tổ Chức)

Đây là bước khởi tạo để đưa sự kiện lên hệ thống.

### Bước 1: Khởi tạo thông tin

Ban tổ chức đăng nhập vào trang quản trị và nhập các thông tin hấp dẫn để thu hút khán giả:

- **Tên sự kiện**: Ngắn gọn, ấn tượng.
- **Thời gian & Địa điểm**: Chọn ngày giờ bắt đầu, kết thúc và địa chỉ tổ chức cụ thể (tích hợp bản đồ).
- **Hình ảnh & Mô tả**: Tải lên Poster sự kiện, viết nội dung giới thiệu chi tiết.

### Bước 2: Thiết lập Sơ đồ ghế và Hạng vé

Hệ thống cho phép cấu hình linh hoạt theo thực tế khán phòng:

- **Tạo Khu vực (Zone)**: Ví dụ: Khu VIP, Khu Thường (Standard), Khu Cánh gà.
- **Định giá vé**: Thiết lập mức giá khác nhau cho từng khu vực hoặc từng ghế.
- **Sơ đồ ghế ngồi**: Hệ thống tự động sinh ra sơ đồ ghế (Ví dụ: Hàng A từ A1-A20). Ban tổ chức có thể khóa trước các ghế dành cho đại biểu.

### 🚩 Bảng Kiểm tra (Checklist) trước khi mở bán:

1.  [ ] Tên và nội dung sự kiện đã chính xác, không sai chính tả?
2.  [ ] Thời gian và địa điểm đã khớp với thực tế?
3.  [ ] Giá vé đã được cập nhật đúng cho từng khu vực chưa?
4.  [ ] Đã test thử hiển thị trên điện thoại chưa?

---

## 3. Quy Trình 2: Đặt Vé & Thanh Toán (Dành cho Khách Tham Dự)

Trải nghiệm mua vé online nhanh chóng, tiện lợi.

### Bước 1: Chọn Sự Kiện & Ghế Ngồi

- Khách hàng truy cập website, xem danh sách sự kiện "Sắp diễn ra".
- Xem sơ đồ khán phòng trực quan:
    - **Ghế màu Xám**: Còn trống (Có thể chọn).
    - **Ghế màu Đỏ**: Đã có người mua.
    - **Ghế màu Xanh**: Đang chọn.

### Bước 2: Thanh toán an toàn

- Khách hàng xem lại giỏ hàng và tổng tiền.
- Lựa chọn phương thức thanh toán:
    - **Chuyển khoản (VietQR)**: Hệ thống hiển thị mã QR kèm số tiền và nội dung chuyển khoản chính xác. Khách hàng chỉ cần mở App ngân hàng quét là xong.
    - **Vé Miễn phí**: Nếu sự kiện miễn phí, khách hàng chỉ cần xác nhận đăng ký.

### Bước 3: Nhận Vé Điện Tử (E-Ticket) qua Email

- Sau khi thanh toán thành công, hệ thống **NGAY LẬP TỨC** gửi vé về Email của khách hàng.
- Nội dung Email bao gồm:
    - Thông tin sự kiện.
    - Vị trí ghế ngồi (Ví dụ: Khu VIP - Hàng A - Ghế 05).
    - **Mã QR Code**: Dùng để check-in khi đến tham dự.

---

## 4. Quy Trình 3: Check-in Tại Sự Kiện

Đảm bảo việc đón khách chuyên nghiệp, tránh ùn tắc.

### Tại quầy soát vé:

1.  Khách hàng đến sự kiện, mở Email hoặc Ảnh chụp màn hình có chứa mã **QR Code**.
2.  Nhân viên sự kiện dùng ứng dụng soát vé (trên điện thoại hoặc máy quét) để quét mã QR.
3.  **Hệ thống phản hồi tức thì**:
    - **Hợp lệ**: Màn hình hiện màu Xanh, hiện tên khách và số ghế. Mời khách vào.
    - **Không hợp lệ**: Màn hình hiện màu Đỏ (Vé giả, vé sai sự kiện).
    - **Đã sử dụng**: Cảnh báo vé này đã quét trước đó (tránh dùng 1 vé cho nhiều người).

---

## 5. Quy Trình 4: Báo Cáo & Sau Sự Kiện

Dành cho Ban tổ chức tổng kết hiệu quả.

### Báo cáo & Thống kê:

- **Doanh thu thực tế**: Tổng tiền vé bán được (cập nhật theo thời gian thực).
- **Tỷ lệ tham gia**: Bao nhiêu khách mua vé so với bao nhiêu khách thực tế đến (Check-in).
- **Danh sách khách hàng**: Xuất file Excel danh sách khán giả để gửi email cảm ơn hoặc marketing cho sự kiện sau.

---

_Tài liệu này được biên soạn để đảm bảo tính dễ hiểu và dễ vận hành cho mọi đối tượng người dùng hệ thống._
