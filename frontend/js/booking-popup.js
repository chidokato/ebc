(() => {
    const dialog = document.getElementById('booking-popup');
    if (!dialog || typeof dialog.showModal !== 'function') return;
    const form = document.getElementById('booking-popup-form');
    const date = form.elements.date;
    const now = new Date();
    date.min = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    let previousFocus;
    let autoOpen;
    const open = () => {
        clearTimeout(autoOpen);
        if (dialog.open) return;
        previousFocus = document.activeElement;
        dialog.showModal();
        document.body.classList.add('booking-popup-open');
        try { sessionStorage.setItem('ebc-booking-popup-seen', '1'); } catch (_) {}
    };
    // Explicit trigger, also supported on links and dynamically inserted buttons.
    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-open-booking]');
        if (!trigger || trigger.matches(':disabled, [aria-disabled="true"]')) return;
        event.preventDefault();
        open();
    }, true);
    dialog.querySelector('[data-close-booking]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    dialog.addEventListener('close', () => {
        document.body.classList.remove('booking-popup-open');
        if (previousFocus && previousFocus.isConnected) previousFocus.focus({preventScroll: true});
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const button = form.querySelector('[type="submit"]');
        if (button.disabled) return;
        const status = form.querySelector('[role="status"]');
        const label = button.textContent;
        button.disabled = true;
        button.textContent = 'ĐANG GỬI…';
        status.hidden = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST', body: new FormData(form),
                headers: {'Accept': 'application/json'},
            });
            const data = await response.json().catch(() => ({}));
            if (response.status === 422) {
                status.textContent = 'Vui lòng kiểm tra họ tên, số điện thoại, số khách và ngày tổ chức (từ hôm nay trở đi).';
            } else if (response.status === 429) {
                status.textContent = 'Bạn đã gửi nhiều yêu cầu. Vui lòng thử lại sau một phút.';
            } else if (response.status === 419) {
                status.textContent = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } else {
                status.textContent = data.message || 'Chưa gửi được yêu cầu. Vui lòng thử lại sau.';
            }
            if (response.ok) form.reset();
        } catch (_) {
            status.textContent = 'Không thể kết nối. Vui lòng kiểm tra mạng và thử lại.';
        } finally {
            status.hidden = false;
            button.disabled = false;
            button.textContent = label;
        }
    });
    let seen = false;
    try { seen = sessionStorage.getItem('ebc-booking-popup-seen') === '1'; } catch (_) {}
    if (!seen) autoOpen = setTimeout(open, 1200);
})();
