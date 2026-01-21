# Auto Delete Emails Script

Dự án này giúp tự động xóa các email cũ hơn một khoảng thời gian nhất định trong hộp thư IMAP của bạn.

## Cấu trúc file
- `config.example.php`: File mẫu cấu hình (cần đổi tên thành `config.php`).
- `delete_emails.php`: Script chính để thực thi logic xóa.
- `extension/`: Mã nguồn extension Chrome hỗ trợ (nếu dùng).

## Hướng dẫn cài đặt (Từ GitHub)

1.  **Clone repository**:
    ```bash
    git clone https://github.com/nqthaivl/Auto-Delete-Emails-Script-Php.git  
    cd repo-name
    ```

2.  **Cấu hình**:
    *   Đổi tên file `config.example.php` thành `config.php`.
    *   Mở `config.php` và điền thông tin email của bạn:
        ```php
        define('EMAIL_HOST', '{imap.yourservice.com:993/imap/ssl}INBOX');
        define('EMAIL_USERNAME', 'you@example.com');
        define('EMAIL_PASSWORD', 'your_password');
        ```

3.  **Chạy thử**:
    *   Chạy lệnh: `php delete_emails.php`
    *   Hoặc truy cập qua trình duyệt nếu để trên host.

## Cron Job (Tự động hóa)
Để chạy tự động mỗi 30 phút (ví dụ):
```cron
*/30 * * * * /usr/bin/php /path/to/script/delete_emails.php >> /path/to/script/logfile.log 2>&1
```
