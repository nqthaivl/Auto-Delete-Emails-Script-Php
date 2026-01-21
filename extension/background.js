// Xử lý sự kiện từ Popup
chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
    if (message.action === 'START_ALARM') {
        startAlarm(message.periodInMinutes);
    } else if (message.action === 'STOP_ALARM') {
        stopAlarm();
    }
});

// Hàm tạo alarm
function startAlarm(periodInMinutes) {
    chrome.alarms.create('autoRunUrl', {
        delayInMinutes: periodInMinutes, // Chạy lần đầu sau khoảng thời gian này
        periodInMinutes: periodInMinutes // Lặp lại
    });
    console.log(`Alarm started. Period: ${periodInMinutes} minutes.`);
}

// Hàm dừng alarm
function stopAlarm() {
    chrome.alarms.clear('autoRunUrl');
    console.log('Alarm stopped.');
}

// Lắng nghe sự kiện Alarm kích hoạt
chrome.alarms.onAlarm.addListener((alarm) => {
    if (alarm.name === 'autoRunUrl') {
        chrome.storage.local.get(['url'], (result) => {
            if (result.url) {
                openUrl(result.url);
            }
        });
    }
});

// Hàm mở URL
function openUrl(url) {
    // Kiểm tra xem URL có hợp lệ không
    if (!url.startsWith('http')) {
        url = 'http://' + url;
    }

    // Tạo tab mới (hoặc có thể update tab hiện tại nếu muốn)
    chrome.tabs.create({ url: url, active: false }, (tab) => {
        // Tùy chọn: Có thể tự động đóng tab sau 1 phút nếu chỉ cần request chạy background
        // setTimeout(() => chrome.tabs.remove(tab.id), 60000);
        console.log(`Opened tab: ${url}`);
    });
}
