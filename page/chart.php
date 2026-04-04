<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$unitAktif = isset($_GET['unit']) ? strtoupper($_GET['unit']) : 'C6KV';

$judul_unit = "";
switch ($unitAktif) {
    case 'C6KV':
        $judul_unit = "PLTU UNIT C 6KV";
        break;
    case 'D6KV':
        $judul_unit = "PLTU UNIT D 6KV";
        break;
    case 'C380':
        $judul_unit = "PLTU UNIT C 380V";
        break;
    case 'D380':
        $judul_unit = "PLTU UNIT D 380V";
        break;
    case 'UTILITY':
        $judul_unit = "PLTU UNIT UTILITY";
        break;
    default:
        $judul_unit = "Unit Tidak Dikenal";
}
?>

<style>
    /* Mengatur posisi checkbox kotak standar agar sejajar di card-tools */
    .card-tools .custom-checkbox {
        vertical-align: middle;
        margin-top: -3px;
    }

    .card-tools .custom-control-label {
        padding-top: 2px;
        font-weight: 600 !important;
        cursor: pointer;
        color: #495057;
        font-size: 0.85rem;
    }

    .card-tools .custom-control-label::before,
    .card-tools .custom-control-label::after {
        top: 0.2rem;
        cursor: pointer;
    }
