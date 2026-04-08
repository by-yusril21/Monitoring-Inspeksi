<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- KODE BARU: AMBIL SETTING DARI DATABASE ---
require 'config/database.php'; // PERBAIKAN PATH

// Nilai default darurat (jika database gagal diakses/kosong)
$db_max_visible = 15;
$db_shift_step = 1;
$db_line_width = 2.5;
$db_point_radius = 2;
$db_default_tooltip = 0;
$db_default_datalabel = 0;
$db_hidden_params = 'TempDE,TempNDE,Suhu,Beban,Damper,CurrR,CurrS,CurrT';
$db_bg_download = '#ffffff';

$query_settings = "SELECT setting_key, setting_value FROM settings";
$hasil_settings = mysqli_query($conn, $query_settings);

if ($hasil_settings) {
    while ($row = mysqli_fetch_assoc($hasil_settings)) {
        if ($row['setting_key'] == 'chart_max_visible')
            $db_max_visible = (int) $row['setting_value'];
        if ($row['setting_key'] == 'chart_shift_step')
            $db_shift_step = (int) $row['setting_value'];
        if ($row['setting_key'] == 'chart_line_width')
            $db_line_width = (float) $row['setting_value'];
        if ($row['setting_key'] == 'chart_point_radius')
            $db_point_radius = (float) $row['setting_value'];
        if ($row['setting_key'] == 'chart_default_tooltip')
            $db_default_tooltip = (int) $row['setting_value'];
        if ($row['setting_key'] == 'chart_default_datalabel')
            $db_default_datalabel = (int) $row['setting_value'];
        if ($row['setting_key'] == 'chart_hidden_parameters')
            $db_hidden_params = $row['setting_value'];
        if ($row['setting_key'] == 'chart_bg_download')
            $db_bg_download = $row['setting_value'];
    }
}

$unitAktif = isset($_GET['unit']) ? strtoupper($_GET['unit']) : 'C6KV';

