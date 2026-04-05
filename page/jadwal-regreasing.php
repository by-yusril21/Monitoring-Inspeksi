<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Panggil koneksi database
require 'config/database.php';

// 2. Siapkan variabel array kosong dengan nama yang baru (Sesuai permintaan Anda)
$pltu_unit_c_motor_6kv = [];
$pltu_unit_c_motor_380v = [];
$pltu_unit_d_motor_6kv = [];
$pltu_unit_d_motor_380v = [];

// 3. Ambil data HANYA yang dicentang di Filter Regreasing
$query = "SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'regreasing_filter_%'";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Data filter disimpan dengan koma, kita pecah jadi array
        $list_array = array_filter(array_map('trim', explode(",", $row['setting_value'])));
        
        // Masukkan ke dalam variabel yang sesuai
        if ($row['setting_key'] == 'regreasing_filter_c6kv') $pltu_unit_c_motor_6kv = array_values($list_array);
        if ($row['setting_key'] == 'regreasing_filter_c380') $pltu_unit_c_motor_380v = array_values($list_array);
        if ($row['setting_key'] == 'regreasing_filter_d6kv') $pltu_unit_d_motor_6kv = array_values($list_array);
        if ($row['setting_key'] == 'regreasing_filter_d380') $pltu_unit_d_motor_380v = array_values($list_array);
    }
}
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid" style="padding-top: 10px;">

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>PLTU UNIT C - MOTOR 6kV</b></h3>
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
                        <table class="table table-striped table-hover table-bordered m-0 table-jadwal" id="table-unit-c-6kv">
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
                                <?php if (empty($pltu_unit_c_motor_6kv)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_c_motor_6kv as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo $motor; ?>">
                                            <td><b><?php echo $motor; ?></b></td>
                                            <td class="status-updater">--</td>
                                            <td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td>
                                            <td class="jadwal-selanjutnya">--</td>
                                            <td class="sisa-waktu">--</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>PLTU UNIT D - MOTOR 6kV</b></h3>
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
                        <table class="table table-striped table-hover table-bordered m-0 table-jadwal" id="table-unit-d-6kv">
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
                                <?php if (empty($pltu_unit_d_motor_6kv)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_d_motor_6kv as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo $motor; ?>">
                                            <td><b><?php echo $motor; ?></b></td>
                                            <td class="status-updater">--</td>
                                            <td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td>
                                            <td class="jadwal-selanjutnya">--</td>
                                            <td class="sisa-waktu">--</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>PLTU UNIT C - MOTOR 380V</b></h3>
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
                        <table class="table table-striped table-hover table-bordered m-0 table-jadwal" id="table-unit-c-380v">
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
                                <?php if (empty($pltu_unit_c_motor_380v)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_c_motor_380v as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo $motor; ?>">
                                            <td><b><?php echo $motor; ?></b></td>
                                            <td class="status-updater">--</td>
                                            <td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td>
                                            <td class="jadwal-selanjutnya">--</td>
                                            <td class="sisa-waktu">--</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>PLTU UNIT D - MOTOR 380V</b></h3>
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
                        <table class="table table-striped table-hover table-bordered m-0 table-jadwal" id="table-unit-d-380v">
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
                                <?php if (empty($pltu_unit_d_motor_380v)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_d_motor_380v as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo $motor; ?>">
                                            <td><b><?php echo $motor; ?></b></td>
                                            <td class="status-updater">--</td>
                                            <td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td>
                                            <td class="jadwal-selanjutnya">--</td>
                                            <td class="sisa-waktu">--</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>