import '@tabler/core/dist/js/tabler.min.js';

if (document.getElementById('dashboard-grid') || document.querySelector('[data-widget]')) {
    import('./dashboard/index.js');
}