</style>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="row" id="chart-cards-container">
                <div class="col-12 text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                    <p>Mempersiapkan layout grafik...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. FUNGSI UNTUK DOWNLOAD GRAFIK KE PNG
    function downloadChart(canvasId, namaMotor) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            alert("Grafik belum siap atau tidak ada data!");
            return;
        }

        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height;
        const tempCtx = tempCanvas.getContext('2d');

        tempCtx.fillStyle = '#ffffff';
        tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        tempCtx.drawImage(canvas, 0, 0);

        const safeName = namaMotor.replace(/[^a-zA-Z0-9]/g, '_');
        const fileName = `Trend_Maintenance_${safeName}.png`;

        const link = document.createElement('a');
        link.download = fileName;
        link.href = tempCanvas.toDataURL('image/png');
        link.click();
    }

    document.addEventListener("DOMContentLoaded", function () {
        const unitAktif = "<?php echo $unitAktif; ?>";

        if (typeof window.dataMotor === 'undefined') {
            document.getElementById('chart-cards-container').innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <p>Error: File config.js tidak terbaca atau belum dimuat.</p>
                </div>`;
            return;
        }

        const listPeralatan = window.dataMotor[unitAktif] || [];
        const container = document.getElementById('chart-cards-container');

        if (listPeralatan.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p>Silakan pilih unit yang valid melalui navbar.</p>
                </div>`;
            return;
        }

        // 2. GENERATE HTML KARTU GRAFIK
        let cardsHTML = "";
        listPeralatan.forEach(function (nama, index) {
            cardsHTML += `
            <div class="col-lg-6">
                <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px; overflow: hidden;">
                    <div class="card-header border-0 bg-white pt-2 pb-0">
                        <h3 class="card-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                            <i class="fas fa-chart-area text-primary mr-2"></i> ${nama}
                        </h3>
                        <div class="card-tools">
                            <div class="custom-control custom-checkbox d-inline-block mr-2" title="Tampilkan Kotak Detail saat Hover">
                                <input type="checkbox" class="custom-control-input" id="toggle_tooltip_${index}">
                                <label class="custom-control-label" for="toggle_tooltip_${index}">Tooltip</label>
                            </div>

                            <div class="custom-control custom-checkbox d-inline-block mr-3" title="Tampilkan Angka Langsung di Atas Titik Grafik">
                                <input type="checkbox" class="custom-control-input" id="toggle_datalabels_${index}">
                                <label class="custom-control-label" for="toggle_datalabels_${index}">Angka</label>
                            </div>

                            <button type="button" class="btn btn-tool" title="Download Grafik PNG" onclick="downloadChart('chart_${index}', '${nama}')">
                                <i class="fas fa-download text-info"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Minimize">
                                <i class="fas fa-minus text-muted"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-2 position-relative">
                        <div id="loading_${index}" class="overlay d-none justify-content-center align-items-center" style="background: rgba(255,255,255,0.9); position: absolute; top:0; left:0; right:0; bottom:0; z-index:10; border-radius: 10px;">
                            <div class="text-center">
                                <i class="fas fa-circle-notch fa-spin fa-2x text-primary"></i>
                                <div class="mt-2 text-sm text-muted font-weight-bold">Sinkronisasi Data...</div>
                            </div>
                        </div>
                        <div class="chart mt-2">
                            <canvas id="chart_${index}" style="min-height: 400px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>`;
        });

        container.innerHTML = cardsHTML;

        // 3. FUNGSI FETCH DATA DAN RENDER GRAFIK
        async function fetchAndRenderChart(namaMotor, indexId) {
            const loadingEl = document.getElementById(`loading_${indexId}`);
            if (loadingEl) {
                loadingEl.classList.remove('d-none');
                loadingEl.classList.add('d-flex');
            }

            try {
                const targetUrl = `api/fetch_chart_data.php?unit=${unitAktif}&sheet=${encodeURIComponent(namaMotor)}`;
                const response = await fetch(targetUrl);
                const result = await response.json();
                const canvasEl = document.getElementById(`chart_${indexId}`);

                if (result.status === 'success') {
                    if (result.labels.length === 0) {
                        if (loadingEl) {
                            loadingEl.classList.remove('d-flex');
                            loadingEl.innerHTML = '<div class="text-muted text-center mt-5"><i class="fas fa-folder-open fa-2x mb-2 text-lightblue"></i><br>Belum ada riwayat data direkam.</div>';
                        }
                        return;
                    }

                    if (loadingEl) {
                        loadingEl.classList.remove('d-flex');
                        loadingEl.classList.add('d-none');
                    }

                    if (canvasEl) {
                        const ctx = canvasEl.getContext('2d');
                        const areaChartData = {
                            labels: result.labels,
                            datasets: [
                                { label: 'Vibrasi Bearing DE', borderColor: 'rgba(60,141,188, 1)', backgroundColor: 'rgba(60,141,188, 0.1)', borderWidth: 2.5, pointRadius: 2, data: result.dataDE, fill: false, spanGaps: true },
                                { label: 'Vibrasi Bearing NDE', borderColor: 'rgba(220,53,69, 1)', backgroundColor: 'rgba(220,53,69, 0.1)', borderWidth: 2.5, pointRadius: 2, data: result.dataNDE, fill: false, spanGaps: true },
                                { label: 'Temp Bearing DE', borderColor: 'rgba(255, 133, 27, 1)', borderWidth: 2, pointRadius: 2, data: result.dataTempDE, fill: false, spanGaps: true, hidden: true },
                                { label: 'Temp Bearing NDE', borderColor: 'rgba(255, 193, 7, 1)', borderWidth: 2, pointRadius: 2, data: result.dataTempNDE, fill: false, spanGaps: true, hidden: true },
                                { label: 'Suhu Ruangan', borderColor: 'rgba(40, 167, 69, 1)', borderWidth: 2, pointRadius: 2, data: result.dataSuhu, fill: false, spanGaps: true, hidden: true },
                                { label: 'Beban Generator', borderColor: 'rgba(111, 66, 193, 1)', borderWidth: 2, pointRadius: 2, data: result.dataBeban, fill: false, spanGaps: true, hidden: true },
                                { label: 'Opening Damper', borderColor: 'rgba(32, 201, 151, 1)', borderWidth: 2, pointRadius: 2, data: result.dataDamper, fill: false, spanGaps: true, hidden: true },
                                { label: 'Load Current', borderColor: 'rgba(139, 0, 0, 1)', borderWidth: 2, pointRadius: 2, data: result.dataCurrent, fill: false, spanGaps: true, hidden: true }
                            ]
                        };

                        const lineChartOptions = {
                            maintainAspectRatio: false,
                            responsive: true,
                            layout: { padding: { top: 20, bottom: 10 } },
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    usePointStyle: false,
                                    boxWidth: 10,
                                    padding: 15,
                                    fontColor: '#444',
                                    fontSize: 12,
                                    generateLabels: function (chart) {
                                        const original = Chart.defaults.global.legend.labels.generateLabels;
                                        const labels = original.call(this, chart);
                                        labels.forEach(label => { label.width = 170; });
                                        return labels;
                                    }
                                }
                            },
                            scales: {
                                xAxes: [{
                                    gridLines: { display: true, color: 'rgba(0,0,0,0.05)', drawOnChartArea: true },
                                    ticks: { fontColor: '#888', maxTicksLimit: 15 },
                                    scaleLabel: { display: true, labelString: 'Waktu Pengukuran', fontColor: '#333', fontStyle: 'bold' }
                                }],
                                yAxes: [{
                                    gridLines: { display: true, color: 'rgba(0,0,0,0.1)', borderDash: [] },
                                    ticks: { beginAtZero: true, fontColor: '#888', padding: 10 },
                                    scaleLabel: { display: true, labelString: 'Nilai Parameter', fontColor: '#333', fontStyle: 'bold' }
                                }]
                            },
                            tooltips: {
                                // WAJIB TRUE: Agar sistem Chart.js tetap melacak koordinat untuk fitur Crosshair
                                enabled: true,
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleFontColor: '#333',
                                bodyFontColor: '#555',
                                borderWidth: 0, // KOTAK POLOSAN (tanpa border)
                                cornerRadius: 8,
                                xPadding: 12,
                                yPadding: 12,
                                caretPadding: 20, // JARAK AMAN: Mendorong tooltip menjauh dari kursor agar tidak menghalangi
                                callbacks: {
                                    label: function (tooltipItem, data) {
                                        let label = data.datasets[tooltipItem.datasetIndex].label || '';
                                        let val = tooltipItem.yLabel;
                                        let unit = '';
                                        if (label.includes('Vibrasi')) unit = ' mm/s';
                                        else if (label.includes('Temp') || label.includes('Suhu')) unit = ' °C';
                                        else if (label.includes('Beban')) unit = ' MW';
                                        else if (label.includes('Damper')) unit = ' %';
                                        else if (label.includes('Current')) unit = ' A';
                                        return label + ': ' + val + unit;
                                    }
                                }
                            },
                            hover: { mode: 'index', intersect: false },
                            animation: { duration: 0 } // Matikan animasi agar peralihan checkbox terasa instan
                        };

                        const myChart = new Chart(ctx, {
                            type: 'line',
                            data: areaChartData,
                            options: lineChartOptions,
                            plugins: [{
                                // --- A. PLUGIN SUPER AMAN: KONTROL TAMPILAN TOOLTIP ---
                                beforeTooltipDraw: function (chart) {
                                    const checkBox = document.getElementById(`toggle_tooltip_${indexId}`);
                                    // Jika checkbox MATI, batalkan proses menggambar tooltip (return false)
                                    // Chart.js akan tetap menghitung posisi, tapi kotak hitamnya tidak akan muncul!
                                    if (checkBox && !checkBox.checked) {
                                        return false;
                                    }
                                },

                                // --- B. AMBIL POSISI KURSOR SAAT HOVER ---
                                afterEvent: function (chart, event) {
                                    if (event.type === 'mousemove' || event.type === 'touchstart' || event.type === 'touchmove') {
                                        if (event.y !== undefined) {
                                            chart.customCrosshairY = event.y;
                                        }
                                    } else if (event.type === 'mouseout') {
                                        chart.customCrosshairY = undefined;
                                    }
                                },

                                // --- C. GAMBAR ANGKA DAN GARIS PELACAK ---
                                afterDraw: function (chart) {
                                    if (!chart || !chart.ctx) return;

                                    const ctx = chart.ctx;

                                    // 1. TAMPILKAN ANGKA DI ATAS TITIK
                                    const showDataLabels = document.getElementById(`toggle_datalabels_${indexId}`);
                                    if (showDataLabels && showDataLabels.checked) {
                                        ctx.font = "11px 'Source Sans Pro', Arial, sans-serif";
                                        ctx.textAlign = 'center';
                                        ctx.textBaseline = 'bottom';

                                        chart.data.datasets.forEach(function (dataset, i) {
                                            if (chart.isDatasetVisible(i)) {
                                                const meta = chart.getDatasetMeta(i);
                                                if (meta.data) {
                                                    meta.data.forEach(function (element, index) {
                                                        let dataValue = dataset.data[index];
                                                        if (dataValue !== null && dataValue !== undefined && dataValue !== '' && !isNaN(dataValue)) {
                                                            if (element._model && element._model.x && element._model.y && !element._view.skip) {
                                                                // Pastikan angka hanya digambar di dalam batas area grafik
                                                                if (element._model.x >= chart.chartArea.left && element._model.x <= chart.chartArea.right &&
                                                                    element._model.y >= chart.chartArea.top && element._model.y <= chart.chartArea.bottom) {

                                                                    ctx.fillStyle = dataset.borderColor;
                                                                    ctx.fillText(dataValue, element._model.x, element._model.y - 5);
                                                                }
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    }

                                    // 2. TAMPILKAN GARIS PELACAK (CROSSHAIR)
                                    // Berkat cara di 'beforeTooltipDraw', variabel _active ini dijamin selalu ada meski Tooltip tak terlihat
                                    if (chart.tooltip && chart.tooltip._active && chart.tooltip._active.length && chart.customCrosshairY !== undefined) {
                                        const activePoints = chart.tooltip._active;

                                        if (!activePoints[0] || !activePoints[0]._model) return;

                                        const x = activePoints[0]._model.x;
                                        let closestY = activePoints[0]._model.y;
                                        let minDiff = Infinity;

                                        for (let i = 0; i < activePoints.length; i++) {
                                            if (activePoints[i] && activePoints[i]._model) {
                                                let pointY = activePoints[i]._model.y;
                                                let diff = Math.abs(pointY - chart.customCrosshairY);
                                                if (diff < minDiff) {
                                                    minDiff = diff;
                                                    closestY = pointY;
                                                }
                                            }
                                        }

                                        if (chart.chartArea && closestY >= chart.chartArea.top && closestY <= chart.chartArea.bottom) {
                                            const topY = chart.chartArea.top;
                                            const bottomY = chart.chartArea.bottom;
                                            const leftX = chart.chartArea.left;
                                            const rightX = chart.chartArea.right;

                                            ctx.save();
                                            ctx.beginPath();
                                            ctx.setLineDash([6, 6]);
                                            ctx.lineWidth = 1.5;
                                            ctx.strokeStyle = 'rgba(0, 0, 0, 0.4)';

                                            // Garis Vertikal
                                            ctx.moveTo(x, topY);
                                            ctx.lineTo(x, bottomY);

                                            // Garis Horizontal
                                            ctx.moveTo(leftX, closestY);
                                            ctx.lineTo(rightX, closestY);

                                            ctx.stroke();
                                            ctx.restore();
                                        }
                                    }
                                }
                            }]
                        });

                        // --- 4. EVENT LISTENER SEDERHANA UNTUK KEDUA CHECKBOX ---

                        const tooltipCb = document.getElementById(`toggle_tooltip_${indexId}`);
                        if (tooltipCb) {
                            tooltipCb.addEventListener('change', function () {
                                myChart.update(0); // Memaksa grafik dirender ulang
                            });
                        }

                        const datalabelsCb = document.getElementById(`toggle_datalabels_${indexId}`);
                        if (datalabelsCb) {
                            datalabelsCb.addEventListener('change', function () {
                                myChart.update(0); // Memaksa grafik dirender ulang
                            });
                        }
                    }
                } else {
                    if (loadingEl) {
                        loadingEl.classList.remove('d-flex');
                        loadingEl.innerHTML = `<div class="text-danger text-sm text-center px-3 mt-4"><i class="fas fa-times-circle mr-1"></i>${result.message}</div>`;
                    }
                }
            } catch (error) {
                console.error(`Error fetch ${namaMotor}:`, error);
                const loadingEl = document.getElementById(`loading_${indexId}`);
                if (loadingEl) {
                    loadingEl.classList.remove('d-flex');
                    loadingEl.innerHTML = '<div class="text-danger mt-4"><i class="fas fa-wifi mr-1"></i>Gagal koneksi ke server API.</div>';
                }
            }
        }

        // 5. JALANKAN FETCH UNTUK SEMUA MOTOR
        listPeralatan.forEach(function (namaMotor, index) {
            fetchAndRenderChart(namaMotor, index);
        });
    });
</script>