$judul_unit = "";
switch ($unitAktif) {
    case 'C6KV':
        $judul_unit = "PLTU UNIT C MOTOR 6kV";
        break;
    case 'D6KV':
        $judul_unit = "PLTU UNIT D MOTOR 6kV";
        break;
    case 'C380':
        $judul_unit = "PLTU UNIT C MOTOR 380V";
        break;
    case 'D380':
        $judul_unit = "PLTU UNIT D MOTOR 380V";
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
    // Penampung Global untuk Instance Chart agar tidak menumpuk saat dibuka-tutup
    window.myCharts = {};

    // 1. FUNGSI UNTUK DOWNLOAD GRAFIK KE PNG
    function downloadChart(canvasId, namaMotor, judulUnit) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            alert("Grafik belum siap atau tidak ada data!");
            return;
        }

        const headerHeight = 60;
        const paddingX = 20;

        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height + headerHeight;
        const tempCtx = tempCanvas.getContext('2d');

        tempCtx.fillStyle = '<?php echo $db_bg_download; ?>';
        tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);

        const bgColor = '<?php echo $db_bg_download; ?>'.toLowerCase();
        tempCtx.fillStyle = (bgColor === '#000000' || bgColor === 'black') ? '#ffffff' : '#333333';
        tempCtx.font = "bold 18px 'Source Sans Pro', Arial, sans-serif";
        tempCtx.textAlign = 'left';
        tempCtx.textBaseline = 'middle';

        const teksJudul = `${judulUnit} - ${namaMotor}`;
        tempCtx.fillText(teksJudul, paddingX, 30);

        tempCtx.beginPath();
        tempCtx.moveTo(paddingX, 50);
        tempCtx.lineTo(tempCanvas.width - paddingX, 50);
        tempCtx.strokeStyle = (bgColor === '#000000' || bgColor === 'black') ? '#555555' : '#e0e0e0';
        tempCtx.lineWidth = 1;
        tempCtx.stroke();

        tempCtx.drawImage(canvas, 0, headerHeight);

        const safeName = namaMotor.replace(/[^a-zA-Z0-9]/g, '_');
        const fileName = `Trend_Maintenance_${safeName}.png`;

        const link = document.createElement('a');
        link.download = fileName;
        link.href = tempCanvas.toDataURL('image/png');
        link.click();
    }

    document.addEventListener("DOMContentLoaded", function () {
        const unitAktif = "<?php echo $unitAktif; ?>";
        const judulUnitLengkap = "<?php echo $judul_unit; ?>";

        if (typeof window.dataMotor === 'undefined') {
            document.getElementById('chart-cards-container').innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <p>Error: File config_motor.php tidak terbaca atau belum dimuat.</p>
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

        // 2. GENERATE HTML KARTU GRAFIK TERTUTUP (COLLAPSED)
        let cardsHTML = "";
        listPeralatan.forEach(function (nama, index) {
            const chkTooltip = <?php echo ($db_default_tooltip == 1) ? "'checked'" : "''"; ?>;
            const chkDatalabel = <?php echo ($db_default_datalabel == 1) ? "'checked'" : "''"; ?>;

            cardsHTML += `
            <div class="col-lg-6">
                <div class="card card-outline card-primary shadow-sm collapsed-card" id="card_${index}" style="border-radius: 10px; overflow: hidden;">
                    <div class="card-header border-0 bg-white pt-2 pb-0">
                        <h3 class="card-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                            <i class="fas fa-chart-area text-primary mr-2"></i> ${nama}
                        </h3>
                        <div class="card-tools">
                            
                            <span id="chart_tools_${index}" style="display: none;">
                                <div class="custom-control custom-checkbox d-inline-block mr-2" title="Tampilkan Kotak Detail saat Hover">
                                    <input type="checkbox" class="custom-control-input" id="toggle_tooltip_${index}" ${chkTooltip}>
                                    <label class="custom-control-label" for="toggle_tooltip_${index}">Tooltip</label>
                                </div>

                                <div class="custom-control custom-checkbox d-inline-block mr-3" title="Tampilkan Angka Langsung di Atas Titik Grafik">
                                    <input type="checkbox" class="custom-control-input" id="toggle_datalabels_${index}" ${chkDatalabel}>
                                    <label class="custom-control-label" for="toggle_datalabels_${index}">Angka</label>
                                </div>

                                <button type="button" class="btn btn-tool" title="Download Grafik PNG" onclick="downloadChart('chart_${index}', '${nama}', '${judulUnitLengkap}')">
                                    <i class="fas fa-download text-info"></i>
                                </button>
                            </span>

                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Minimize/Maximize">
                                <i class="fas fa-plus"></i>
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
                            <canvas id="chart_${index}" style="min-height: 450px; height: 450px; max-height: 450px; max-width: 100%;"></canvas>
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

                        // Jika Chart sudah pernah dibuat di kotak ini, hancurkan (destroy) dulu agar tidak tumpang tindih
                        if (window.myCharts[indexId]) {
                            window.myCharts[indexId].destroy();
                        }

                        const fullLabels = result.labels;
                        const fullData = {
                            DE_H: result.dataDE_H || [],
                            DE_V: result.dataDE_V || [],
                            DE_Ax: result.dataDE_Ax || [],
                            DE_gE: result.dataDE_gE || [],

                            NDE_H: result.dataNDE_H || [],
                            NDE_V: result.dataNDE_V || [],
                            NDE_Ax: result.dataNDE_Ax || [],
                            NDE_gE: result.dataNDE_gE || [],

                            TempDE: result.dataTempDE || [],
                            TempNDE: result.dataTempNDE || [],
                            Suhu: result.dataSuhu || [],

                            Beban: result.dataBeban || [],
                            Damper: result.dataDamper || [],

                            CurrR: result.dataCurrR || [],
                            CurrS: result.dataCurrS || [],
                            CurrT: result.dataCurrT || []
                        };

                        let maxVisible = <?php echo $db_max_visible; ?>;
                        const shiftStep = <?php echo $db_shift_step; ?>;
                        const lineWidthDb = <?php echo $db_line_width; ?>;
                        const pointRadiusDb = <?php echo $db_point_radius; ?>;

                        let currentIndex = Math.max(0, fullLabels.length - maxVisible);
                        let startInit = currentIndex;
                        let endInit = currentIndex + maxVisible;

                        const hiddenParamsStr = "<?php echo $db_hidden_params; ?>";
                        const hiddenList = hiddenParamsStr.split(',').map(item => item.trim().toLowerCase());

                        function checkHidden(fullName, shortName) {
                            return hiddenList.includes(fullName.toLowerCase()) || hiddenList.includes(shortName.toLowerCase());
                        }

                        const areaChartData = {
                            labels: fullLabels.slice(startInit, endInit),
                            datasets: [
                                { label: 'Vib DE (H)', borderColor: '#007bff', backgroundColor: 'rgba(0,123,255,0.05)', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.DE_H.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Vib DE (H)', 'DE_H') },
                                { label: 'Vib DE (V)', borderColor: '#17a2b8', backgroundColor: 'rgba(23,162,184,0.05)', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.DE_V.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Vib DE (V)', 'DE_V') },
                                { label: 'Vib DE (Ax)', borderColor: '#3498db', backgroundColor: 'rgba(52,152,219,0.05)', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.DE_Ax.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Vib DE (Ax)', 'DE_Ax') },
                                { label: 'Vib DE (gE)', borderColor: '#20c997', backgroundColor: 'rgba(32,201,151,0.05)', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.DE_gE.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Vib DE (gE)', 'DE_gE') },

                                { label: 'Vib NDE (H)', borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,0.05)', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.NDE_H.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Vib NDE (H)', 'NDE_H') },
                                { label: 'Vib NDE (V)', borderColor: '#e83e8c', backgroundColor: 'rgba(232,62,140,0.05)', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.NDE_V.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Vib NDE (V)', 'NDE_V') },
                                { label: 'Vib NDE (Ax)', borderColor: '#ff6b6b', backgroundColor: 'rgba(255,107,107,0.05)', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.NDE_Ax.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Vib NDE (Ax)', 'NDE_Ax') },
                                { label: 'Vib NDE (gE)', borderColor: '#fd7e14', backgroundColor: 'rgba(253,126,20,0.05)', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.NDE_gE.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Vib NDE (gE)', 'NDE_gE') },

                                { label: 'Temp DE', borderColor: '#ffc107', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.TempDE.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Temp DE', 'TempDE') },
                                { label: 'Temp NDE', borderColor: '#ff851b', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.TempNDE.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Temp NDE', 'TempNDE') },
                                { label: 'Suhu Ruang', borderColor: '#28a745', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.Suhu.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Suhu Ruang', 'Suhu') },
                                { label: 'Beban Gen', borderColor: '#6f42c1', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.Beban.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Beban Gen', 'Beban') },
                                { label: 'Damper', borderColor: '#3d9970', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.Damper.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Damper', 'Damper') },

                                { label: 'Arus (R)', borderColor: '#8b0000', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.CurrR.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Arus (R)', 'CurrR') },
                                { label: 'Arus (S)', borderColor: '#343a40', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.CurrS.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Arus (S)', 'CurrS') },
                                { label: 'Arus (T)', borderColor: '#85144b', borderWidth: lineWidthDb, pointRadius: pointRadiusDb, data: fullData.CurrT.slice(startInit, endInit), fill: false, spanGaps: true, hidden: checkHidden('Arus (T)', 'CurrT') }
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
                                    fontSize: 11,
                                    generateLabels: function (chart) {
                                        const original = Chart.defaults.global.legend.labels.generateLabels;
                                        const labels = original.call(this, chart);
                                        labels.forEach(label => {
                                            label.width = 110;
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

                                        if (label.includes('Vib')) unit = ' mm/s';
                                        else if (label.includes('Temp') || label.includes('Suhu')) unit = ' °C';
                                        else if (label.includes('Beban')) unit = ' MW';
                                        else if (label.includes('Damper')) unit = ' %';
                                        else if (label.includes('Arus')) unit = ' A';

                                        return label + ': ' + val + unit;
                                    }
                                }
                            },
                            hover: { mode: 'index', intersect: false },
                            animation: { duration: 0 }
                        };

                        // Simpan ke Object Global
                        window.myCharts[indexId] = new Chart(ctx, {
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

                        const myChart = window.myCharts[indexId]; // Referensi untuk update controls

                        function updateChartDataWindow() {
                            const start = currentIndex;
                            const limit = maxVisible === 'all' ? fullLabels.length : maxVisible;
                            const end = maxVisible === 'all' ? fullLabels.length : currentIndex + limit;

                            myChart.data.labels = fullLabels.slice(start, end);
                            myChart.data.datasets[0].data = fullData.DE_H.slice(start, end);
                            myChart.data.datasets[1].data = fullData.DE_V.slice(start, end);
                            myChart.data.datasets[2].data = fullData.DE_Ax.slice(start, end);
                            myChart.data.datasets[3].data = fullData.DE_gE.slice(start, end);

                            myChart.data.datasets[4].data = fullData.NDE_H.slice(start, end);
                            myChart.data.datasets[5].data = fullData.NDE_V.slice(start, end);
                            myChart.data.datasets[6].data = fullData.NDE_Ax.slice(start, end);
                            myChart.data.datasets[7].data = fullData.NDE_gE.slice(start, end);

                            myChart.data.datasets[8].data = fullData.TempDE.slice(start, end);
                            myChart.data.datasets[9].data = fullData.TempNDE.slice(start, end);
                            myChart.data.datasets[10].data = fullData.Suhu.slice(start, end);

                            myChart.data.datasets[11].data = fullData.Beban.slice(start, end);
                            myChart.data.datasets[12].data = fullData.Damper.slice(start, end);

                            myChart.data.datasets[13].data = fullData.CurrR.slice(start, end);
                            myChart.data.datasets[14].data = fullData.CurrS.slice(start, end);
                            myChart.data.datasets[15].data = fullData.CurrT.slice(start, end);

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
                                        if (btnPrev) btnPrev.disabled = true;
                                        if (btnNext) btnNext.disabled = true;
                                    } else {
                                        infoText.innerText = `Data ${start + 1} - ${displayEnd} dari ${fullLabels.length}`;
                                    }
                                }
                            }
                        }

                        updateChartDataWindow();

                        // Event Listener Limit & Paging (Dibuat .onchange / .onclick agar tidak double trigger)
                        const limitSelect = document.getElementById(`data_limit_${indexId}`);
                        if (limitSelect) {
                            limitSelect.onchange = function () {
                                const val = this.value;
                                if (val === 'all') {
                                    maxVisible = 'all';
                                    currentIndex = 0;
                                } else {
                                    maxVisible = parseInt(val);
                                    currentIndex = Math.max(0, fullLabels.length - maxVisible);
                                }
                                updateChartDataWindow();
                            };
                        }

                        const btnPrev = document.getElementById(`btn_prev_${indexId}`);
                        if (btnPrev) {
                            btnPrev.onclick = () => {
                                if (maxVisible !== 'all') {
                                    currentIndex = Math.max(0, currentIndex - shiftStep);
                                    updateChartDataWindow();
                                }
                            };
                        }

                        const btnNext = document.getElementById(`btn_next_${indexId}`);
                        if (btnNext) {
                            btnNext.onclick = () => {
                                if (maxVisible !== 'all') {
                                    currentIndex = Math.min(fullLabels.length - maxVisible, currentIndex + shiftStep);
                                    updateChartDataWindow();
                                }
                            };
                        }

                        const tooltipCb = document.getElementById(`toggle_tooltip_${indexId}`);
                        if (tooltipCb) tooltipCb.onchange = () => myChart.update(0);

                        const datalabelsCb = document.getElementById(`toggle_datalabels_${indexId}`);
                        if (datalabelsCb) datalabelsCb.onchange = () => myChart.update(0);
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

        // =========================================================================
        // JQUERY EVENT LISTENER: PENDETEKSI ANIMASI BUKA/TUTUP CARD
        // =========================================================================
        $(document).on('expanded.lte.cardwidget', '.card', function () {
            let cardId = $(this).attr('id');
            if (cardId && cardId.startsWith('card_')) {
                let indexId = cardId.replace('card_', '');
                let namaMotor = listPeralatan[indexId];

                // Tampilkan Tombol & Checkbox
                document.getElementById(`chart_tools_${indexId}`).style.display = 'inline-block';

                // Tarik Data & Render Chart
                fetchAndRenderChart(namaMotor, indexId);
            }
        });

        $(document).on('collapsed.lte.cardwidget', '.card', function () {
            let cardId = $(this).attr('id');
            if (cardId && cardId.startsWith('card_')) {
                let indexId = cardId.replace('card_', '');

                // Sembunyikan Tombol & Checkbox
                document.getElementById(`chart_tools_${indexId}`).style.display = 'none';
            }
        });

    });
</script>