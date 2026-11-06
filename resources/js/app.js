import '@tabler/core/dist/js/tabler.min.js';
import './register.js';

if (document.getElementById('dashboard-grid') || document.querySelector('[data-widget]')) {
    import('./dashboard/index.js');
}
