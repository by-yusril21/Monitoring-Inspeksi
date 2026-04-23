<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Panggil koneksi database
require 'config/database.php';

// Menarik Username untuk Footer PDF
$username_login = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';

// Default Judul PDF
$pdf_judul_1 = 'DOKUMEN RANGKUMAN DATA PMC SCHEDULE BULANAN MOTOR 6kV DAN 380V';
$pdf_judul_2 = 'PT Semen Tonasa - Electrical of Power Plant Elins Maintenance';
$pdf_logo_base64 = '';

// Ambil Setting PDF dari Database
$query_settings = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('pdf_judul_1', 'pdf_judul_2', 'pdf_logo_base64')";
$hasil_settings = mysqli_query($conn, $query_settings);
if ($hasil_settings) {
    while ($row = mysqli_fetch_assoc($hasil_settings)) {
        if ($row['setting_key'] == 'pdf_judul_1' && !empty($row['setting_value'])) $pdf_judul_1 = $row['setting_value'];
        if ($row['setting_key'] == 'pdf_judul_2' && !empty($row['setting_value'])) $pdf_judul_2 = $row['setting_value'];
        if ($row['setting_key'] == 'pdf_logo_base64') $pdf_logo_base64 = $row['setting_value'];
    }
}

// 2. Siapkan variabel array kosong dengan nama yang baru
$pltu_unit_c_motor_6kv = [];
$pltu_unit_c_motor_380v = [];
$pltu_unit_d_motor_6kv = [];
$pltu_unit_d_motor_380v = [];
$pltu_unit_utility_motor_6kv = [];
$pltu_unit_utility_motor_380v = [];
$pltu_unit_utility_motor_240v = [];

// 3. Ambil data HANYA yang dicentang di Filter Regreasing
$query = "SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'regreasing_filter_%'";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $list_array = array_filter(array_map('trim', explode(",", $row['setting_value'])));
        if ($row['setting_key'] == 'regreasing_filter_c6kv') $pltu_unit_c_motor_6kv = array_values($list_array);
        if ($row['setting_key'] == 'regreasing_filter_c380') $pltu_unit_c_motor_380v = array_values($list_array);
        if ($row['setting_key'] == 'regreasing_filter_d6kv') $pltu_unit_d_motor_6kv = array_values($list_array);
        if ($row['setting_key'] == 'regreasing_filter_d380') $pltu_unit_d_motor_380v = array_values($list_array);
        if ($row['setting_key'] == 'regreasing_filter_utility6kv') $pltu_unit_utility_motor_6kv = array_values($list_array);
        if ($row['setting_key'] == 'regreasing_filter_utility380') $pltu_unit_utility_motor_380v = array_values($list_array);
        if ($row['setting_key'] == 'regreasing_filter_utility240') $pltu_unit_utility_motor_240v = array_values($list_array);
    }
}
?>

