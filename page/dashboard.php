<?php
// WAJIB: Session Start di baris paling awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Catatan: Array $columns ini bisa dibiarkan saja jika dipakai di tempat lain...
$columns = [
    "No",
    "TIMESTAMP",
    "EMAIL UPDATE",
    "AKSI",
    "SECTION NO",
    "VIBRASI BEARING DE",
    "VIBRASI BEARING NDE",
    "TEMP. BEARING DE",
    "TEMP. BEARING NDE",
    "SUHU RUANGAN",
    "BEBAN GENERATOR",
    "OPENING DAMPER",
    "LOAD CURRENT",
    "BUNYI MOTOR",
    "PANEL LOCAL",
    "KELENGKAPAN",
    "KEBERSIHAN",
    "GROUNDING",
    "REGREASING",
    "ACTIONS"
];

// --- TAMBAHAN BARU: Query untuk mengambil Setting PDF dari Database ---
// Pastikan file koneksi ($conn) sudah di-include sebelum baris ini
$q_pdf = mysqli_query($conn, "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('pdf_judul_1', 'pdf_judul_2', 'pdf_logo_base64')");
$pdf_data = [];
if ($q_pdf) {
    while ($row = mysqli_fetch_assoc($q_pdf)) {
        $pdf_data[$row['setting_key']] = $row['setting_value'];
    }
}
?>

<style>
    /* VARIABEL WARNA TEMA TERANG (MENYESUAIKAN ADMINLTE) */
    :root {
        --bg-color: #f4f6f9;
        --card-bg: #ffffff;
        --text-main: #495057;
        --text-muted: #6c757d;
        --border-color: #dee2e6;
        --accent-color: #007bff;
    }

    html {
        scroll-behavior: smooth;
    }

    .content-wrapper {
        background-color: var(--bg-color) !important;
        overflow-x: hidden;
    }

    .section-full {
        height: 100vh;
        display: flex;
        flex-direction: column;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .px-custom-5 {
        padding-left: 2px !important;
        padding-right: 2px !important;
    }

    .table-responsive-vh {
        flex: 1;
        overflow-y: auto;
        border: 1px solid var(--border-color);
        background-color: var(--card-bg);
    }

    .card-custom {
        border-radius: 0;
        box-shadow: none !important;
        border: 1px solid var(--border-color) !important;
        display: flex;
        flex-direction: column;
        height: 100%;
        margin-bottom: 0;
        background-color: var(--card-bg);
    }

    .form-label-custom {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 3px;
        display: block;
    }

    #consoleStatus {
        font-family: 'Courier New', Courier, monospace;
        background-color: #1a1a1a;
        color: #28a745;
        border: 1px solid #333;
        padding: 10px;
        height: 120px;
        overflow-y: auto;
        font-size: 11px;
        width: 100%;
    }

    #section-tabel,
    #section-gauge,
    #section-input {
        scroll-margin-top: 25px;
    }

    /* =========================================================
       CUSTOM SCROLLBAR (VERTIKAL HILANG, HORIZONTAL TAMPIL)
       ========================================================= */

    /* 1. Pengaturan untuk browser Firefox & IE/Edge Lama */
    * {
        scrollbar-width: none !important;
        /* Sembunyikan semua di Firefox */
        -ms-overflow-style: none !important;
        /* Sembunyikan di IE/Edge */
    }

    /* Pengecualian di Firefox: Tampilkan scrollbar tipis khusus area yang butuh geser kiri-kanan */
    .dataTables_scrollBody,
    .table-responsive-vh,
    .table-responsive-custom {
        scrollbar-width: thin !important;
    }

    /* 2. Pengaturan untuk browser WebKit (Chrome, Safari, Edge Baru, Opera) */
    *::-webkit-scrollbar {
        width: 0px !important;
        /* HILANGKAN scrollbar vertikal (Atas-Bawah) */
        height: 8px !important;
        /* TAMPILKAN scrollbar horizontal (Kiri-Kanan) */
    }

    /* Mempercantik tampilan scrollbar horizontal yang muncul */
    *::-webkit-scrollbar-track {
        background: var(--bg-color);
    }

    *::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    *::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* CSS KHUSUS UNTUK MERAPIKAN HEADER TABEL BERTINGKAT */
    .table-header-custom th {
        vertical-align: middle !important;
        text-align: center !important;
        padding: 8px 5px !important;
    }
