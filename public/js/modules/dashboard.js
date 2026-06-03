(function () {
    function updateClock() {
        const clock = document.querySelector('[data-dashboard-clock]');

        if (!clock) {
            return;
        }

        clock.textContent = new Intl.DateTimeFormat('es-PE', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        }).format(new Date());
    }

    function getChartData() {
        const source = document.getElementById('dashboard-chart-data');

        if (!source) {
            return null;
        }

        try {
            return JSON.parse(source.textContent);
        } catch (error) {
            console.error('No se pudo leer la data del dashboard.', error);
            return null;
        }
    }

    function makeActivityChart(payload) {
        const canvas = document.getElementById('dashboardActivityChart');

        if (!canvas || !payload) {
            return;
        }

        const context = canvas.getContext('2d');
        const gradient = context.createLinearGradient(0, 0, 0, canvas.height || 360);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.92)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.38)');

        new Chart(context, {
            data: {
                labels: payload.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Citas',
                        data: payload.citas,
                        backgroundColor: gradient,
                        borderRadius: 10,
                        borderSkipped: false,
                        maxBarThickness: 28,
                    },
                    {
                        type: 'line',
                        label: 'Atenciones',
                        data: payload.atenciones,
                        borderColor: '#0f766e',
                        backgroundColor: 'rgba(15, 118, 110, 0.10)',
                        fill: true,
                        tension: 0.34,
                        pointRadius: 3,
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#0f766e',
                        pointBorderWidth: 0,
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 14,
                            color: '#334155',
                            font: {
                                size: 12,
                                weight: '600',
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 11,
                                weight: '600',
                            },
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.14)',
                        },
                        ticks: {
                            precision: 0,
                            color: '#64748b',
                            font: {
                                size: 11,
                            },
                        },
                    },
                },
            },
        });
    }

    function makeDoughnutChart(canvasId, payload, colors) {
        const canvas = document.getElementById(canvasId);

        if (!canvas || !payload) {
            return;
        }

        new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: payload.labels,
                datasets: [
                    {
                        data: payload.values,
                        backgroundColor: colors,
                        borderWidth: 0,
                        hoverOffset: 3,
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false,
                    },
                },
            },
        });
    }

    function makeVaccinesChart(payload) {
        const canvas = document.getElementById('dashboardVaccinesChart');

        if (!canvas || !payload) {
            return;
        }

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: payload.labels,
                datasets: [
                    {
                        label: 'Pacientes',
                        data: payload.values,
                        backgroundColor: ['#d97706', '#e11d48', '#64748b'],
                        borderRadius: 10,
                        borderSkipped: false,
                        maxBarThickness: 22,
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.14)',
                        },
                        ticks: {
                            precision: 0,
                            color: '#64748b',
                            font: {
                                size: 11,
                            },
                        },
                    },
                    y: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#334155',
                            font: {
                                size: 12,
                                weight: '600',
                            },
                        },
                    },
                },
            },
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateClock();
        window.setInterval(updateClock, 1000);

        if (!window.Chart) {
            return;
        }

        Chart.defaults.font.family = 'Figtree, sans-serif';
        Chart.defaults.color = '#64748b';

        const payload = getChartData();

        if (!payload) {
            return;
        }

        makeActivityChart(payload.actividad);
        makeDoughnutChart('dashboardStatusChart', payload.estados, ['#3b82f6', '#10b981', '#f43f5e']);
        makeDoughnutChart('dashboardOriginsChart', payload.origenes, ['#2563eb', '#10b981', '#f59e0b']);
        makeDoughnutChart('dashboardSpeciesChart', payload.especies, ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6']);
        makeVaccinesChart(payload.vacunas);
    });
})();
