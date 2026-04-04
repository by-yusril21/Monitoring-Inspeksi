<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$unitAktif = isset($_GET['unit']) ? strtoupper($_GET['unit']) : 'C6KV';

$judul_unit = "";
switch ($unitAktif) {
    case 'C6KV':
        $judul_unit = "PLTU UNIT C MOTOR 6kV"; // Format diperbarui
        break;
    case 'D6KV':
        $judul_unit = "PLTU UNIT D MOTOR 6kV"; // Format diperbarui
        break;
    case 'C380':
        $judul_unit = "PLTU UNIT C MOTOR 380V"; // Format diperbarui
        break;
    case 'D380':
        $judul_unit = "PLTU UNIT D MOTOR 380V"; // Format diperbarui
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
    // 1. FUNGSI UNTUK DOWNLOAD GRAFIK KE PNG (DIPERBARUI DENGAN JUDUL)
    function downloadChart(canvasId, namaMotor, judulUnit) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            alert("Grafik belum siap atau tidak ada data!");
            return;
        }

        const headerHeight = 60; // Ruang tambahan di atas grafik untuk judul
        const paddingX = 20;     // Jarak teks dari pinggir kiri

        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width;
        // Tinggi total adalah tinggi grafik ditambah ruang judul
        tempCanvas.height = canvas.height + headerHeight; 
        const tempCtx = tempCanvas.getContext('2d');

        // A. Mengisi background menjadi putih utuh
        tempCtx.fillStyle = '#ffffff';
        tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);

        // B. Menulis Judul di bagian atas (Kiri)
        tempCtx.fillStyle = '#333333';
        tempCtx.font = "bold 18px 'Source Sans Pro', Arial, sans-serif";
        tempCtx.textAlign = 'left';
        tempCtx.textBaseline = 'middle';
        
        // Format: Nama Unit - Nama Motor
        const teksJudul = `${judulUnit} - ${namaMotor}`;
        tempCtx.fillText(teksJudul, paddingX, 30);

        // C. Membuat Garis Pembatas tipis di bawah judul agar terlihat elegan
        tempCtx.beginPath();
        tempCtx.moveTo(paddingX, 50);
        tempCtx.lineTo(tempCanvas.width - paddingX, 50);
        tempCtx.strokeStyle = '#e0e0e0';
        tempCtx.lineWidth = 1;
        tempCtx.stroke();

        // D. Menggambar grafik tepat di bawah area judul (y = headerHeight)
        tempCtx.drawImage(canvas, 0, headerHeight);

        // Proses Download
        const safeName = namaMotor.replace(/[^a-zA-Z0-9]/g, '_');
        const fileName = `Trend_Maintenance_${safeName}.png`;

        const link = document.createElement('a');
        link.download = fileName;
        link.href = tempCanvas.toDataURL('image/png');
        link.click();
    }

    document.addEventListener("DOMContentLoaded", function () {
        const unitAktif = "<?php echo $unitAktif; ?>";
        // Mengambil teks judul unit dari PHP untuk dilempar ke fungsi download
        const judulUnitLengkap = "<?php echo $judul_unit; ?>";

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

        // 2. GENERATE HTML KARTU GRAFIK & TOMBOL KONTROL
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

                            <button type="button" class="btn btn-tool" title="Download Grafik PNG" onclick="downloadChart('chart_${index}', '${nama}', '${judulUnitLengkap}')">
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
                        <div class="chart mt-0">
                            <canvas id="chart_${index}" style="min-height: 380px; height: 380px; max-height: 380px; max-width: 100%;"></canvas>
                        </div>

                        <div class="d-flex justify-content-start align-items-center mt-0 pt-1 pb-1 pl-3">
                            
                            <div class="d-flex align-items-center border-right pr-3 mr-3">
                                <button class="btn btn-sm btn-outline-secondary px-2 mx-1" id="btn_prev_${index}" title="Mundur ke data sebelumnya (-)"><i class="fas fa-chevron-left"></i></button>
                                <span class="text-muted text-sm font-weight-bold mx-1" id="page_info_${index}" style="min-width: 120px; text-align: center;">Menyiapkan...</span>
                                <button class="btn btn-sm btn-outline-secondary px-2 mx-1" id="btn_next_${index}" title="Maju ke data selanjutnya (+)"><i class="fas fa-chevron-right"></i></button>
                            </div>

                            <div class="d-flex align-items-center">
                                <label for="data_limit_${index}" class="text-muted text-sm font-weight-normal mb-0 mr-2">Limit:</label>
                                <select id="data_limit_${index}" class="custom-select custom-select-sm form-control-border" style="width: auto; min-width: 80px;">
                                    <option value="15" selected>15 Data</option>
                                    <option value="30">30 Data</option>
                                    <option value="50">50 Data</option>
                                    <option value="all">Semua Data</option>
                                </select>
                            </div>

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

                        // ==========================================
                        // SETUP PAGINATION (PENYIMPANAN DATA UTUH)
                        // ==========================================
                        const fullLabels = result.labels;
                        const fullData = {
                            DE: result.dataDE,
                            NDE: result.dataNDE,
                            TempDE: result.dataTempDE,
                            TempNDE: result.dataTempNDE,
                            Suhu: result.dataSuhu,
                            Beban: result.dataBeban,
                            Damper: result.dataDamper,
                            Current: result.dataCurrent
                        };

                        let maxVisible = 15; 
                        const shiftStep = 1; 

                        let currentIndex = Math.max(0, fullLabels.length - maxVisible);
                        let startInit = currentIndex;
                        let endInit = currentIndex + maxVisible;

                        const areaChartData = {
                            labels: fullLabels.slice(startInit, endInit),
                            datasets: [
                                { label: 'Vibrasi Bearing DE', borderColor: 'rgba(60,141,188, 1)', backgroundColor: 'rgba(60,141,188, 0.1)', borderWidth: 2.5, pointRadius: 2, data: fullData.DE.slice(startInit, endInit), fill: false, spanGaps: true },
                                { label: 'Vibrasi Bearing NDE', borderColor: 'rgba(220,53,69, 1)', backgroundColor: 'rgba(220,53,69, 0.1)', borderWidth: 2.5, pointRadius: 2, data: fullData.NDE.slice(startInit, endInit), fill: false, spanGaps: true },
                                { label: 'Temp Bearing DE', borderColor: 'rgba(255, 133, 27, 1)', borderWidth: 2, pointRadius: 2, data: fullData.TempDE.slice(startInit, endInit), fill: false, spanGaps: true, hidden: true },
                                { label: 'Temp Bearing NDE', borderColor: 'rgba(255, 193, 7, 1)', borderWidth: 2, pointRadius: 2, data: fullData.TempNDE.slice(startInit, endInit), fill: false, spanGaps: true, hidden: true },
                                { label: 'Suhu Ruangan', borderColor: 'rgba(40, 167, 69, 1)', borderWidth: 2, pointRadius: 2, data: fullData.Suhu.slice(startInit, endInit), fill: false, spanGaps: true, hidden: true },
                                { label: 'Beban Generator', borderColor: 'rgba(111, 66, 193, 1)', borderWidth: 2, pointRadius: 2, data: fullData.Beban.slice(startInit, endInit), fill: false, spanGaps: true, hidden: true },
                                { label: 'Opening Damper', borderColor: 'rgba(32, 201, 151, 1)', borderWidth: 2, pointRadius: 2, data: fullData.Damper.slice(startInit, endInit), fill: false, spanGaps: true, hidden: true },
                                { label: 'Load Current', borderColor: 'rgba(139, 0, 0, 1)', borderWidth: 2, pointRadius: 2, data: fullData.Current.slice(startInit, endInit), fill: false, spanGaps: true, hidden: true }
                            ]
                        };

                        const lineChartOptions = {
                            maintainAspectRatio: false,
                            responsive: true,
                            layout: { padding: { top: 35, bottom: 5, right: 15, left: 10 } },
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    usePointStyle: false,
                                    boxWidth: 10,
                                    padding: 10,
                                    fontColor: '#444',
                                    fontSize: 12,
                                    generateLabels: function (chart) {
                                        const original = Chart.defaults.global.legend.labels.generateLabels;
                                        const labels = original.call(this, chart);
                                        labels.forEach(label => { 
                                            label.width = 170; 
                                            if (label.hidden) {
                                                label.fillStyle = 'rgba(200, 200, 200, 0.4)';
                                                label.strokeStyle = 'rgba(150, 150, 150, 0.4)';
                                                label.fontColor = '#adb5bd'; 
                                            } else {
                                                label.fontColor = '#444'; 
                                            }
                                        });
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
                                enabled: true, 
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleFontColor: '#333',
                                bodyFontColor: '#555',
                                borderWidth: 0,
                                cornerRadius: 8,
                                xPadding: 12,
                                yPadding: 12,
                                caretPadding: 20,
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
                            animation: { duration: 0 } 
                        };

                        const myChart = new Chart(ctx, {
                            type: 'line',
                            data: areaChartData,
                            options: lineChartOptions,
                            plugins: [{
                                beforeTooltipDraw: function (chart) {
                                    const checkBox = document.getElementById(`toggle_tooltip_${indexId}`);
                                    if (checkBox && !checkBox.checked) {
                                        return false;
                                    }
                                },
                                afterEvent: function (chart, event) {
                                    if (event.type === 'mousemove' || event.type === 'touchstart' || event.type === 'touchmove') {
                                        if (event.y !== undefined) {
                                            chart.customCrosshairY = event.y;
                                        }
                                    } else if (event.type === 'mouseout') {
                                        chart.customCrosshairY = undefined;
                                    }
                                },
                                afterDraw: function (chart) {
                                    if (!chart || !chart.ctx) return;
                                    const ctx = chart.ctx;

                                    // 1. TAMPILKAN ANGKA DI ATAS TITIK (ANTI-BLUR FIX)
                                    const showDataLabels = document.getElementById(`toggle_datalabels_${indexId}`);
                                    if (showDataLabels && showDataLabels.checked) {
                                        ctx.save(); 
                                        ctx.font = "bold 11px 'Source Sans Pro', Arial, sans-serif"; 
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
                                                                if (element._model.x >= chart.chartArea.left && element._model.x <= chart.chartArea.right &&
                                                                    element._model.y >= chart.chartArea.top && element._model.y <= chart.chartArea.bottom) {

                                                                    ctx.fillStyle = dataset.borderColor;
                                                                    ctx.fillText(dataValue, Math.round(element._model.x), Math.round(element._model.y) - 5);
                                                                }
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                        ctx.restore(); 
                                    }

                                    // 2. TAMPILKAN CROSSHAIR (ANTI-BLUR FIX)
                                    if (chart.tooltip && chart.tooltip._active && chart.tooltip._active.length && chart.customCrosshairY !== undefined) {
                                        const activePoints = chart.tooltip._active;

                                        if (!activePoints[0] || !activePoints[0]._model) return;

                                        const x = Math.round(activePoints[0]._model.x);
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
                                        
                                        closestY = Math.round(closestY); 

                                        if (chart.chartArea && closestY >= chart.chartArea.top && closestY <= chart.chartArea.bottom) {
                                            const topY = Math.round(chart.chartArea.top);
                                            const bottomY = Math.round(chart.chartArea.bottom);
                                            const leftX = Math.round(chart.chartArea.left);
                                            const rightX = Math.round(chart.chartArea.right);

                                            ctx.save();
                                            ctx.beginPath();
                                            ctx.setLineDash([6, 6]);
                                            ctx.lineWidth = 1; 
                                            ctx.strokeStyle = 'rgba(0, 0, 0, 0.4)';

                                            ctx.moveTo(x + 0.5, topY);
                                            ctx.lineTo(x + 0.5, bottomY);
                                            ctx.moveTo(leftX, closestY + 0.5);
                                            ctx.lineTo(rightX, closestY + 0.5);

                                            ctx.stroke();
                                            ctx.restore();
                                        }
                                    }
                                }
                            }]
                        });

                        // ==========================================
                        // FUNGSI UPDATE DATA (GESER / UBAH LIMIT)
                        // ==========================================
                        function updateChartDataWindow() {
                            const start = currentIndex;
                            const limit = maxVisible === 'all' ? fullLabels.length : maxVisible;
                            const end = maxVisible === 'all' ? fullLabels.length : currentIndex + limit;

                            myChart.data.labels = fullLabels.slice(start, end);
                            myChart.data.datasets[0].data = fullData.DE.slice(start, end);
                            myChart.data.datasets[1].data = fullData.NDE.slice(start, end);
                            myChart.data.datasets[2].data = fullData.TempDE.slice(start, end);
                            myChart.data.datasets[3].data = fullData.TempNDE.slice(start, end);
                            myChart.data.datasets[4].data = fullData.Suhu.slice(start, end);
                            myChart.data.datasets[5].data = fullData.Beban.slice(start, end);
                            myChart.data.datasets[6].data = fullData.Damper.slice(start, end);
                            myChart.data.datasets[7].data = fullData.Current.slice(start, end);

                            myChart.update(0);

                            const btnPrev = document.getElementById(`btn_prev_${indexId}`);
                            const btnNext = document.getElementById(`btn_next_${indexId}`);
                            const infoText = document.getElementById(`page_info_${indexId}`);

                            if (maxVisible === 'all') {
                                if (btnPrev) btnPrev.disabled = true;
                                if (btnNext) btnNext.disabled = true;
                                if (infoText) infoText.innerText = `Semua Data (${fullLabels.length})`;
                            } else {
                                if (btnPrev) btnPrev.disabled = (currentIndex === 0);
                                if (btnNext) btnNext.disabled = (currentIndex + limit >= fullLabels.length);
                                
                                const displayEnd = Math.min(start + limit, fullLabels.length);
                                if (infoText) {
                                    if (fullLabels.length <= limit) {
                                        infoText.innerText = `Semua Data (${fullLabels.length})`;
                                        if(btnPrev) btnPrev.disabled = true;
                                        if(btnNext) btnNext.disabled = true;
                                    } else {
                                        infoText.innerText = `Data ${start + 1} - ${displayEnd} dari ${fullLabels.length}`;
                                    }
                                }
                            }
                        }

                        updateChartDataWindow();

                        // ==========================================
                        // EVENT LISTENER KONTROL BAWAH
                        // ==========================================

                        // Dropdown Limit Data
                        const limitSelect = document.getElementById(`data_limit_${indexId}`);
                        if (limitSelect) {
                            limitSelect.addEventListener('change', function() {
                                const val = this.value;
                                if (val === 'all') {
                                    maxVisible = 'all';
                                    currentIndex = 0; 
                                } else {
                                    maxVisible = parseInt(val);
                                    currentIndex = Math.max(0, fullLabels.length - maxVisible);
                                }
                                updateChartDataWindow();
                            });
                        }

                        // Tombol Mundur (-)
                        const btnPrev = document.getElementById(`btn_prev_${indexId}`);
                        if (btnPrev) {
                            btnPrev.addEventListener('click', () => {
                                if (maxVisible !== 'all') {
                                    currentIndex = Math.max(0, currentIndex - shiftStep);
                                    updateChartDataWindow();
                                }
                            });
                        }

                        // Tombol Maju (+)
                        const btnNext = document.getElementById(`btn_next_${indexId}`);
                        if (btnNext) {
                            btnNext.addEventListener('click', () => {
                                if (maxVisible !== 'all') {
                                    currentIndex = Math.min(fullLabels.length - maxVisible, currentIndex + shiftStep);
                                    updateChartDataWindow();
                                }
                            });
                        }

                        // Checkbox Tooltip
                        const tooltipCb = document.getElementById(`toggle_tooltip_${indexId}`);
                        if (tooltipCb) {
                            tooltipCb.addEventListener('change', function () {
                                myChart.update(0);
                            });
                        }

                        // Checkbox Angka
                        const datalabelsCb = document.getElementById(`toggle_datalabels_${indexId}`);
                        if (datalabelsCb) {
                            datalabelsCb.addEventListener('change', function () {
                                myChart.update(0);
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

        listPeralatan.forEach(function (namaMotor, index) {
            fetchAndRenderChart(namaMotor, index);
        });
    });
</script>