</style>

<div class="content-wrapper">
    <section id="section-tabel" class="vh-100 d-flex flex-column py-2">
        <div class="container-fluid px-1 h-100">
            <div class="card shadow-none border h-100 mb-0">
                <div class="card-body p-0 flex-fill d-flex flex-column">
                    <div class="table-responsive flex-fill" style="overflow-y: auto;">

                        <span id="nama-user-login"
                            style="display: none;"><?php echo htmlspecialchars($username_login ?? 'User'); ?></span>
                        <span id="judul-1-pdf"
                            style="display: none;"><?php echo htmlspecialchars($pdf_data['pdf_judul_1'] ?? 'DOKUMEN RANGKUMAN DATA PMC SCHEDULE BULANAN MOTOR 6kV DAN 380V'); ?></span>
                        <span id="judul-2-pdf"
                            style="display: none;"><?php echo htmlspecialchars($pdf_data['pdf_judul_2'] ?? 'PT Semen Tonasa - Electrical of Power Plant Elins Maintenance'); ?></span>
                        <span id="logo-base64-pdf"
                            style="display: none;"><?php echo htmlspecialchars($pdf_data['pdf_logo_base64'] ?? ''); ?></span>

                        <table id="example1"
                            class="table table-bordered table-striped table-hover table-sm text-nowrap m-0 text-center">

                            <thead class="bg-light text-dark align-middle">
                                <tr>
                                    <th class="align-middle" rowspan="3" style="min-width: 20px;">No</th>
                                    <th class="align-middle" rowspan="3" style="min-width: 140px;">Date</th>
                                    <th class="align-middle" rowspan="3" style="min-width: 160px;">Update By</th>
                                    <th class="align-middle" rowspan="3" style="min-width: 70px;">Aksi</th>
                                    <th class="align-middle" rowspan="3" style="min-width: 50px;">Section<br>No</th>

                                    <th class="align-middle" colspan="8">Vibrasi (mm/s)</th>

                                    <th class="align-middle" colspan="3">Temp (°C)</th>

                                    <th class="align-middle" colspan="3">Load<br>Current(A)</th>

                                    <th class="align-middle" rowspan="3" style="min-width: 50px;">Load Gene<br>rator(MW)
                                    </th>
                                    <th class="align-middle" rowspan="3" style="min-width: 50px;">Opening<br>Damper(%)
                                    </th>

                                    

                                    <th class="align-middle" rowspan="3" style="min-width: 50px;">Bunyi<br>Motor</th>
                                    <th class="align-middle" rowspan="3" style="min-width: 50px;">Kondisi<br>Panel</th>
                                    <th class="align-middle" rowspan="3" style="min-width: 50px;">Kelengkapan<br>Motor
                                    </th>
                                    <th class="align-middle" rowspan="3" style="min-width: 50px;">Kebersihan<br>Motor
                                    </th>
                                    <th class="align-middle" rowspan="3" style="min-width: 50px;">Grounding<br>Motor
                                    </th>
                                    <th class="align-middle" rowspan="3" style="min-width: 50px;">Regreasing<br>Motor
                                    </th>
                                    <th class="align-middle" rowspan="3" style="min-width: 300px;">Action</th>
                                </tr>

                                <tr>
                                    <th class="align-middle" colspan="4">DE</th>
                                    <th class="align-middle" colspan="4">NDE</th>

                                    <th class="align-middle" rowspan="2"
                                        style="width: 30px; min-width: 30px; max-width: 30px;">DE</th>
                                    <th class="align-middle" rowspan="2"
                                        style="width: 30px; min-width: 30px; max-width: 30px;">NDE</th>
                                    <th class="align-middle" rowspan="2"
                                        style="width: 30px; min-width: 30px; max-width: 30px;">Ruang</th>

                                    <th class="align-middle" rowspan="2"
                                        style="width: 30px; min-width: 30px; max-width: 30px;">R</th>
                                    <th class="align-middle" rowspan="2"
                                        style="width: 30px; min-width: 30px; max-width: 30px;">S</th>
                                    <th class="align-middle" rowspan="2"
                                        style="width: 30px; min-width: 30px; max-width: 30px;">T</th>
                                </tr>

                                <tr>
                                    <th class="align-middle" style="width: 30px; min-width: 30px; max-width: 30px;">H
                                    </th>
                                    <th class="align-middle" style="width: 30px; min-width: 30px; max-width: 30px;">V
                                    </th>
                                    <th class="align-middle" style="width: 30px; min-width: 30px; max-width: 30px;">Ax
                                    </th>
                                    <th class="align-middle" style="width: 30px; min-width: 30px; max-width: 30px;">gE
                                    </th>

                                    <th class="align-middle" style="width: 30px; min-width: 30px; max-width: 30px;">H
                                    </th>
                                    <th class="align-middle" style="width: 30px; min-width: 30px; max-width: 30px;">V
                                    </th>
                                    <th class="align-middle" style="width: 30px; min-width: 30px; max-width: 30px;">Ax
                                    </th>
                                    <th class="align-middle" style="width: 30px; min-width: 30px; max-width: 30px;">gE
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="section-gauge" class="section-full">
        <div class="container-fluid px-custom-5 h-100 py-3" style="overflow-y: auto;">

            <div class="dashboard-horizontal-split">

                <div class="gauge-main-container bg-white">
                    <div class="gauge-dashboard-title">
                        <i class="fas fa-industry mr-2 text-primary"></i>
                        Live Parameter Status: <span id="label-motor-gauge" class="text-danger">Pilih Motor...</span>
                    </div>

                    <div class="gauge-layout-vertical">

                        <div class="modern-thermo-container">

                            <div class="modern-thermo-card">
                                <div class="th-header">
                                    <div class="th-icon"><i class="fas fa-cogs"></i></div>
                                    <div class="th-title-group">
                                        <div class="th-title">Bearing DE</div>
                                        <div class="th-subtitle"><span class="th-dot green"></span> Temp</div>
                                    </div>
                                </div>
                                <div class="th-body">
                                    <div id="thermo-de" class="th-svg"></div>
                                    <div id="val-de" class="th-value">0.0 °C</div>
                                </div>
                                <div id="status-box-de" class="th-footer">
                                    <div id="status-text-de" class="th-status"><i class="fas fa-check-circle"></i>
                                        Normal</div>
                                    <div id="time-temp-de" class="th-time">--/--/----</div>
                                </div>
                            </div>

                            <div class="modern-thermo-card">
                                <div class="th-header">
                                    <div class="th-icon"><i class="fas fa-cogs"></i></div>
                                    <div class="th-title-group">
                                        <div class="th-title">Bearing NDE</div>
                                        <div class="th-subtitle"><span class="th-dot green"></span> Temp</div>
                                    </div>
                                </div>
                                <div class="th-body">
                                    <div id="thermo-nde" class="th-svg"></div>
                                    <div id="val-nde" class="th-value">0.0 °C</div>
                                </div>
                                <div id="status-box-nde" class="th-footer">
                                    <div id="status-text-nde" class="th-status"><i class="fas fa-check-circle"></i>
                                        Normal</div>
                                    <div id="time-temp-nde" class="th-time">--/--/----</div>
                                </div>
                            </div>

                            <div class="modern-thermo-card">
                                <div class="th-header">
                                    <div class="th-icon" style="color: #3498db;"><i class="fas fa-thermometer-half"></i>
                                    </div>
                                    <div class="th-title-group">
                                        <div class="th-title">Suhu Ruangan</div>
                                        <div class="th-subtitle"><span class="th-dot blue"></span> Temp</div>
                                    </div>
                                </div>
                                <div class="th-body">
                                    <div id="thermo-winding" class="th-svg"></div>
                                    <div id="val-winding" class="th-value">0.0 °C</div>
                                </div>
                                <div id="status-box-winding" class="th-footer">
                                    <div id="status-text-winding" class="th-status"><i class="fas fa-check-circle"></i>
                                        Normal</div>
                                    <div id="time-suhu-ruang" class="th-time">--/--/----</div>
                                </div>
                            </div>

                        </div>


                        <div class="modern-vibe-card">

                            <div class="vibe-row-container">

                                <div class="vibe-section">
                                    <div class="vibe-indicator"></div>
                                    <div class="vibe-content">
                                        <div class="vibe-info-row">
                                            <span class="vibe-label">BEARING DE</span>
                                        </div>
                                        <div class="vibe-tube-row">
                                            <div id="vibe-de-container" class="vibe-tube-wrapper"></div>
                                        </div>
                                        <div class="vibe-time-individual">
                                            <i class="far fa-clock"></i> <span
                                                id="ext-time-vibe-de-container">--/--/----</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="vibe-vertical-divider"></div>

                                <div class="vibe-section">
                                    <div class="vibe-indicator"></div>
                                    <div class="vibe-content">
                                        <div class="vibe-info-row">
                                            <span class="vibe-label">BEARING NDE</span>
                                        </div>
                                        <div class="vibe-tube-row">
                                            <div id="vibe-nde-container" class="vibe-tube-wrapper"></div>
                                        </div>
                                        <div class="vibe-time-individual">
                                            <i class="far fa-clock"></i> <span
                                                id="ext-time-vibe-nde-container">--/--/----</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="modern-gauge-container">

                            <div class="modern-gauge-card">
                                <div class="gauge-card-header">
                                    <span>Load Generator</span>
                                </div>
                                <div id="gauge-beban-gen" class="modern-gauge-chart"></div>
                                <div class="gauge-card-footer">
                                    <span id="time-beban-gen">Data Kosong</span>
                                </div>
                            </div>

                            <div class="modern-gauge-card">
                                <div class="gauge-card-header">
                                    <span>Opening Damper</span>
                                </div>
                                <div id="gauge-damper" class="modern-gauge-chart"></div>
                                <div class="gauge-card-footer">
                                    <span id="time-damper">Data Kosong</span>
                                </div>
                            </div>

                            <div class="modern-gauge-card">
                                <div class="gauge-card-header">
                                    <span>Load Current</span>
                                </div>
                                <div id="gauge-load-current" class="modern-gauge-chart"></div>
                                <div class="gauge-card-footer">
                                    <span id="time-load-current">Data Kosong</span>
                                </div>
                            </div>

                        </div>


                    </div>
                </div>

                <div class="condition-main-container bg-white">
                    <div class="gauge-dashboard-title">
                        <i class="fas fa-clipboard-check mr-2 text-success"></i> Kondisi Motor
                    </div>

                    <table class="condition-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Diupdate Oleh</th>
                                <th>Tanggal Update</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="cond-label">Bunyi Motor</td>
                                <td id="updater-bunyi" class="cond-name">--</td>
                                <td id="date-bunyi" class="cond-date">--/--/----</td>
                                <td style="text-align: center;"><span id="stat-bunyi"
                                        class="status-badge status-good"></span></td>
                            </tr>
                            <tr>
                                <td class="cond-label">Panel Local</td>
                                <td id="updater-panel" class="cond-name">--</td>
                                <td id="date-panel" class="cond-date">--/--/----</td>
                                <td style="text-align: center;"><span id="stat-panel"
                                        class="status-badge status-fair"></span></td>
                            </tr>
                            <tr>
                                <td class="cond-label">Kelengkapan</td>
                                <td id="updater-lengkap" class="cond-name">--</td>
                                <td id="date-lengkap" class="cond-date">--/--/----</td>
                                <td style="text-align: center;"><span id="stat-lengkap"
                                        class="status-badge status-good"></span></td>
                            </tr>
                            <tr>
                                <td class="cond-label">Kebersihan</td>
                                <td id="updater-bersih" class="cond-name">--</td>
                                <td id="date-bersih" class="cond-date">--/--/----</td>
                                <td style="text-align: center;"><span id="stat-bersih"
                                        class="status-badge status-poor"></span></td>
                            </tr>
                            <tr>
                                <td class="cond-label">Grounding</td>
                                <td id="updater-ground" class="cond-name">--</td>
                                <td id="date-ground" class="cond-date">--/--/----</td>
                                <td style="text-align: center;"><span id="stat-ground"
                                        class="status-badge status-good"></span></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="condition-divider" style="margin: 15px 0 10px 0;"></div>

                    <div class="gauge-dashboard-title"
                        style="font-size: 0.85rem; border: none; text-align: left; margin-bottom: 5px;">
                        <i class="fas fa-oil-can mr-2 text-info"></i> Jadwal Pemeliharaan
                    </div>

                    <table class="condition-table regreasing-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th style="text-align: left;">Update By</th>
                                <th>Tgl Terakhir</th>
                                <th>Jadwal Next</th>
                                <th>Sisa Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="cond-label">Regreasing</td>
                                <td id="updater-regrease" class="cond-name" style="font-weight: 600; color: #34495e;">--
                                </td>
                                <td id="date-regrease-last" class="cond-date">--/--/----</td>
                                <td id="date-regrease-next" class="cond-date">--/--/----</td>
                                <td id="time-left-regrease" class="cond-date"
                                    style="font-weight: bold; color: #d9534f;">-- Hari</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="condition-divider" style="margin: 15px 0 10px 0;"></div>

                    <div class="gauge-dashboard-title"
                        style="font-size: 0.85rem; border: none; text-align: left; margin-bottom: 5px;">
                        <i class="fas fa-clipboard-list mr-2 text-warning"></i> Action
                    </div>

                    <div class="action-box"
                        style="background-color: #f8f9fa; border-left: 4px solid #ffc107; padding: 10px; border-radius: 4px; margin-bottom: 15px; max-height: 120px; overflow-y: auto; overflow-x: hidden;">
                        <p id="teks-action"
                            style="margin: 0 0 8px 0; font-size: 0.85rem; color: #333; line-height: 1.4; font-style: italic; word-wrap: break-word;">
                            "Belum ada data action yang direkam."
                        </p>
                        <div style="font-size: 0.75rem; color: #6c757d; text-align: right; margin-top: auto;">
                            <i class="far fa-calendar-alt mr-1"></i> <span id="tanggal-action">--/--/----</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="section-input" class="section-full">
        <div class="container-fluid px-custom-5 h-100 py-3">
            <div class="card card-custom bg-white shadow-sm">
                <div class="card-header py-2 bg-light">
                    <h3 class="card-title text-sm font-weight-bold">
                        <i class="fas fa-plus-circle mr-1 text-primary"></i> INPUT DATA INSPEKSI
                    </h3>
                </div>

                <form id="formInputMotor" class="p-3">
                    <input type="hidden" id="userLoggedIn" value="<?php
                    $user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
                    $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
                    echo $user . ($role ? ' / ' . $role : '');
                    ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="form-label-custom font-weight-bold">PILIH SALAH SATU</label>
                                <select name="pilih_salah_satu" id="pilihTipe"
                                    class="form-control form-control-sm border-secondary font-weight-bold">
                                    <option value="PREVENTIVE">PREVENTIVE</option>
                                    <option value="PREDICTIVE">PREDICTIVE</option>
                                    <option value="CORRECTIVE">CORRECTIVE</option>
                                    <option value="LAINNYA">LAINNYA</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="form-label-custom font-weight-bold">SECTION NO (AUTO)</label>
                                <input type="text" name="section_no" id="inputSectionNo"
                                    class="form-control form-control-sm border-secondary font-weight-bold"
                                    style="background-color: #e9ecef;" readonly placeholder="Syncing Tabel...">
                            </div>
                        </div>
                    </div>

                    <div id="parameterSection">
                        <hr class="my-2">
                        <div class="row">
                            <?php
                            // [DIPERBAIKI] Memisahkan Vibrasi menjadi DE dan NDE
                            $allInputs = [
                                ["vibrasi_de", "Vibrasi Bearing DE", "number"],
                                ["vibrasi_nde", "Vibrasi Bearing NDE", "number"],
                                ["temp_de", "Temp. Bearing DE", "number"],
                                ["temp_nde", "Temp. Bearing NDE", "number"],
                                ["suhu_ruang", "Suhu Ruangan", "number"],
                                ["beban_gen", "Beban Generator", "number"],
                                ["damper", "Opening Damper", "number"],
                                ["load_current", "Load Current", "number"],
                                ["bunyi", "Bunyi Motor", "select", ["GOOD", "FAIR", "POOR"]],
                                ["panel", "Panel Local", "select", ["GOOD", "FAIR", "POOR"]],
                                ["lengkap", "Kelengkapan", "select", ["GOOD", "FAIR", "POOR"]],
                                ["bersih", "Kebersihan", "select", ["GOOD", "FAIR", "POOR"]],
                                ["ground", "Grounding", "select", ["GOOD", "FAIR", "POOR"]],
                                ["regrease", "Regreasing", "select", ["BELUM", "SELESAI"]]
                            ];
                            foreach ($allInputs as $item): ?>
                                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                                    <div class="form-group mb-2">
                                        <label class="form-label-custom"><?= $item[1] ?></label>
                                        <?php if ($item[2] == "number"): ?>
                                            <input type="number" step="0.01" name="<?= $item[0] ?>"
                                                class="form-control form-control-sm border-secondary" placeholder="-">
                                        <?php else: ?>
                                            <select name="<?= $item[0] ?>"
                                                class="form-control form-control-sm border-secondary">
                                                <option value="" selected disabled>- Pilih -</option>
                                                <?php foreach ($item[3] as $opt): ?>
                                                    <option value="<?= $opt ?>"><?= $opt ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr id="dividerBawah" class="my-2">

                    <div class="row align-items-stretch">
                        <div class="col-md-6 d-flex flex-column">
                            <div class="form-group mb-2 d-flex flex-column flex-grow-1">
                                <label class="form-label-custom font-weight-bold">ACTION (KETERANGAN)</label>
                                <textarea name="action"
                                    class="form-control form-control-sm border-secondary flex-grow-1"
                                    style="min-height: 120px;"
                                    placeholder="Isi keterangan action di sini..."></textarea>
                            </div>

                            <button type="button" id="btnKirim"
                                class="btn btn-primary btn-sm btn-block shadow-sm font-weight-bold">
                                <i class="fas fa-paper-plane mr-1"></i> KIRIM DATA MONITORING
                            </button>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-0 d-flex flex-column h-100">
                                <label class="form-label-custom text-muted font-weight-bold">
                                    <i class="fas fa-terminal mr-1"></i> CONSOLE LOG STATUS
                                </label>
                                <div id="consoleStatus" class="border rounded p-2 bg-dark flex-grow-1">
                                    <div id="log-output">
                                        <div>> [SYSTEM] Dashboard Ready...</div>
                                        <div>> [STATUS] Connection Stable</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>