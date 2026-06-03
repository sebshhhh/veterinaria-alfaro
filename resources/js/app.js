import './bootstrap';

import Alpine from 'alpinejs';
import { initWorkspaceAlertCenter } from './core/alert-center';
import { mountWorkspaceModals } from './core/modal-portal';
import { initReportCharts } from './core/report-charts';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    mountWorkspaceModals();
    initWorkspaceAlertCenter();
    initReportCharts();
});
