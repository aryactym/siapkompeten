// Dashboard widget loader (DRY: satu modul untuk semua widget).
const ENDPOINT = (key) => `/dashboard/widgets/${encodeURIComponent(key)}`;

function renderWidget(container, payload) {
    const body = container.querySelector('.widget-body');
    const empty = container.querySelector('.widget-empty');
    const error = container.querySelector('.widget-error');
    const skeleton = container.querySelector('.widget-skeleton');

    skeleton?.classList.add('d-none');

    if (payload.empty) {
        empty?.classList.remove('d-none');
        body?.classList.add('d-none');
        return;
    }

    empty?.classList.add('d-none');
    error?.classList.add('d-none');
    body?.classList.remove('d-none');

    // Render minimal: tampilkan meta total + categories/series ringkas.
    const lines = [];
    if (payload.meta && payload.meta.total !== undefined) {
        lines.push(`<div class="fw-bold fs-2">${new Intl.NumberFormat('id-ID').format(payload.meta.total)}</div>`);
    }
    if (Array.isArray(payload.series)) {
        payload.series.forEach((s) => {
            lines.push(`<div class="small text-secondary">${s.name}: ${(s.data || []).reduce((a, b) => a + (b || 0), 0)}</div>`);
        });
    }
    if (body) body.innerHTML = lines.join('') || '<span class="text-secondary small">OK</span>';
}

async function loadWidget(container) {
    const key = container.getAttribute('data-widget');
    const skeleton = container.querySelector('.widget-skeleton');
    const error = container.querySelector('.widget-error');
    const body = container.querySelector('.widget-body');

    skeleton?.classList.remove('d-none');
    error?.classList.add('d-none');
    body?.classList.add('d-none');

    try {
        const res = await fetch(ENDPOINT(key), { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const payload = await res.json();
        renderWidget(container, payload);
    } catch (e) {
        skeleton?.classList.add('d-none');
        error?.classList.remove('d-none');
        const btn = error?.querySelector('.widget-retry');
        if (btn) btn.onclick = () => loadWidget(container);
    }
}

const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            const el = entry.target;
            if (!el.dataset.loaded) {
                el.dataset.loaded = '1';
                loadWidget(el);
            }
            observer.unobserve(el);
        }
    });
}, { rootMargin: '100px' });

document.querySelectorAll('[data-widget]').forEach((el) => observer.observe(el));
