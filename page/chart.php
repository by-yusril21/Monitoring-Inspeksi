<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simulasi Data Tanggal (Bulan lalu hingga perkiraan bulan depan)
$tanggal = ['2026-01-15', '2026-01-30', '2026-02-15', '2026-02-28', '2026-03-15', '2026-03-30', '2026-03-15', '2026-03-15', '2026-03-15', '2026-03-15', '2026-03-15', '2026-03-15', '2026-03-15', '2026-03-15', '2026-03-15',];

// Data Vibrasi Bearing DE & NDE (mm/s) (Simulasi untuk semua chart)
$vibrasi_de = [2.1, 2.3, 2.4, 2.2, 2.6, 2.8];
$vibrasi_nde = [1.5, 1.6, 1.5, 1.7, 1.8, 1.9];

// Array Nama Motor/Equipment untuk 9 Chart
$nama_peralatan = [
    "BOILER FEED WATER PUMP A",
    "BOILER FEED WATER PUMP B",
    "COAL MILL C",
    "FORCED DRAFT FAN C",
    "PULVERIZED FAN C",
    "INDUCED DRAFT FAN C",
    "VENT GAS FAN C",
    "SEA WATER INTAKE PUMP A",
    "SEA WATER INTAKE PUMP C"
];
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Data Sensor Vibrasi</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">

                <?php
                // Looping untuk membuat 9 Card / Kolom secara otomatis
                foreach ($nama_peralatan as $index => $nama) {
                    ?>
                    <div class="col-lg-6">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title"><?php echo $nama; ?></h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart">
                                    <canvas id="chart_<?php echo $index; ?>"
                                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/chart.js/Chart.min.js"></script>

<script>
    $(function () {
        // 1. Ambil data dari PHP
        var labelsBulan = <?php echo json_encode($tanggal); ?>;
        var dataDE = <?php echo json_encode($vibrasi_de); ?>;
        var dataNDE = <?php echo json_encode($vibrasi_nde); ?>;
        var listPeralatan = <?php echo json_encode($nama_peralatan); ?>;

        // 2. Setup Data (Dataset)
        var areaChartData = {
            labels: labelsBulan,
            datasets: [
                {
                    label: 'Vibrasi DE (mm/s)',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    pointRadius: 4,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: dataDE,
                    fill: false
                },
                {
                    label: 'Vibrasi NDE (mm/s)',
                    backgroundColor: 'rgba(210, 214, 222, 1)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    pointRadius: 4,
                    pointColor: 'rgba(255, 99, 132, 1)',
                    pointStrokeColor: '#c1c7d1',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(255, 99, 132, 1)',
                    data: dataNDE,
                    fill: false
                }
            ]
        };

        // 3. Opsi Garis (Line Chart Options)
        var lineChartOptions = {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                display: true,
                position: 'bottom' // <-- Label vibrasi dipindah ke bawah sumbu X
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false,
                    }
                }],
                yAxes: [{
                    gridLines: {
                        display: true,
                    },
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        };

        // 4. Looping untuk Render ke-9 Chart secara otomatis
        listPeralatan.forEach(function (nama, index) {
            // Ambil ID Canvas yang sesuai (chart_0, chart_1, dst)
            var canvasElement = $('#chart_' + index).get(0);

            // Cek jika canvas ada di HTML
            if (canvasElement) {
                var lineChartCanvas = canvasElement.getContext('2d');

                // Clone objek agar setiap chart berdiri sendiri dan tidak saling overwrite
                var chartData = $.extend(true, {}, areaChartData);
                var chartOptions = $.extend(true, {}, lineChartOptions);

                // Inisialisasi Chart
                new Chart(lineChartCanvas, {
                    type: 'line',
                    data: chartData,
                    options: chartOptions
                });
            }
        });

    });
</script>