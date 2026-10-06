// Chart.js queda fuera del proxy de Alpine y se libera al retirar el componente.
window.graficoResultados = datos => {
    let grafico;
    const movimiento = window.matchMedia?.('(prefers-reduced-motion: reduce)');
    const token = nombre => getComputedStyle(document.documentElement).getPropertyValue(`--ui-${nombre}`).trim();
    const tipo = datos.tipo === 'radar' && datos.labels.length < 3 ? 'bar' : (datos.tipo || 'bar');
    const horizontal = datos.horizontal || (datos.tipo === 'radar' && tipo === 'bar');
    const colores = serie => (serie.tokens ? serie.tokens.map(token) : token(serie.token));
    const colorear = () => {
        grafico.data.datasets.forEach((serie, indice) => {
            const origen = datos.series[indice];
            serie.backgroundColor = tipo === 'radar' ? token(`${origen.token}-soft`) : colores(origen);
            serie.borderColor = colores(origen);
        });
        if (tipo === 'radar') {
            const radial = grafico.options.scales.r;
            radial.pointLabels.color = token('text');
            radial.angleLines.color = token('border');
            radial.ticks.backdropColor = token('surface');
        }
        grafico.update('none');
    };
    const ajustarMovimiento = () => {
        grafico.options.animation = movimiento?.matches ? false : {duration: 800, easing: 'easeOutQuart'};
        if (movimiento?.matches) grafico.stop();
    };
    const centro = {
        id: 'resumenSeguimiento',
        afterDraw(chart) {
            if (tipo !== 'doughnut' || !chart.chartArea) return;
            const {ctx, chartArea: area} = chart;
            const x = (area.left + area.right) / 2;
            const y = (area.top + area.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.fillStyle = token('text');
            ctx.font = '800 30px Figtree, sans-serif';
            ctx.fillText(String(datos.series[0].data.reduce((suma, valor) => suma + (valor || 0), 0)), x, y);
            ctx.font = '13px Figtree, sans-serif';
            ctx.fillStyle = token('text-soft');
            ctx.fillText('estudiantes', x, y + 24);
            ctx.restore();
        },
    };
    return {
        fallo: false,
        init() {
            this.$nextTick(() => {
                if (!this.$refs.canvas.isConnected) return;
                try {
                    const escalas = tipo === 'doughnut' ? {} : tipo === 'radar' ? {
                        r: {min: 0, max: datos.maximo, ticks: {stepSize: 25, color: token('text-soft'), backdropColor: token('surface')},
                            grid: {color: token('border')}, angleLines: {color: token('border')}, pointLabels: {color: token('text'), font: {size: 12}}},
                    } : {
                        x: horizontal
                            ? {min: 0, max: datos.maximo, stacked: !!datos.apilado, ticks: {}, grid: {}, title: {display: true, text: datos.unidad}}
                            : {stacked: !!datos.apilado, ticks: {autoSkip: false, maxRotation: 35}, grid: {display: false}},
                        y: horizontal
                            ? {stacked: !!datos.apilado, ticks: {}, grid: {display: false}}
                            : {min: 0, max: datos.maximo, stacked: !!datos.apilado, ticks: {}, grid: {}, title: {display: true, text: datos.unidad}},
                    };
                    grafico = new window.Chart(this.$refs.canvas, {
                        type: tipo,
                        plugins: [centro],
                        data: {
                            labels: datos.labels,
                            datasets: datos.series.map((serie, indice) => ({
                                label: serie.label, data: serie.data,
                                backgroundColor: tipo === 'radar' ? token(`${serie.token}-soft`) : colores(serie),
                                borderColor: colores(serie),
                                borderWidth: tipo === 'doughnut' ? 0 : 3,
                                borderRadius: tipo === 'doughnut' ? 5 : 4,
                                spacing: tipo === 'doughnut' ? 3 : 0,
                                maxBarThickness: 28, pointRadius: 4, pointHoverRadius: 6,
                                pointStyle: ['circle', 'rect', 'triangle'][indice % 3],
                                borderDash: tipo === 'line' && indice ? [6, 3] : [],
                                spanGaps: false, tension: 0, fill: tipo === 'radar',
                            })),
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            animation: movimiento?.matches ? false : {duration: 800, easing: 'easeOutQuart'},
                            cutout: '72%', indexAxis: horizontal ? 'y' : 'x',
                            interaction: {mode: tipo === 'doughnut' ? 'nearest' : 'index', intersect: tipo === 'doughnut'},
                            plugins: {
                                legend: {position: 'bottom', labels: {usePointStyle: true, padding: 18}},
                                tooltip: {callbacks: {label: contexto => `${tipo === 'doughnut' ? contexto.label : contexto.dataset.label}: ${contexto.raw === null ? 'Sin datos' : contexto.raw + ' ' + datos.unidad}`}},
                            },
                            scales: escalas,
                        },
                    });
                    window.addEventListener('theme-changed', colorear);
                    movimiento?.addEventListener('change', ajustarMovimiento);
                } catch {
                    this.fallo = true;
                    grafico?.destroy();
                }
            });
        },
        destroy() {
            window.removeEventListener('theme-changed', colorear);
            movimiento?.removeEventListener('change', ajustarMovimiento);
            grafico?.destroy();
        },
    };
};
