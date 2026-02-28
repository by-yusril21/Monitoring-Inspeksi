<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Daftar motor spesifik UNIT C 6KV
$motor_unit_c_6kv = [
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

<link rel="stylesheet" href="jadwal-regreasing.css">

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-oil-can mr-2"></i>Jadwal Regreasing Motor</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Jadwal Regreasing</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>MOTOR UNIT C 6KV</b></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm mr-2"
                            onclick="exportToExcel('table-unit-c-6kv', 'Jadwal_Regreasing_Unit_C_6KV')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered m-0 table-jadwal"
                            id="table-unit-c-6kv">
                            <thead>
                                <tr>
                                    <th>NAMA MOTOR</th>
                                    <th>UPDATED BY</th>
                                    <th>TERAKHIR REGREASING</th>
                                    <th>JADWAL SELANJUTNYA</th>
                                    <th>SISA WAKTU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($motor_unit_c_6kv as $motor): ?>
                                    <tr data-motor="<?php echo $motor; ?>">
                                        <td><b><?php echo $motor; ?></b></td>
                                        <td class="status-updater">--</td>
                                        <td class="terakhir-regreasing">
                                            <span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Menunggu
                                                API...</span>
                                        </td>
                                        <td class="jadwal-selanjutnya">--/--/----</td>
                                        <td class="sisa-waktu">--</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>