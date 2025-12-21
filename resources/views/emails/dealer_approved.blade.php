<x-mail::message>
# Xin chào {{ $user->name }},

Chúc mừng! 🎉 Yêu cầu nâng cấp tài khoản của bạn đã được Admin phê duyệt.

Tài khoản của bạn hiện đã chính thức trở thành **Đại lý (Dealer)**. 
Bây giờ bạn có thể truy cập vào các tính năng dành riêng cho đại lý, quản lý kho hàng và xem chính sách giá ưu đãi.

Vui lòng đăng nhập lại để cập nhật quyền hạn mới nhất.

<x-mail::button :url="config('app.url')">
Truy cập hệ thống ngay
</x-mail::button>

Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với đội ngũ hỗ trợ.

Trân trọng,<br>
Đội ngũ {{ config('app.name') }}
</x-mail::message>