const formatNumber = new Intl.NumberFormat('es-PE');
const formatCurrency = new Intl.NumberFormat('es-PE', {
    style: 'currency',
    currency: 'PEN',
});

function getChartData(root) {
    try {
        return JSON.parse(root.dataset.reportCharts || '{}');
    } catch {
        return {};
    }
}

function resizeCanvas(canvas) {
    const ratio = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = Math.max(1, Math.floor(rect.width * ratio));
    canvas.height = Math.max(1, Math.floor(rect.height * ratio));
    const context = canvas.getContext('2d');
    context.setTransform(ratio, 0, 0, ratio, 0, 0);
    return { context, width: rect.width, height: rect.height };
}

function roundedRect(ctx, x, y, width, height, radius) {
    const safeRadius = Math.min(radius, width / 2, height / 2);
    ctx.beginPath();
    ctx.moveTo(x + safeRadius, y);
    ctx.arcTo(x + width, y, x + width, y + height, safeRadius);
    ctx.arcTo(x + width, y + height, x, y + height, safeRadius);
    ctx.arcTo(x, y + height, x, y, safeRadius);
    ctx.arcTo(x, y, x + width, y, safeRadius);
    ctx.closePath();
}

function drawOverviewChart(root) {
    const data = getChartData(root);
    const canvas = root.querySelector('[data-report-overview-canvas]');
    const tooltip = root.querySelector('[data-report-canvas-tooltip]');

    if (!canvas) {
        return;
    }

    const labels = data.labels || [];
    const atenciones = (data.atenciones || []).map((value) => Number(value) || 0);
    const citas = (data.citas || []).map((value) => Number(value) || 0);
    const { context: ctx, width, height } = resizeCanvas(canvas);
    const pad = { top: 28, right: 22, bottom: 42, left: 46 };
    const chartW = Math.max(1, width - pad.left - pad.right);
    const chartH = Math.max(1, height - pad.top - pad.bottom);
    const maxValue = Math.max(1, ...atenciones, ...citas);
    const count = Math.max(labels.length, 1);
    const step = chartW / count;
    const pointX = (index) => pad.left + step * index + step / 2;
    const pointY = (value) => pad.top + chartH - (value / maxValue) * chartH;

    ctx.clearRect(0, 0, width, height);

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 1;
    ctx.font = '700 11px sans-serif';
    ctx.fillStyle = '#94a3b8';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';

    for (let i = 0; i <= 4; i += 1) {
        const y = pad.top + (chartH / 4) * i;
        const value = Math.round(maxValue - (maxValue / 4) * i);
        ctx.beginPath();
        ctx.moveTo(pad.left, y);
        ctx.lineTo(width - pad.right, y);
        ctx.stroke();
        ctx.fillText(formatNumber.format(value), pad.left - 10, y);
    }

    const barWidth = Math.min(28, step * 0.42);
    citas.forEach((value, index) => {
        const x = pointX(index) - barWidth / 2;
        const y = pointY(value);
        const barHeight = pad.top + chartH - y;
        const gradient = ctx.createLinearGradient(0, y, 0, pad.top + chartH);
        gradient.addColorStop(0, '#2dd4bf');
        gradient.addColorStop(1, '#ccfbf1');
        roundedRect(ctx, x, y, barWidth, Math.max(5, barHeight), 8);
        ctx.fillStyle = gradient;
        ctx.fill();
    });

    const points = atenciones.map((value, index) => ({ x: pointX(index), y: pointY(value), value }));

    if (points.length) {
        const areaGradient = ctx.createLinearGradient(0, pad.top, 0, pad.top + chartH);
        areaGradient.addColorStop(0, 'rgba(37, 99, 235, 0.20)');
        areaGradient.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

        ctx.beginPath();
        ctx.moveTo(points[0].x, pad.top + chartH);
        points.forEach((point) => ctx.lineTo(point.x, point.y));
        ctx.lineTo(points[points.length - 1].x, pad.top + chartH);
        ctx.closePath();
        ctx.fillStyle = areaGradient;
        ctx.fill();

        ctx.beginPath();
        points.forEach((point, index) => {
            if (index === 0) {
                ctx.moveTo(point.x, point.y);
            } else {
                const previous = points[index - 1];
                const midX = (previous.x + point.x) / 2;
                ctx.bezierCurveTo(midX, previous.y, midX, point.y, point.x, point.y);
            }
        });
        ctx.strokeStyle = '#2563eb';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.stroke();

        points.forEach((point) => {
            ctx.beginPath();
            ctx.arc(point.x, point.y, 4, 0, Math.PI * 2);
            ctx.fillStyle = '#ffffff';
            ctx.fill();
            ctx.lineWidth = 2.5;
            ctx.strokeStyle = '#2563eb';
            ctx.stroke();
        });
    }

    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    ctx.font = '800 11px sans-serif';
    ctx.fillStyle = '#64748b';
    labels.forEach((label, index) => {
        ctx.fillText(label, pointX(index), pad.top + chartH + 15);
    });

    const showTooltip = (index, clientX, clientY) => {
        if (!tooltip || !labels[index]) {
            return;
        }

        tooltip.innerHTML = `
            <span class="block text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">${labels[index]}</span>
            <span class="mt-1 block text-sm font-black text-blue-700">${formatNumber.format(atenciones[index] || 0)} atenciones</span>
            <span class="block text-sm font-black text-teal-700">${formatNumber.format(citas[index] || 0)} citas</span>
        `;
        tooltip.style.left = `${clientX}px`;
        tooltip.style.top = `${clientY}px`;
        tooltip.dataset.visible = 'true';
    };

    canvas.onmousemove = (event) => {
        const rect = canvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const index = Math.max(0, Math.min(labels.length - 1, Math.floor((x - pad.left) / step)));
        showTooltip(index, x, event.clientY - rect.top);
    };

    canvas.onmouseleave = () => {
        if (tooltip) {
            tooltip.dataset.visible = 'false';
        }
    };
}

