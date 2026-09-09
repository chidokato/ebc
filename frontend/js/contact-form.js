(() => {
    const form = document.querySelector('.consultation-form');
    if (!form) return;
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const button = form.querySelector('[type="submit"]');
        if (button.disabled) return;
        const status = form.querySelector('[role="status"]');
        const label = button.textContent;
        button.disabled = true;
        button.textContent = 'Đang gửi…';
        status.hidden = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST', body: new FormData(form),
                headers: {'Accept': 'application/json'},
            });
            const data = await response.json().catch(() => ({}));
            if (response.status === 422) {
                status.textContent = 'Vui lòng kiểm tra dịch vụ, họ tên, email, số điện thoại và chi tiết yêu cầu.';
            } else if (response.status === 429) {
                status.textContent = 'Bạn đã gửi nhiều yêu cầu. Vui lòng thử lại sau một phút.';
            } else if (response.status === 419) {
                status.textContent = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } else {
                status.textContent = data.message || 'Chưa gửi được yêu cầu. Vui lòng thử lại sau.';
            }
            if (response.ok && data.message) form.reset();
        } catch (_) {
            status.textContent = 'Không thể kết nối. Vui lòng kiểm tra mạng và thử lại.';
        } finally {
            status.hidden = false;
            button.disabled = false;
            button.textContent = label;
        }
    });
})();
