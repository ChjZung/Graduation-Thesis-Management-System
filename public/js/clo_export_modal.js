(() => {
    const init = () => {
        const element = document.getElementById('cloExportModal');
        if (!element || !window.bootstrap?.Modal) return;
        const selectionUrl = new URL(element.dataset.selectionUrl, location.href);
        const modal = bootstrap.Modal.getOrCreateInstance(element);
        const body = element.querySelector('[data-clo-export-body]');
        let controller;

        function initForm(form) {
            const options = JSON.parse(form.dataset.options || '[]');
            const semester = form.querySelector('[name="MaHocKy"]');
            const major = form.querySelector('[name="MaNganh"]');
            const round = form.querySelector('[name="DotBaoVe"]');
            const council = form.querySelector('[name="MaHoiDong"]');
            const button = form.querySelector('[type="submit"]');
            function updateSummary() {
                button.disabled = !semester.value || !major.value;
                form.querySelector('[data-scope-summary]').textContent = button.disabled
                    ? 'Chọn học kỳ và ngành đào tạo trước.'
                    : 'Phạm vi xuất: ' + (council.value ? council.selectedOptions[0].textContent
                        : round.value ? 'Toàn ngành trong ' + round.value : 'Toàn ngành trong học kỳ đã chọn') + '.';
            }
            function updateCouncils(initial = false) {
                const previous = initial ? form.dataset.selectedCouncil : council.value;
                council.replaceChildren(new Option('Không chọn hội đồng riêng', ''));
                const rows = options.filter(o => o.semester === semester.value && o.major === major.value && (!round.value || o.round === round.value) && o.council);
                const seen = new Set();
                rows.forEach(o => {
                    if (!seen.has(o.council)) council.add(new Option(o.name + ' (' + o.council + ')' + (o.round ? ' · ' + o.round : ''), o.council));
                    seen.add(o.council);
                });
                council.disabled = !semester.value || !major.value;
                council.value = rows.some(o => o.council === previous) ? previous : '';
                updateSummary();
            }
            function update(initial = false) {
                round.replaceChildren(new Option('Tất cả đợt trong học kỳ', ''));
                const rounds = [...new Set(options.filter(o => o.semester === semester.value && o.major === major.value).map(o => o.round).filter(Boolean))];
                rounds.forEach(name => round.add(new Option(name, name)));
                round.disabled = !semester.value || !major.value;
                if (initial && rounds.includes(form.dataset.selectedRound)) round.value = form.dataset.selectedRound;
                council.value = '';
                updateCouncils(initial);
            }
            semester.addEventListener('change', () => update());
            major.addEventListener('change', () => update());
            round.addEventListener('change', () => updateCouncils());
            council.addEventListener('change', updateSummary);
            update(true);
            form.addEventListener('submit', async event => {
                event.preventDefault();
                if (!form.reportValidity() || button.disabled) return;
                const oldLabel = button.textContent;
                button.disabled = true;
                button.textContent = 'Đang xuất báo cáo…';
                const notice = document.createElement('div');
                notice.className = 'col-12';
                notice.setAttribute('role', 'status');
                form.querySelector('[data-export-notice]')?.remove();
                notice.dataset.exportNotice = '';
                form.append(notice);
                try {
                    const url = new URL(form.action, location.href);
                    url.search = new URLSearchParams(new FormData(form)).toString();
                    const response = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!response.ok) {
                        const detail = await response.json().catch(() => ({}));
                        throw new Error(Object.values(detail.errors || {}).flat().join(' ') || detail.message || 'Không xuất được báo cáo. Vui lòng thử lại.');
                    }
                    const blob = await response.blob();
                    if (!/application\/(pdf|vnd\.openxmlformats-officedocument\.spreadsheetml\.sheet)/i.test(blob.type)) throw new Error('Phiên đăng nhập có thể đã hết hạn. Vui lòng tải lại trang.');
                    const disposition = response.headers.get('Content-Disposition') || '';
                    const filename = disposition.match(/filename="?([^";]+)"?/i)?.[1] || ('Bao_cao_CLO.' + (form.elements.format.value === 'pdf' ? 'pdf' : 'xlsx'));
                    const objectUrl = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = objectUrl; link.download = filename;
                    document.body.append(link); link.click(); link.remove();
                    setTimeout(() => URL.revokeObjectURL(objectUrl), 30000);
                    notice.className = 'col-12 text-success small';
                    notice.textContent = 'Đã tạo báo cáo. Tệp được tải về máy.';
                } catch (error) {
                    notice.className = 'col-12 text-danger small';
                    notice.setAttribute('role', 'alert');
                    notice.textContent = error.message;
                } finally {
                    button.textContent = oldLabel;
                    updateSummary();
                }
            });
        }

        async function open(url) {
            controller?.abort();
            controller = new AbortController();
            const signal = controller.signal;
            body.innerHTML = '<div class="text-center py-4" role="status"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Đang tải lựa chọn báo cáo…</div>';
            modal.show();
            url.searchParams.set('popup', '1');
            try {
                const response = await fetch(url, { signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Không tải được lựa chọn báo cáo.');
                const html = await response.text();
                if (signal.aborted) return;
                body.innerHTML = html;
                const form = body.querySelector('[data-clo-round-form]');
                if (!form) throw new Error('Phiên đăng nhập có thể đã hết hạn. Vui lòng tải lại trang.');
                initForm(form);
            } catch (error) {
                if (signal.aborted) return;
                const message = document.createElement('div');
                message.className = 'alert alert-warning mb-0'; message.setAttribute('role', 'alert');
                message.textContent = error.message; body.replaceChildren(message);
            }
        }

        document.addEventListener('click', event => {
            const link = event.target.closest('a[href]');
            if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            const url = new URL(link.href, location.href);
            if (url.origin !== selectionUrl.origin || url.pathname !== selectionUrl.pathname) return;
            event.preventDefault(); event.stopPropagation(); open(url);
        }, true);
        element.addEventListener('hidden.bs.modal', () => controller?.abort());
        // Old bookmarks open the popup on the existing role page, never a separate landing page.
        const current = new URL(location.href);
        if (current.searchParams.get('clo_export') === '1') {
            const url = new URL(selectionUrl);
            ['MaHocKy', 'MaNganh', 'DotBaoVe', 'MaHoiDong', 'loai', 'format'].forEach(key => {
                if (current.searchParams.has(key)) url.searchParams.set(key, current.searchParams.get(key));
            });
            current.searchParams.delete('clo_export');
            history.replaceState(history.state, '', current);
            open(url);
        }

    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
