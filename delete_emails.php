<?php
/**
 * Script tự động xóa email cũ - Phiên bản Giao diện Đẹp
 * Hỗ trợ cả Web (HTML/CSS) và CLI (Text only cho Cron)
 */

// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// Kiểm tra môi trường chạy
$isCli = (php_sapi_name() === 'cli');

// --- HÀM HỖ TRỢ GIAO DIỆN ---
function printHeader($isCli) {
    if ($isCli) {
        echo "=== AUTO DELETE EMAILS SCRIPT ===" . PHP_EOL;
        return;
    }
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Auto Delete Emails | Quản Lý Hộp Thư</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary: #6366f1;
                --primary-dark: #4f46e5;
                --success: #10b981;
                --danger: #ef4444;
                --warning: #f59e0b;
                --bg: #0f172a;
                --card-bg: #1e293b;
                --text: #f8fafc;
                --text-muted: #94a3b8;
                --border: #334155;
            }

            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Outfit', sans-serif;
                background-color: var(--bg);
                color: var(--text);
                line-height: 1.6;
                padding: 2rem;
                min-height: 100vh;
                background-image: radial-gradient(circle at top right, #1e1b4b, transparent 40%),
                                  radial-gradient(circle at bottom left, #312e81, transparent 40%);
            }

            .container {
                max-width: 1000px;
                margin: 0 auto;
            }

            header {
                margin-bottom: 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid var(--border);
                padding-bottom: 1rem;
            }

            h1 { font-size: 1.8rem; font-weight: 700; background: linear-gradient(to right, #818cf8, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
            .badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; }
            .badge.dry-run { background: rgba(245, 158, 11, 0.1); color: var(--warning); border: 1px solid rgba(245, 158, 11, 0.2); }
            .badge.live { background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
                margin-bottom: 2rem;
            }

            .card {
                background: var(--card-bg);
                border: 1px solid var(--border);
                border-radius: 1rem;
                padding: 1.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(10px);
            }

            .card h3 { font-size: 0.875rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
            .card .value { font-size: 2rem; font-weight: 700; color: var(--text); }

            .log-container {
                background: #000;
                border: 1px solid var(--border);
                border-radius: 1rem;
                padding: 1rem;
                height: 400px;
                overflow-y: auto;
                font-family: 'Courier New', monospace;
                font-size: 0.9rem;
                box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.3);
            }

            .log-item { padding: 0.25rem 0; border-bottom: 1px solid #1a1a1a; display: flex; gap: 1rem; }
            .log-item:last-child { border-bottom: none; }
            .log-time { color: var(--text-muted); min-width: 140px; }
            .log-msg { color: var(--text); flex: 1; }
            .log-type { font-weight: bold; min-width: 60px; }
            
            .text-success { color: var(--success); }
            .text-danger { color: var(--danger); }
            .text-warning { color: var(--warning); }
            .text-info { color: var(--primary); }

            /* Scrollbar */
            ::-webkit-scrollbar { width: 8px; }
            ::-webkit-scrollbar-track { background: var(--bg); }
            ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
            ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <div>
                    <h1>Hệ Thống Dọn Dẹp Email</h1>
                    <p style="color: var(--text-muted); margin-top: 0.25rem;">Tự động xóa email cũ hơn quy định</p>
                </div>
                <div>
                    <span class="badge <?php echo DRY_RUN ? 'dry-run' : 'live'; ?>">
                        <?php echo DRY_RUN ? '🔧 CHẾ ĐỘ CHẠY THỬ (DRY RUN)' : '🚀 CHẾ ĐỘ XÓA THẬT (LIVE)'; ?>
                    </span>
                </div>
            </header>

        <div class="stats-grid">
                <div class="card">
                    <h3>Tài khoản</h3>
                    <div class="value" style="font-size: 1.25rem; word-break: break-all;"><?php echo EMAIL_USERNAME; ?></div>
                </div>
                <div class="card">
                    <h3>Tổng Email</h3>
                    <div class="value" id="stat-total">...</div>
                </div>
                <div class="card" style="border-color: var(--danger);">
                    <h3>Đã xóa</h3>
                    <div class="value" id="stat-deleted">0</div>
                </div>
                <div class="card" style="border-color: var(--success);">
                    <h3>Còn lại</h3>
                    <div class="value" id="stat-remaining">...</div>
                </div>
                <div class="card" style="border-color: var(--primary);">
                    <h3>Trạng thái</h3>
                    <div class="value" id="status-text">Đang chạy...</div>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 1rem;">Nhật ký hoạt động (Logs)</h3>
                <div class="log-container" id="log-box">
    <?php
    // Flush buffer để hiển thị HTML header ngay lập tức
    if (function_exists('ob_flush')) ob_flush();
    flush();
}

function printFooter($isCli, $totalDeleted, $totalInit) {
    $remaining = $totalInit - (DRY_RUN ? 0 : $totalDeleted);
    
    if ($isCli) {
        echo "=== HOÀN TẤT. Tổng: $totalInit | Đã xóa: $totalDeleted | Còn lại: $remaining ===" . PHP_EOL;
    } else {
        ?>
                </div> <!-- End log-container -->
            </div> <!-- End card -->
            
            <div style="margin-top: 2rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
                &copy; <?php echo date('Y'); ?> Email Automation System
            </div>
        </div> <!-- End container -->
        
        <script>
            // Tự động cuộn xuống cuối log
            const logBox = document.getElementById('log-box');
            logBox.scrollTop = logBox.scrollHeight;
            
            // Cập nhật trạng thái khi xong
            document.getElementById('status-text').innerText = "Hoàn tất";
            document.getElementById('status-text').style.color = "var(--success)";
            
            // Cập nhật số liệu cuối cùng
            document.getElementById('stat-deleted').innerText = "<?php echo $totalDeleted; ?>";
            document.getElementById('stat-remaining').innerText = "<?php echo $remaining; ?>";
        </script>
    </body>
    </html>
    <?php
    }
}

function logMsg($msg, $type = 'INFO', $isCli) {
    $time = date('H:i:s');
    if ($isCli) {
        echo "[$time][$type] $msg" . PHP_EOL;
    } else {
        $colorClass = 'text-info';
        if ($type === 'DELETE') $colorClass = 'text-success';
        if ($type === 'ERROR') $colorClass = 'text-danger';
        if ($type === 'WARN') $colorClass = 'text-warning';
        
        echo "<div class='log-item'>
                <div class='log-time'>$time</div>
                <div class='log-type $colorClass'>$type</div>
                <div class='log-msg'>$msg</div>
              </div>";
        // Cố gắng đẩy output ra trình duyệt ngay
        flush();
    }
}

// --- BẮT ĐẦU LOGIC CHÍNH ---

printHeader($isCli);

logMsg("Bắt đầu kết nối tới máy chủ...", 'INFO', $isCli);

// 1. Kết nối kết IMAP
$inbox = @imap_open(EMAIL_HOST, EMAIL_USERNAME, EMAIL_PASSWORD);
if (!$inbox) {
    logMsg("Không thể kết nối tới Email Server: " . imap_last_error(), 'ERROR', $isCli);
    if (!$isCli) echo "</div></div></body></html>";
    exit;
}
logMsg("Kết nối thành công!", 'INFO', $isCli);

// Lấy tổng số email hiện tại
$totalEmailsInit = imap_num_msg($inbox);
if (!$isCli) {
    echo "<script>
        document.getElementById('stat-total').innerText = '$totalEmailsInit';
        document.getElementById('stat-remaining').innerText = '$totalEmailsInit'; 
    </script>";
}
logMsg("Tổng số email trong hộp thư: $totalEmailsInit", 'INFO', $isCli);

// 2. Tính toán mốc thời gian
$minutes = MINUTES_TO_KEEP;
$cutoffTimestamp = time() - ($minutes * 60);
$cutoffDateStr = date('d-M-Y', $cutoffTimestamp); 

logMsg("Đang tìm email cũ hơn: " . date('Y-m-d H:i:s', $cutoffTimestamp), 'INFO', $isCli);

$totalDeleted = 0;

function processEmailList($inbox, $emailIds, $cutoffTimestamp, $checkPreciseTime, $isCli) {
    global $totalDeleted;
    
    if (empty($emailIds)) return;
    rsort($emailIds); // Xử lý từ mới nhất trong danh sách tìm được

    foreach ($emailIds as $emailId) {
        $shouldDelete = true;
        
        // Lấy thông tin header
        $overview = imap_fetch_overview($inbox, $emailId, 0);
        $subject = isset($overview[0]->subject) ? $overview[0]->subject : '(No Subject)';
        // Mã hóa lại tiêu đề nếu bị lỗi font (phổ biến với tiếng Việt trong mail header)
        $subjectPreview = mb_decode_mimeheader($subject);
        $udate = isset($overview[0]->udate) ? $overview[0]->udate : 0;
        $dateStr = date('d/m/Y H:i', $udate);

        if ($checkPreciseTime) {
            if ($udate >= $cutoffTimestamp) {
                $shouldDelete = false; 
            }
        }

        if ($shouldDelete) {
            if (DRY_RUN) {
                logMsg("[Dry Run] Sẽ xóa: '$subjectPreview' ($dateStr)", 'WARN', $isCli);
                $totalDeleted++;
            } else {
                if (imap_delete($inbox, $emailId)) {
                    logMsg("Đã xóa: '$subjectPreview' ($dateStr)", 'DELETE', $isCli);
                    $totalDeleted++;
                } else {
                    logMsg("Lỗi xóa ID $emailId", 'ERROR', $isCli);
                }
            }
        }
    }
}

// Giai đoạn 1: Email thuộc ngày cũ hơn
logMsg("Quét các ngày trước $cutoffDateStr...", 'INFO', $isCli);
$emailsOlderDays = imap_search($inbox, 'BEFORE "' . $cutoffDateStr . '"');
if ($emailsOlderDays) {
    logMsg("Tìm thấy " . count($emailsOlderDays) . " email.", 'INFO', $isCli);
    processEmailList($inbox, $emailsOlderDays, $cutoffTimestamp, false, $isCli);
} else {
    logMsg("Không có email nào ở các ngày trước.", 'INFO', $isCli);
}

// Giai đoạn 2: Email trong ngày cutoff
logMsg("Quét email trong ngày $cutoffDateStr...", 'INFO', $isCli);
$emailsOnCutoffDay = imap_search($inbox, 'ON "' . $cutoffDateStr . '"');
if ($emailsOnCutoffDay) {
    logMsg("Tìm thấy " . count($emailsOnCutoffDay) . " email trong ngày này. Đang lọc theo giờ...", 'INFO', $isCli);
    processEmailList($inbox, $emailsOnCutoffDay, $cutoffTimestamp, true, $isCli);
} else {
    logMsg("Không có email nào trong ngày này.", 'INFO', $isCli);
}

// Dọn dẹp
if ($totalDeleted > 0 && !DRY_RUN) {
    logMsg("Đang thực hiện dọn dẹp vĩnh viễn (Expunge)...", 'INFO', $isCli);
    if (imap_expunge($inbox)) {
        logMsg("Dọn dẹp thành công.", 'INFO', $isCli);
    } else {
        logMsg("Lỗi dọn dẹp.", 'ERROR', $isCli);
    }
}

imap_close($inbox);
printFooter($isCli, $totalDeleted, $totalEmailsInit);
?>