<div class="content-wrapper">
    <div class="content">
        <span id="nama-user-login" style="display: none;"><?php echo htmlspecialchars($username_login); ?></span>
        <span id="judul-1-pdf" style="display: none;"><?php echo htmlspecialchars($pdf_judul_1); ?></span>
        <span id="judul-2-pdf" style="display: none;"><?php echo htmlspecialchars($pdf_judul_2); ?></span>
        <span id="logo-base64-pdf" style="display: none;"><?php echo htmlspecialchars($pdf_logo_base64); ?></span>
        
        <div class="container-fluid" style="padding-top: 10px;">
            
            <!-- PLTU UNIT C - MOTOR 6kV -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>PLTU UNIT C - MOTOR 6kV</b></h3>
                   <div class="card-tools">
                        <a href="index.php?page=masterJadwal&unit=C6KV" class="btn btn-warning btn-sm mr-1 text-dark font-weight-bold">
                            <i class="fas fa-database"></i> Edit 
                        </a>
                        <button type="button" class="btn btn-success btn-sm mr-1" onclick="exportToExcel('table-unit-c-6kv', 'Jadwal_Regreasing_Unit_C_6KV')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm mr-2" onclick="exportToPDF('table-unit-c-6kv', 'Jadwal_Regreasing_Unit_C_6KV', 'PLTU UNIT C MOTOR 6kV')">
                            <i class="fas fa-file-pdf"></i> PDF
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
                                    <th>NAMA MOTOR</th><th>UPDATED BY</th><th>TERAKHIR REGREASING</th><th>JADWAL SELANJUTNYA</th><th>SISA WAKTU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pltu_unit_c_motor_6kv)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_c_motor_6kv as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo htmlspecialchars($motor); ?>">
                                            <td><b><?php echo htmlspecialchars($motor); ?></b></td>
                                            <td class="status-updater">--</td><td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td><td class="jadwal-selanjutnya">--</td><td class="sisa-waktu">--</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PLTU UNIT D - MOTOR 6kV -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>PLTU UNIT D - MOTOR 6kV</b></h3>
                    <div class="card-tools">
                        <a href="index.php?page=masterJadwal&unit=D6KV" class="btn btn-warning btn-sm mr-1 text-dark font-weight-bold">
                            <i class="fas fa-database"></i> Edit
                        </a>
                        <button type="button" class="btn btn-success btn-sm mr-1" onclick="exportToExcel('table-unit-d-6kv', 'Jadwal_Regreasing_Unit_D_6KV')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm mr-2" onclick="exportToPDF('table-unit-d-6kv', 'Jadwal_Regreasing_Unit_D_6KV', 'PLTU UNIT D MOTOR 6kV')">
                            <i class="fas fa-file-pdf"></i> PDF
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
                                    <th>NAMA MOTOR</th><th>UPDATED BY</th><th>TERAKHIR REGREASING</th><th>JADWAL SELANJUTNYA</th><th>SISA WAKTU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pltu_unit_d_motor_6kv)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_d_motor_6kv as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo htmlspecialchars($motor); ?>">
                                            <td><b><?php echo htmlspecialchars($motor); ?></b></td>
                                            <td class="status-updater">--</td><td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td><td class="jadwal-selanjutnya">--</td><td class="sisa-waktu">--</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- PLTU UNIT C - MOTOR 380V -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>PLTU UNIT C - MOTOR 380V</b></h3>
                    <div class="card-tools">
                        <a href="index.php?page=masterJadwal&unit=C380" class="btn btn-warning btn-sm mr-1 text-dark font-weight-bold">
                            <i class="fas fa-database"></i> Edit
                        </a>
                        <button type="button" class="btn btn-success btn-sm mr-1" onclick="exportToExcel('table-unit-c-380v', 'Jadwal_Regreasing_Unit_C_380V')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm mr-2" onclick="exportToPDF('table-unit-c-380v', 'Jadwal_Regreasing_Unit_C_380V', 'PLTU UNIT C MOTOR 380V')">
                            <i class="fas fa-file-pdf"></i> PDF
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
                                    <th>NAMA MOTOR</th><th>UPDATED BY</th><th>TERAKHIR REGREASING</th><th>JADWAL SELANJUTNYA</th><th>SISA WAKTU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pltu_unit_c_motor_380v)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_c_motor_380v as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo htmlspecialchars($motor); ?>">
                                            <td><b><?php echo htmlspecialchars($motor); ?></b></td>
                                            <td class="status-updater">--</td><td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td><td class="jadwal-selanjutnya">--</td><td class="sisa-waktu">--</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

             <!-- UNIT D MOTRO 380V -->
            <div class="card card-outline card-info"> 
                <div class="card-header">
                    <h3 class="card-title"><b>PLTU UNIT D - MOTOR 380V</b></h3>
                    <div class="card-tools">
                        <a href="index.php?page=masterJadwal&unit=D380" class="btn btn-warning btn-sm mr-1 text-dark font-weight-bold">
                            <i class="fas fa-database"></i> Edit
                        </a>
                        <button type="button" class="btn btn-success btn-sm mr-1" onclick="exportToExcel('table-unit-d-380v', 'Jadwal_Regreasing_Unit_D_380V')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm mr-2" onclick="exportToPDF('table-unit-d-380v', 'Jadwal_Regreasing_Unit_D_380V', 'PLTU UNIT D MOTOR 380V')">
                            <i class="fas fa-file-pdf"></i> PDF
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
                                    <th>NAMA MOTOR</th><th>UPDATED BY</th><th>TERAKHIR REGREASING</th><th>JADWAL SELANJUTNYA</th><th>SISA WAKTU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pltu_unit_d_motor_380v)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_d_motor_380v as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo htmlspecialchars($motor); ?>">
                                            <td><b><?php echo htmlspecialchars($motor); ?></b></td>
                                            <td class="status-updater">--</td><td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td><td class="jadwal-selanjutnya">--</td><td class="sisa-waktu">--</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PLTU UNIT UTILITY - MOTOR 6kV -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>PLTU UNIT UTILITY - MOTOR 6kV</b></h3>
                    <div class="card-tools">
                        <a href="index.php?page=masterJadwal&unit=UTILITY6KV" class="btn btn-warning btn-sm mr-1 text-dark font-weight-bold">
                            <i class="fas fa-database"></i> Edit
                        </a>
                        <button type="button" class="btn btn-success btn-sm mr-1" onclick="exportToExcel('table-unit-utility-6kv', 'Jadwal_Regreasing_Unit_Utility_6KV')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm mr-2" onclick="exportToPDF('table-unit-utility-6kv', 'Jadwal_Regreasing_Unit_Utility_6KV', 'PLTU UNIT UTILITY MOTOR 6kV')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered m-0 table-jadwal" id="table-unit-utility-6kv">
                            <thead>
                                <tr>
                                    <th>NAMA MOTOR</th><th>UPDATED BY</th><th>TERAKHIR REGREASING</th><th>JADWAL SELANJUTNYA</th><th>SISA WAKTU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pltu_unit_utility_motor_6kv)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_utility_motor_6kv as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo htmlspecialchars($motor); ?>">
                                            <td><b><?php echo htmlspecialchars($motor); ?></b></td>
                                            <td class="status-updater">--</td><td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td><td class="jadwal-selanjutnya">--</td><td class="sisa-waktu">--</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PLTU UNIT UTILITY - MOTOR 380V -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><b>PLTU UNIT UTILITY - MOTOR 380V</b></h3>
                    <div class="card-tools">
                        <a href="index.php?page=masterJadwal&unit=UTILITY380" class="btn btn-warning btn-sm mr-1 text-dark font-weight-bold">
                            <i class="fas fa-database"></i> Edit
                        </a>
                        <button type="button" class="btn btn-success btn-sm mr-1" onclick="exportToExcel('table-unit-utility-380v', 'Jadwal_Regreasing_Unit_Utility_380V')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm mr-2" onclick="exportToPDF('table-unit-utility-380v', 'Jadwal_Regreasing_Unit_Utility_380V', 'PLTU UNIT UTILITY MOTOR 380V')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered m-0 table-jadwal" id="table-unit-utility-380v">
                            <thead>
                                <tr>
                                    <th>NAMA MOTOR</th><th>UPDATED BY</th><th>TERAKHIR REGREASING</th><th>JADWAL SELANJUTNYA</th><th>SISA WAKTU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pltu_unit_utility_motor_380v)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_utility_motor_380v as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo htmlspecialchars($motor); ?>">
                                            <td><b><?php echo htmlspecialchars($motor); ?></b></td>
                                            <td class="status-updater">--</td><td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td><td class="jadwal-selanjutnya">--</td><td class="sisa-waktu">--</td>
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
                    <h3 class="card-title"><b>PLTU UNIT UTILITY - MOTOR 240v</b></h3>
                    <div class="card-tools">
                        <a href="index.php?page=masterJadwal&unit=UTILITY240" class="btn btn-warning btn-sm mr-1 text-dark font-weight-bold">
                            <i class="fas fa-database"></i> Edit
                        </a>
                        <button type="button" class="btn btn-success btn-sm mr-1" onclick="exportToExcel('table-unit-utility-240v', 'Jadwal_Regreasing_Unit_Utility_240V')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm mr-2" onclick="exportToPDF('table-unit-utility-240v', 'Jadwal_Regreasing_Unit_Utility_240V', 'PLTU UNIT UTILITY MOTOR 240V')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered m-0 table-jadwal" id="table-unit-utility-240v">
                            <thead>
                                <tr>
                                    <th>NAMA MOTOR</th><th>UPDATED BY</th><th>TERAKHIR REGREASING</th><th>JADWAL SELANJUTNYA</th><th>SISA WAKTU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pltu_unit_utility_motor_240v)) { ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada motor yang dijadwalkan.<br>Silakan centang motor di menu <b>Settings > Filter Jadwal Regreasing</b>.</td></tr>
                                <?php } else { ?>
                                    <?php foreach ($pltu_unit_utility_motor_240v as $motor): ?>
                                        <tr class="data-row" data-motor="<?php echo htmlspecialchars($motor); ?>">
                                            <td><b><?php echo htmlspecialchars($motor); ?></b></td>
                                            <td class="status-updater">--</td><td class="terakhir-regreasing"><span class="text-muted"><i class='fas fa-spinner fa-spin'></i> Mengambil data</span></td><td class="jadwal-selanjutnya">--</td><td class="sisa-waktu">--</td>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>