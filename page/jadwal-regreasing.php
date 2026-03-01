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

// Daftar motor spesifik UNIT C 380V
$motor_unit_c_380v = [
    "EJECTOR PUMP A",
    "EJECTOR PUMP B",
    "PULVERIZED COAL FAN C",
    "MILL SEAL AIR FAN C",
    "CONDENSATE PUMP A",
    "CONDENSATE PUMP B",
    "IGNITER AIR FAN C",
    "BLOWER PFISTER C",
    "GAS AIR HEATER C"
];

// Daftar motor spesifik UNIT D 6KV
$motor_unit_d_6kv = [
    "SEA WATER INTAKE PUMP B",
    "VENT GAS FAN D",
    "INDUCED DRAFT FAN D",
    "PULVERIZED FAN D",
    "FORCED DRAFT FAN D",
    "COAL MILL D",
    "BOILER FEED WATER PUMP B",
    "BOILER FEED WATER PUMP A"
];

// Daftar motor spesifik UNIT D 380V
$motor_unit_d_380v = [
    "GAS AIR HEATER D",
    "BLOWER PFISTER D",
    "IGNITER AIR FAN D",
    "MILL SEAL AIR FAN D",
    "PULVERIZED COAL FAN D",
    "EJECTOR PUMP B",
    "EJECTOR PUMP A",
    "CONDENSATE PUMP B",
    "CONDENSATE PUMP A"
];
?>

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
        <div class="container-fluid" style="padding-top: 10px;">

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

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>MOTOR UNIT D 6KV</b></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm mr-2"
                            onclick="exportToExcel('table-unit-d-6kv', 'Jadwal_Regreasing_Unit_D_6KV')">
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
                            id="table-unit-d-6kv">
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
                                <?php foreach ($motor_unit_d_6kv as $motor): ?>
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

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>MOTOR UNIT C 380V</b></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm mr-2"
                            onclick="exportToExcel('table-unit-c-380v', 'Jadwal_Regreasing_Unit_C_380V')">
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
                            id="table-unit-c-380v">
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
                                <?php foreach ($motor_unit_c_380v as $motor): ?>
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

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>MOTOR UNIT D 380V</b></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm mr-2"
                            onclick="exportToExcel('table-unit-d-380v', 'Jadwal_Regreasing_Unit_D_380V')">
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
                            id="table-unit-d-380v">
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
                                <?php foreach ($motor_unit_d_380v as $motor): ?>
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