<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$unitAktif = isset($_GET['unit']) ? strtoupper($_GET['unit']) : 'C6KV';

$judul_unit = "";
switch ($unitAktif) {
    case 'C6KV': $judul_unit = "PLTU UNIT C 6KV"; break;
    case 'D6KV': $judul_unit = "PLTU UNIT D 6KV"; break;
    case 'C380': $judul_unit = "PLTU UNIT C 380V"; break;
    case 'D380': $judul_unit = "PLTU UNIT D 380V"; break;
    case 'UTILITY': $judul_unit = "PLTU UNIT UTILITY"; break;
    default: $judul_unit = "Unit Tidak Dikenal";
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-bold text-primary"><?php echo $judul_unit; ?></h1>
                    <p class="text-muted">Monitoring Trend Vibrasi Motor Aktual</p>
                </div>
            </div>
        </div>
    </div>

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

        // 1. GENERATE KOTAK DENGAN PADDING YANG SUDAH DIKURANGI AGAR LEBIH RAPAT
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
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
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
                        <div class="chart">
                            <canvas id="chart_${index}" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        
        container.innerHTML = cardsHTML;

        // 2. FUNGSI UNTUK MENARIK DATA & MENGGAMBAR CHART PRO
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
                    // Jika data kosong
                    if (result.labels.length === 0) {
                         if(loadingEl) {
                             loadingEl.classList.remove('d-flex');
                             loadingEl.innerHTML = '<div class="text-muted text-center mt-5"><i class="fas fa-folder-open fa-2x mb-2 text-lightblue"></i><br>Belum ada riwayat vibrasi direkam.</div>';
                         }
                         return;
                    }

                    // Hilangkan efek loading
                    if (loadingEl) {
                        loadingEl.classList.remove('d-flex');
                        loadingEl.classList.add('d-none');
                    }

                    // --- STYLING MODERN CHART.JS ---
                    if (canvasEl) {
                        const ctx = canvasEl.getContext('2d');

                        let gradientDE = ctx.createLinearGradient(0, 0, 0, 300);
                        gradientDE.addColorStop(0, 'rgba(60,141,188, 0.6)'); 
                        gradientDE.addColorStop(1, 'rgba(60,141,188, 0.0)'); 

                        let gradientNDE = ctx.createLinearGradient(0, 0, 0, 300);
                        gradientNDE.addColorStop(0, 'rgba(220,53,69, 0.6)');
                        gradientNDE.addColorStop(1, 'rgba(220,53,69, 0.0)');

                        const areaChartData = {
                            labels: result.labels,
                            datasets: [
                                {
                                    label: 'Vibrasi Bearing DE',
                                    backgroundColor: gradientDE,
                                    borderColor: 'rgba(60,141,188, 1)',
                                    borderWidth: 2.5,
                                    pointRadius: 3,
                                    pointHoverRadius: 6,
                                    pointBackgroundColor: '#ffffff',
                                    pointBorderColor: 'rgba(60,141,188, 1)',
                                    pointBorderWidth: 2,
                                    // Membuat titik di garis menjadi kotak juga (opsional, jika ingin seragam)
                                    pointStyle: 'rect', 
                                    data: result.dataDE,
                                    fill: true,
                                    lineTension: 0.4 
                                },
                                {
                                    label: 'Vibrasi Bearing NDE',
                                    backgroundColor: gradientNDE,
                                    borderColor: 'rgba(220,53,69, 1)',
                                    borderWidth: 2.5,
                                    pointRadius: 3,
                                    pointHoverRadius: 6,
                                    pointBackgroundColor: '#ffffff',
                                    pointBorderColor: 'rgba(220,53,69, 1)',
                                    pointBorderWidth: 2,
                                    pointStyle: 'rect', 
                                    data: result.dataNDE,
                                    fill: true,
                                    lineTension: 0.4
                                }
                            ]
                        };

                        const lineChartOptions = {
                            maintainAspectRatio: false,
                            responsive: true,
                            layout: {
                                padding: { top: 0, bottom: 0 } // Menghapus padding bawaan canvas
                            },
                            legend: { 
                                display: true, 
                                position: 'top', 
                                labels: { 
                                    usePointStyle: false, // DIUBAH: false agar menjadi kotak
                                    boxWidth: 12,         // DIUBAH: Ukuran kotak legend
                                    padding: 10,          // DIUBAH: Jarak legend ke grafik lebih rapat
                                    fontColor: '#555',
                                    fontFamily: 'Helvetica Neue, Arial, sans-serif'
                                } 
                            },
                            scales: {
                                xAxes: [{ 
                                    gridLines: { display: false, drawBorder: false },
                                    ticks: { fontColor: '#888', maxTicksLimit: 8 },
                                    scaleLabel: {
                                        display: true,
                                        labelString: 'Waktu Pengukuran',
                                        fontColor: '#333',
                                        fontStyle: 'bold'
                                    }
                                }],
                                yAxes: [{ 
                                    gridLines: { 
                                        display: true, 
                                        color: 'rgba(0,0,0,0.25)', 
                                        zeroLineColor: 'rgba(0,0,0,0.4)', 
                                        borderDash: [5, 5], 
                                        drawBorder: false
                                    },
                                    ticks: { 
                                        beginAtZero: true, 
                                        suggestedMax: 5,
                                        fontColor: '#888',
                                        padding: 10
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: 'Nilai Vibrasi (mm/s)',
                                        fontColor: '#333',
                                        fontStyle: 'bold'
                                    }
                                }]
                            },
                            tooltips: { 
                                mode: 'index', 
                                intersect: false,
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleFontColor: '#333',
                                bodyFontColor: '#555',
                                borderColor: 'rgba(0,0,0,0.1)',
                                borderWidth: 1,
                                cornerRadius: 8,
                                xPadding: 12,
                                yPadding: 12,
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                        if (label) { label += ': '; }
                                        label += tooltipItem.yLabel + ' mm/s';
                                        return label;
                                    }
                                }
                            },
                            hover: { mode: 'nearest', intersect: true }
                        };

                        new Chart(ctx, {
                            type: 'line',
                            data: areaChartData,
                            options: lineChartOptions
                        });
                    }

                } else {
                    if(loadingEl) {
                        loadingEl.classList.remove('d-flex');
                        loadingEl.innerHTML = `<div class="text-danger text-sm text-center px-3 mt-4"><i class="fas fa-times-circle mr-1"></i>${result.message}</div>`;
                    }
                }

            } catch (error) {
                console.error(`Error fetch ${namaMotor}:`, error);
                const loadingEl = document.getElementById(`loading_${indexId}`);
                if(loadingEl) {
                    loadingEl.classList.remove('d-flex');
                    loadingEl.innerHTML = '<div class="text-danger mt-4"><i class="fas fa-wifi mr-1"></i>Gagal koneksi ke server API.</div>';
                }
            }
        }

        // 3. JALANKAN FETCH
        listPeralatan.forEach(function (namaMotor, index) {
            fetchAndRenderChart(namaMotor, index);
        });

    });
</script>