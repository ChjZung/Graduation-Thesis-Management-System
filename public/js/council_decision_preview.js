(() => {
    const element = document.getElementById('councilDecisionPreviewModal');
    if (!element || !window.bootstrap?.Modal) return;
    const target = new URL(element.dataset.previewUrl, location.href);
    const body = element.querySelector('[data-decision-preview-body]');
    const modal = bootstrap.Modal.getOrCreateInstance(element);
    let controller;
    document.addEventListener('click', async event => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const url = new URL(link.href, location.href);
        if (url.origin !== target.origin || url.pathname !== target.pathname) return;
        event.preventDefault(); event.stopPropagation();
        controller?.abort(); controller = new AbortController();
        const signal = controller.signal;
        body.innerHTML = '<div class="text-center p-5" role="status"><span class="spinner-border spinner-border-sm me-2"></span>Đang tạo bản xem trước quyết định…</div>';
        modal.show(); url.searchParams.set('popup', '1');
        try {
            const response = await fetch(url, { signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok || response.redirected) throw new Error('Không tạo được bản xem trước. Kiểm tra đợt đã chọn hoặc tải lại trang để đăng nhập lại.');
            const html = await response.text();
            if (signal.aborted) return;
            const fragment = document.createElement('div'); fragment.innerHTML = html;
            const content = fragment.querySelector('.decision-preview-content');
            if (!content) throw new Error('Không tải được nội dung quyết định. Vui lòng tải lại trang.');
            body.replaceChildren(content);
        } catch (error) {
            if (signal.aborted) return;
            const alert = document.createElement('div'); alert.className = 'alert alert-danger m-3';
            alert.setAttribute('role', 'alert'); alert.textContent = error.message; body.replaceChildren(alert);
        }
    }, true);
    element.addEventListener('hidden.bs.modal', () => { controller?.abort(); body.replaceChildren(); });
})();
