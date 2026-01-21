<?php
// Cấu hình thông tin Email
define('EMAIL_HOST', '{imap.example.com:993/imap/ssl}INBOX'); // Máy chủ IMAP
define('EMAIL_USERNAME', 'your_email@example.com');           // Tên đăng nhập
define('EMAIL_PASSWORD', 'your_password');                    // Mật khẩu

// Cấu hình thời gian
define('MINUTES_TO_KEEP', 30); // Số PHÚT giữ lại email (xóa email cũ hơn X phút)

// Chế độ chạy thử (True = chỉ in ra màn hình, không xóa thật; False = xóa thật)
define('DRY_RUN', false); 
?>
