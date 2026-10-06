// Gráficos del administrador: datos del servidor, colores compartidos y movimiento accesible.
let disposePanel = () => {};

function iniciarPanel() {
    disposePanel();
    const panel = document.querySelector('[data-admin-dashboard]');
    if (!panel || !window.Chart) return;
    const source = panel.querySelector('[data-admin-chart-config]');
    if (!source) return;
    const definitions = JSON.parse(source.textContent);
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const charts = new Map();
    const token = name => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    const number = new Intl.NumberFormat('es-BO');
    const palette = () => ['--ui-primary', '--ui-info', '--ui-violet', '--ui-warning', '--ui-danger', '--ui-muted'].map(token);
    const applyColors = (chart, definition) => {
        chart.data.datasets[0].backgroundColor = definition.tipo === 'doughnut' ? palette() : token(definition.color);
        chart.data.datasets[0].borderColor = definition.tipo === 'doughnut' ? token('--ui-surface') : token(definition.color);
    };

    const create = definition => {
        const canvas = document.getElementById(definition.id);
        if (!canvas || charts.has(definition.id)) return;
        const initialData = definition.vistas?.[0]?.datos ?? definition.datos;
        const labels = Object.keys(initialData);
        const values = Object.values(initialData).map(Number);
        const doughnut = definition.tipo === 'doughnut';
        if (definition.vistas?.[0]) canvas.setAttribute('aria-label', `${definition.titulo}. ${definition.vistas[0].etiqueta}. Los valores están disponibles debajo del gráfico.`);
        const chart = new window.Chart(canvas, {
            type: definition.tipo,
            data: {
                labels,
                datasets: [{
                    label: definition.unidad,
                    data: values,
                    backgroundColor: doughnut ? palette() : token(definition.color),
                    borderColor: doughnut ? token('--ui-surface') : token(definition.color),
                    borderWidth: doughnut ? 3 : 0,
                    borderRadius: doughnut ? 5 : 6,
                    hoverOffset: motion.matches ? 0 : 6,
                    maxBarThickness: 28,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: definition.horizontal ? 'y' : 'x',
                animation: motion.matches ? false : {
                    duration: doughnut ? 950 : 750,
                    easing: 'easeOutQuart',
                    animateRotate: true,
                    animateScale: false,
                    delay: context => context.type === 'data' && context.mode === 'default' ? context.dataIndex * 65 : 0,
                },
                transitions: { resize: { animation: { duration: 0 } }, active: { animation: { duration: motion.matches ? 0 : 180 } } },
                cutout: '72%',
                layout: { padding: 8 },
                plugins: {
                    legend: {
                        display: doughnut,
                        position: 'bottom',
                        labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, boxHeight: 8, padding: 16, font: { family: 'Figtree', size: 12 } },
                    },
                    tooltip: {
                        padding: 12,
                        callbacks: { label: context => ` ${doughnut ? `${context.label}: ` : ''}${number.format(context.raw)} ${definition.unidad}` },
                    },
                },
                ...(doughnut ? {} : {
                    scales: {
                        x: {
                            beginAtZero: true,
                            ...(definition.horizontal ? { grace: '20%' } : {}),
                            grid: { display: Boolean(definition.horizontal) },
                            border: { display: false },
                            ticks: {
                                precision: 0,
                                maxRotation: 0,
                                autoSkip: false,
                                font: { family: 'Figtree', size: 12 },
                                ...(definition.horizontal ? {} : {
                                    callback: function (_value, index) { return this.chart.data.labels[index]?.replace(' de Secundaria', ' Sec.'); },
                                }),
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ...(!definition.horizontal ? { grace: '15%' } : {}),
                            grid: { display: !definition.horizontal },
                            border: { display: false },
                            ticks: {
                                precision: 0,
                                font: { family: 'Figtree', size: 12 },
                                ...(definition.horizontal ? {
                                    callback: function (_value, index) {
                                        const label = this.chart.data.labels[index];
                                        return label?.length > 21 ? `${label.slice(0, 20)}…` : label;
                                    },
                                } : {}),
                            },
                        },
                    },
                }),
            },
            plugins: doughnut ? [{
                id: 'totalInstitucional',
                afterDraw(chart) {
                    const { ctx, chartArea } = chart;
                    if (!chartArea) return;
                    const x = (chartArea.left + chartArea.right) / 2;
                    const y = (chartArea.top + chartArea.bottom) / 2;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = token('--ui-text');
                    ctx.font = '800 26px Figtree, sans-serif';
                    ctx.fillText(number.format(values.reduce((sum, value, index) => sum + (chart.getDataVisibility(index) ? value : 0), 0)), x, y - 8);
                    ctx.fillStyle = token('--ui-muted');
                    ctx.font = '12px Figtree, sans-serif';
                    ctx.fillText('asignaciones', x, y + 15);
                    ctx.restore();
                },
            }] : [{
                id: 'cantidadesVisibles',
                afterDatasetsDraw(chart) {
                    const { ctx, chartArea } = chart;
                    ctx.save();
                    ctx.font = '600 12px Figtree, sans-serif';
                    ctx.fillStyle = token('--ui-text');
                    ctx.textBaseline = 'middle';
                    chart.getDatasetMeta(0).data.forEach((bar, index) => {
                        const label = number.format(chart.data.datasets[0].data[index]);
                        ctx.textAlign = definition.horizontal ? 'left' : 'center';
                        const x = definition.horizontal ? Math.min(bar.x + 6, chartArea.right - ctx.measureText(label).width) : bar.x;
                        const y = definition.horizontal ? bar.y : Math.max(chartArea.top + 8, bar.y - 10);
                        ctx.fillText(label, x, y);
                    });
                    ctx.restore();
                },
            }],
        });
        charts.set(definition.id, chart);
    };

    // Cada gráfico se presenta una vez cuando entra en pantalla; el tema no reinicia la animación.
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const definition = definitions.find(item => item.id === entry.target.id);
        if (definition) create(definition);
        observer.unobserve(entry.target);
    }), { threshold: 0.15 });
    definitions.forEach(definition => {
        const canvas = document.getElementById(definition.id);
        if (canvas) observer.observe(canvas);
    });
    const replay = () => definitions.forEach(definition => {
        create(definition);
        const chart = charts.get(definition.id);
        if (!chart) return;
        chart.stop();
        if (!motion.matches) chart.reset();
        chart.update(motion.matches ? 'none' : 'default');
    });
    const updateTheme = () => definitions.forEach(definition => {
        const chart = charts.get(definition.id);
        if (!chart) return;
        applyColors(chart, definition);
        chart.update('none');
    });
    const updateMotion = () => charts.forEach(chart => {
        chart.stop();
        chart.options.animation = motion.matches ? false : { duration: 750, easing: 'easeOutQuart' };
        chart.options.transitions.active.animation.duration = motion.matches ? 0 : 180;
        chart.data.datasets[0].hoverOffset = motion.matches ? 0 : 6;
        chart.update('none');
    });
    const replayButton = panel.querySelector('[data-replay-charts]');
    const viewButtons = [...panel.querySelectorAll('[data-chart-view]')];
    const changeView = event => {
        const button = event.currentTarget;
        const definition = definitions.find(item => item.id === button.dataset.chartView);
        const index = Number(button.dataset.viewIndex);
        const view = definition?.vistas?.[index];
        if (!view || button.getAttribute('aria-pressed') === 'true') return;
        create(definition);
        const chart = charts.get(definition.id);
        if (!chart) return;
        chart.stop();
        chart.data.labels = Object.keys(view.datos);
        chart.data.datasets[0].data = Object.values(view.datos).map(Number);
        chart.update(motion.matches ? 'none' : 'default');
        chart.canvas.setAttribute('aria-label', `${definition.titulo}. ${view.etiqueta}. Los valores están disponibles debajo del gráfico.`);
        viewButtons.filter(item => item.dataset.chartView === definition.id).forEach(item => {
            item.setAttribute('aria-pressed', String(item === button));
        });
        panel.querySelectorAll('[data-chart-values]').forEach(list => {
            if (list.dataset.chartValues === definition.id) list.hidden = Number(list.dataset.viewIndex) !== index;
        });
    };
    viewButtons.forEach(button => button.addEventListener('click', changeView));
    replayButton?.addEventListener('click', replay);
    window.addEventListener('theme-changed', updateTheme);
    motion.addEventListener('change', updateMotion);
    disposePanel = () => {
        observer.disconnect();
        charts.forEach(chart => chart.destroy());
        viewButtons.forEach(button => button.removeEventListener('click', changeView));
        replayButton?.removeEventListener('click', replay);
        window.removeEventListener('theme-changed', updateTheme);
        motion.removeEventListener('change', updateMotion);
    };
}

document.addEventListener('DOMContentLoaded', iniciarPanel);
document.addEventListener('livewire:navigated', iniciarPanel);
