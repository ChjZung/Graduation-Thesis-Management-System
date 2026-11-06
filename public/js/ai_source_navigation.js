/* Local report navigation. Requests are made only by the existing citation viewer. */
(() => {
    'use strict';

    const aliases = [
        ['dang nhap', 'xac thuc tai khoan', 'login', 'sign in'],
        ['dang xuat', 'logout', 'sign out'],
        ['ngan hang cau hoi', 'kho cau hoi', 'question bank'],
        ['phan quyen', 'kiem soat truy cap', 'access control'],
        ['quen mat khau', 'dat lai mat khau', 'khoi phuc mat khau', 'password reset'],
        ['bao cao tien do', 'bao cao dinh ky', 'progress report'],
    ];

    function normalize(value) {
        return String(value ?? '').toLowerCase().normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd')
            .replace(/[^a-z0-9]+/g, ' ').trim();
    }

    const contains = (text, phrase) => Boolean(phrase) && (' ' + text + ' ').includes(' ' + phrase + ' ');

    function phrases(query) {
        const variants = new Set([query]);
        for (const group of aliases) {
            for (const alias of group) {
                if (!contains(query, alias)) continue;
                for (const equivalent of group) {
                    variants.add((' ' + query + ' ').replace(' ' + alias + ' ', ' ' + equivalent + ' ').trim());
                }
            }
        }
        return [...variants];
    }

    function rank(rows, query, type = '', assets = false) {
        const variants = phrases(query);
        return rows.flatMap(row => {
            if (type && row.type !== type) return [];
            const quote = normalize(row.quote);
            const context = assets ? quote : normalize(row.quote + ' ' + (row.section || ''));
            const score = Math.max(...variants.map((phrase, i) =>
                contains(quote, phrase) ? (i ? 4 : 6) :
                contains(context, phrase) ? (i ? 3 : 5) :
                (!assets && phrase.split(' ').every(word => contains(context, word))) ? (i ? 1 : 2) : 0));
            return score ? [{row, score}] : [];
        }).sort((a, b) => b.score - a.score).map(result => result.row);
    }

    function captionKeys(text) {
        return [...String(text ?? '').matchAll(/\b(hinh|bang|figure|table)\s+(\d+(?:[.-]\d+)*)(?![\w]|[.-]\d)/g)]
            .map(([, kind, number]) => (['hinh', 'figure'].includes(kind) ? 'figure:' : 'table:') + number.replace(/-/g, '.'));
    }

    function references(rows, assets) {
        const counts = new Map();
        const identified = assets.flatMap(asset => {
            if (asset.basis === 'table') return [];
            const key = captionKeys(foldCaption(asset.caption_label || asset.quote))[0];
            if (!key) return [];
            counts.set(key, (counts.get(key) || 0) + 1);
            return [{asset, key}];
        });
        return identified.flatMap(({asset, key}) => {
            if (counts.get(key) !== 1) return [];
            const row = rows.find(row => row.quote !== asset.quote &&
                !foldCaption(asset.quote).startsWith(foldCaption(row.quote)) &&
                captionKeys(foldCaption(row.quote)).includes(key));
            return row ? [{asset, row}] : [];
        });
    }

    function foldCaption(text) {
        return String(text ?? '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd');
    }

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = {normalize, rank, references};
        return;
    }

    const parsed = new WeakMap();
    function indexFor(section) {
        const template = section.querySelector('[data-source-index]');
        if (!parsed.has(template)) parsed.set(template, JSON.parse(template.content.textContent));
        return parsed.get(template);
    }

    function appendSource(output, row, canOpen) {
        const line = document.createElement('p');
        line.className = 'mb-2';
        line.textContent = [row.type, row.section, row.label, row.quote].filter(Boolean).join(' · ') + ' ';
        if (canOpen) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'ai-tag ai-citation-button';
            button.textContent = 'Mở nguồn';
            button.dataset.aiAction = 'citation';
            button.dataset.citationPage = row.page;
            button.dataset.citationQuote = row.quote;
            line.append(button);
        }
        output.append(line);
    }

    function render(section) {
        const output = section.querySelector('[data-source-results]');
        const query = normalize(section.querySelector('[data-source-search]').value).slice(0, 150).trim();
        output.replaceChildren();
        if (query.length < 3) return;
        const index = indexFor(section);
        const canOpen = section.dataset.sourceCanOpen === '1';
        const selected = section.querySelector('[data-source-kind]').value;
        const rows = rank(index.rows || [], query, selected);
        const assets = rank(index.assets || [], query, '', true);
        const linked = references(rows.slice(0, 12), index.assets || []);
        if (!rows.length && !assets.length && !linked.length) {
            output.textContent = 'Chưa tìm thấy kết quả trong phần đã lập chỉ mục.';
        }
        rows.slice(0, 12).forEach(row => appendSource(output, row, canOpen));
        const hits = new Map(assets.map(asset => [asset, null]));
        linked.forEach(({asset, row}) => hits.set(asset, row));
        if (hits.size) {
            const heading = document.createElement('strong');
            heading.textContent = 'Sơ đồ và bảng liên quan theo văn bản';
            output.append(heading);
            for (const [asset, row] of [...hits].slice(0, 6)) {
                appendSource(output, asset, canOpen);
                if (row) {
                    const basis = document.createElement('small');
                    basis.textContent = 'Đoạn mô tả có nhắc tới chú thích này: ';
                    output.append(basis);
                    appendSource(output, row, canOpen);
                }
            }
        }
    }

    document.addEventListener('input', event => {
        const input = event.target.closest('[data-source-search]');
        if (input) render(input.closest('[data-source-navigation]'));
    });
    document.addEventListener('change', event => {
        const select = event.target.closest('[data-source-kind], [data-source-filter]');
        if (!select) return;
        const section = select.closest('[data-source-navigation]');
        if (select.matches('[data-source-kind]')) {
            render(section);
        } else {
            for (const row of section.querySelectorAll('[data-source-asset]')) {
                row.hidden = !!select.value && row.dataset.sourceAsset !== select.value;
            }
        }
    });
})();
