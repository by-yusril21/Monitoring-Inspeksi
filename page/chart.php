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
    // FUNGSI UNTUK DOWNLOAD GRAFIK KE PNG (Berada di luar DOMContentLoaded agar bisa dipanggil tombol HTML)
    function downloadChart(canvasId, namaMotor) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            alert("Grafik belum siap atau tidak ada data!");
            return;
        }

        // Trik Profesional: Bawaan Chart.js background-nya transparan. 
        // Jika didownload langsung, kadang backgroundnya jadi hitam/gelap.
        // Kita buat canvas sementara untuk memberi warna dasar putih.
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height;
        const tempCtx = tempCanvas.getContext('2d');

        // Isi background warna putih
        tempCtx.fillStyle = '#ffffff';
        tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);

        // Timpa dengan grafik asli
        tempCtx.drawImage(canvas, 0, 0);

        // Buat nama file yang rapi (menghilangkan spasi berlebih)
        const safeName = namaMotor.replace(/[^a-zA-Z0-9]/g, '_');
        const fileName = `Trend_Maintenance_${safeName}.png`;

        // Buat link download palsu, lalu klik otomatis
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

        // 1. GENERATE KOTAK DENGAN TOMBOL DOWNLOAD
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
                                {
                                    label: 'Vibrasi Bearing DE',
                                    borderColor: 'rgba(60,141,188, 1)',
                                    backgroundColor: 'rgba(60,141,188, 0.1)',
                                    borderWidth: 2.5,
                                    pointRadius: 2,
                                    data: result.dataDE,
                                    fill: true,
                                    spanGaps: true
                                },
                                {
                                    label: 'Vibrasi Bearing NDE',
                                    borderColor: 'rgba(220,53,69, 1)',
                                    backgroundColor: 'rgba(220,53,69, 0.1)',
                                    borderWidth: 2.5,
                                    pointRadius: 2,
                                    data: result.dataNDE,
                                    fill: true,
                                    spanGaps: true
                                },
                                {
                                    label: 'Temp Bearing DE',
                                    borderColor: 'rgba(255, 133, 27, 1)', // Orange
                                    borderWidth: 2,
                                    pointRadius: 2,
                                    data: result.dataTempDE,
                                    fill: false,
                                    spanGaps: true
                                },
                                {
                                    label: 'Temp Bearing NDE',
                                    borderColor: 'rgba(255, 193, 7, 1)', // Kuning
                                    borderWidth: 2,
                                    pointRadius: 2,
                                    data: result.dataTempNDE,
                                    fill: false,
                                    spanGaps: true
                                },
                                {
                                    label: 'Suhu Ruangan',
                                    borderColor: 'rgba(40, 167, 69, 1)', // Hijau
                                    borderWidth: 2,
                                    pointRadius: 2,
                                    data: result.dataSuhu,
                                    fill: false,
                                    spanGaps: true
                                },
                                {
                                    label: 'Beban Generator',
                                    borderColor: 'rgba(111, 66, 193, 1)', // Ungu
                                    borderWidth: 2,
                                    pointRadius: 2,
                                    data: result.dataBeban,
                                    fill: false,
                                    spanGaps: true
                                },
                                {
                                    label: 'Opening Damper',
                                    borderColor: 'rgba(32, 201, 151, 1)', // Teal
                                    borderWidth: 2,
                                    pointRadius: 2,
                                    data: result.dataDamper,
                                    fill: false,
                                    spanGaps: true
                                },
                                {
                                    label: 'Load Current',
                                    borderColor: 'rgba(139, 0, 0, 1)', // Merah Gelap
                                    borderWidth: 2,
                                    pointRadius: 2,
                                    data: result.dataCurrent,
                                    fill: false,
                                    spanGaps: true
                                }
                            ]
                        };

                        const lineChartOptions = {
                            maintainAspectRatio: false,
                            responsive: true,
                            layout: { padding: { top: 0, bottom: 0 } },
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { usePointStyle: false, boxWidth: 12, padding: 10, fontFamily: 'Helvetica Neue, Arial, sans-serif' }
                            },
                            scales: {
                                xAxes: [{
                                    gridLines: { display: false },
                                    ticks: { fontColor: '#888', maxTicksLimit: 15 },
                                    scaleLabel: { display: true, labelString: 'Waktu Pengukuran', fontColor: '#333', fontStyle: 'bold' }
                                }],
                                yAxes: [{
                                    gridLines: { display: true, color: 'rgba(0,0,0,0.1)', borderDash: [5, 5] },
                                    ticks: { beginAtZero: true, fontColor: '#888', padding: 10 },
                                    scaleLabel: { display: true, labelString: 'Nilai Parameter', fontColor: '#333', fontStyle: 'bold' }
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
                                    label: function (tooltipItem, data) {
                                        let label = data.datasets[tooltipItem.datasetIndex].label || '';
                                        let val = tooltipItem.yLabel;
                                        let unit = '';

                                        // Setel satuan dinamis berdasarkan nama label
                                        if (label.includes('Vibrasi')) unit = ' mm/s';
                                        else if (label.includes('Temp') || label.includes('Suhu')) unit = ' °C';
                                        else if (label.includes('Beban')) unit = ' MW'; // Ubah sesuai satuanmu (misal kW atau MW)
                                        else if (label.includes('Damper')) unit = ' %';
                                        else if (label.includes('Current')) unit = ' A';

                                        if (label) { label += ': '; }
                                        return label + val + unit;
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

        // 3. JALANKAN FETCH
        listPeralatan.forEach(function (namaMotor, index) {
            fetchAndRenderChart(namaMotor, index);
        });

    });
</script>