function initServices(root) {
    const rows = root.querySelectorAll('[data-service-row]');
    const detail = root.querySelector('[data-service-detail]');

    const selectRow = (row) => {
        rows.forEach((item) => item.dataset.active = item === row ? 'true' : 'false');

        if (detail) {
            detail.innerHTML = `
                <span class="block text-[10px] font-black uppercase tracking-[0.16em] text-emerald-700">Servicio seleccionado</span>
                <span class="mt-2 block text-2xl font-black text-slate-950">${row.dataset.label}</span>
                <span class="mt-2 block text-sm font-bold leading-6 text-slate-500">${formatCurrency.format(Number(row.dataset.value || 0))} generados en el periodo.</span>
            `;
        }
    };

    rows.forEach((row, index) => {
        row.addEventListener('click', () => selectRow(row));
        row.addEventListener('mouseenter', () => selectRow(row));

        if (index === 0) {
            selectRow(row);
        }
    });
}

function initSpecies(root) {
    const scope = root.querySelector('[data-species-total]') || root;
    const rows = scope.querySelectorAll('[data-species-row]');
    const total = Number(scope.dataset.speciesTotal || root.dataset.speciesTotal || 0);
    const centerLabel = scope.querySelector('[data-species-center-label]');
    const centerValue = scope.querySelector('[data-species-center-value]');

    const selectRow = (row) => {
        rows.forEach((item) => item.dataset.active = item === row ? 'true' : 'false');

        if (centerLabel && centerValue) {
            const value = Number(row.dataset.value || 0);
            const percent = total > 0 ? Math.round((value / total) * 100) : 0;
            centerValue.textContent = `${percent}%`;
            centerLabel.textContent = row.dataset.label || 'Especie';
        }
    };

    rows.forEach((row, index) => {
        row.addEventListener('click', () => selectRow(row));
        row.addEventListener('mouseenter', () => selectRow(row));

        if (index === 0) {
            selectRow(row);
        }
    });
}

export function initReportCharts() {
    document.querySelectorAll('[data-report-charts]').forEach((root) => {
        drawOverviewChart(root);
        initServices(root);
        initSpecies(root);
    });

    window.addEventListener('resize', () => {
        document.querySelectorAll('[data-report-charts]').forEach((root) => drawOverviewChart(root));
    });
}
