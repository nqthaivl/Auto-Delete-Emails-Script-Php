document.addEventListener('DOMContentLoaded', () => {
    // Load saved settings
    chrome.storage.local.get(['url', 'hours', 'minutes', 'seconds', 'isRunning'], (result) => {
        if (result.url) document.getElementById('url').value = result.url;
        if (result.hours !== undefined) document.getElementById('hours').value = result.hours;
        if (result.minutes !== undefined) document.getElementById('minutes').value = result.minutes;
        if (result.seconds !== undefined) document.getElementById('seconds').value = result.seconds;
        
        updateStatus(result.isRunning);
    });

    document.getElementById('btnSave').addEventListener('click', () => {
        const url = document.getElementById('url').value;
        const hours = parseInt(document.getElementById('hours').value) || 0;
        const minutes = parseInt(document.getElementById('minutes').value) || 0;
        const seconds = parseInt(document.getElementById('seconds').value) || 0;

        if (!url) {
            showStatus('Vui lòng nhập URL!', 'error');
            return;
        }

        const totalMinutes = (hours * 60) + minutes + (seconds / 60);
        
        if (totalMinutes <= 0) {
            showStatus('Thời gian phải lớn hơn 0!', 'error');
            return;
        }

        // Save settings and notify background
        chrome.storage.local.set({
            url: url,
            hours: hours,
            minutes: minutes,
            seconds: seconds,
            isRunning: true
        }, () => {
            chrome.runtime.sendMessage({
                action: 'START_ALARM',
                url: url,
                periodInMinutes: totalMinutes
            });
            showStatus('Đã lưu và bắt đầu hẹn giờ!', 'success');
            updateStatus(true);
        });
    });

    document.getElementById('btnStop').addEventListener('click', () => {
        chrome.storage.local.set({ isRunning: false }, () => {
            chrome.runtime.sendMessage({ action: 'STOP_ALARM' });
            showStatus('Đã dừng tự động chạy.', 'success');
            updateStatus(false);
        });
    });

    function showStatus(msg, type) {
        const statusEl = document.getElementById('status');
        statusEl.textContent = msg;
        statusEl.className = type;
        setTimeout(() => {
            statusEl.textContent = '';
            statusEl.className = '';
        }, 3000);
    }

    function updateStatus(isRunning) {
        const statusEl = document.getElementById('status');
        if (isRunning) {
            statusEl.textContent = 'Trạng thái: ĐANG CHẠY';
            statusEl.className = 'running';
        } else {
            statusEl.textContent = 'Trạng thái: ĐÃ DỪNG';
            statusEl.className = 'stopped';
        }
    }
});
