<?php
// WAJIB: Session Start di baris paling awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Catatan: Array $columns ini bisa dibiarkan saja jika dipakai di tempat lain...


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

    /* Styling Tambahan untuk Unified Card (Kotak Utama) */
    .unified-dashboard-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #e9ecef;
    }

    /* Styling Khusus untuk Card Kondisi Motor */
    .status-card-modern {
        background: #ffffff;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .status-card-modern:hover {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .border-left-good {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-fair {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-poor {
        border-left: 4px solid #dc3545 !important;
    }

    .border-left-empty {
        border-left: 4px solid #6c757d !important;
    }

    .icon-wrapper-cond {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #f4f6f9;
        color: #495057;
        font-size: 15px;
    }

    /* Penyesuaian tinggi Gauge Card agar presisi & simetris di dalam Grid Baru */
    .modern-gauge-card {
        width: 100% !important;
        margin: 0 !important;
        height: 100%;
    }
</style>

<div class="content-wrapper">

    <section id="section-tabel" class="vh-100 d-flex flex-column py-3">
        <div class="container-fluid px-3 h-100">
            <div class="card h-100 mb-0 shadow-sm">

                <div class=" card-body p-2 d-flex flex-column" id="area-tabel"
                    style="opacity: 0; transition: opacity 0.5s ease-in-out;">
                    <span id="nama-user-login"
                        style="display: none;"><?php echo htmlspecialchars($username_login ?? 'User'); ?></span>
                    <span id="judul-1-pdf"
                        style="display: none;"><?php echo htmlspecialchars($pdf_data['pdf_judul_1'] ?? 'DOKUMEN RANGKUMAN DATA PMC SCHEDULE BULANAN MOTOR 6kV DAN 380V'); ?></span>
                    <span id="judul-2-pdf"
                        style="display: none;"><?php echo htmlspecialchars($pdf_data['pdf_judul_2'] ?? 'PT Semen Tonasa - Electrical of Power Plant Elins Maintenance'); ?></span>
                    <span id="logo-base64-pdf"
                        style="display: none;"><?php echo htmlspecialchars($pdf_data['pdf_logo_base64'] ?? ''); ?></span>

                    <table id="example1" class="table table-bordered table-hover table-sm text-center w-100">
                        <thead class="align-middle">
                            <tr>
                                <th class="align-middle" rowspan="3">No</th>
                                <th class="align-middle" rowspan="3">Date</th>
                                <th class="align-middle" rowspan="3">Update By</th>
                                <th class="align-middle" rowspan="3">Aksi</th>
                                <th class="align-middle" rowspan="3">Section<br>No</th>
                                <th class="align-middle text-center" colspan="8">Vibrasi (mm/s)</th>
                                <th class="align-middle text-center" colspan="3">Temp (°C)</th>
                                <th class="align-middle text-center" colspan="3">Load Current (A)</th>
                                <th class="align-middle" rowspan="3">Load Generetor<br>(MW)</th>
                                <th class="align-middle" rowspan="3">Opening<br>Damper (%)</th>
                                <th class="align-middle" rowspan="3">Bunyi<br>Motor</th>
                                <th class="align-middle" rowspan="3">Kondisi<br>Panel</th>
                                <th class="align-middle" rowspan="3">Kelengkapan<br>Motor</th>
                                <th class="align-middle" rowspan="3">Kebersihan<br>Motor</th>
                                <th class="align-middle" rowspan="3">Grounding<br>Motor</th>
                                <th class="align-middle" rowspan="3">Regreasing<br>Bearing</th>
                                <th class="align-middle" rowspan="3">Action</th>
                            </tr>
                            <tr>
                                <th class="align-middle text-center" colspan="4">DE</th>
                                <th class="align-middle text-center" colspan="4">NDE</th>
                                <th class="align-middle" rowspan="2">DE</th>
                                <th class="align-middle" rowspan="2">NDE</th>
                                <th class="align-middle" rowspan="2">Ruang</th>
                                <th class="align-middle" rowspan="2">R</th>
                                <th class="align-middle" rowspan="2">S</th>
                                <th class="align-middle" rowspan="2">T</th>
                            </tr>
                            <tr>
                                <th class="align-middle">H</th>
                                <th class="align-middle">V</th>
                                <th class="align-middle">Ax</th>
                                <th class="align-middle">gE</th>
                                <th class="align-middle">H</th>
                                <th class="align-middle">V</th>
                                <th class="align-middle">Ax</th>
                                <th class="align-middle">gE</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </section>

    <section id="section-gauge" class="section-full">
        <div class="container-fluid h-100 py-3"
            style="max-width: 1366px; margin: 0 auto; overflow-y: auto; overflow-x: hidden;">

            <div class="unified-dashboard-card p-3">

                <div class="row">

                    <div class="col-xl-6 col-lg-12 border-right pr-xl-3 mb-2">

                        <div class="modern-thermo-container mb-2">

                            <div class="modern-thermo-card" style="padding: 10px;">
                                <div class="th-header" style="margin-bottom: 0px;">
                                    <div class="th-icon"><i class="fas fa-cogs"></i></div>
                                    <div class="th-title-group">
                                        <div class="th-title" style="font-size: 0.8rem;">Bearing DE</div>
                                        <div class="th-subtitle" style="font-size: 0.65rem;"><span
                                                class="th-dot green"></span> Temp</div>
                                    </div>
                                </div>
                                <div class="th-body" style="padding: 5px 0;">
                                    <div id="thermo-de" class="th-svg"></div>
                                    <div id="val-de" class="th-value" style="font-size: 1rem; margin-top: 5px;">0.0 °C
                                    </div>
                                </div>
                                <div id="status-box-de" class="th-footer d-flex justify-content-center"
                                    style="padding: 6px; font-size: 0.7rem;">
                                    <div id="time-temp-de" class="th-time font-weight-bold text-muted">--/--/----</div>
                                </div>
                            </div>

                            <div class="modern-thermo-card" style="padding: 10px;">
                                <div class="th-header" style="margin-bottom: 0px;">
                                    <div class="th-icon"><i class="fas fa-cogs"></i></div>
                                    <div class="th-title-group">
                                        <div class="th-title" style="font-size: 0.8rem;">Bearing NDE</div>
                                        <div class="th-subtitle" style="font-size: 0.65rem;"><span
                                                class="th-dot green"></span> Temp</div>
                                    </div>
                                </div>
                                <div class="th-body" style="padding: 5px 0;">
                                    <div id="thermo-nde" class="th-svg"></div>
                                    <div id="val-nde" class="th-value" style="font-size: 1rem; margin-top: 5px;">0.0 °C
                                    </div>
                                </div>
                                <div id="status-box-nde" class="th-footer d-flex justify-content-center"
                                    style="padding: 6px; font-size: 0.7rem;">
                                    <div id="time-temp-nde" class="th-time font-weight-bold text-muted">--/--/----</div>
                                </div>
                            </div>

                            <div class="modern-thermo-card" style="padding: 10px;">
                                <div class="th-header" style="margin-bottom: 0px;">
                                    <div class="th-icon" style="color: #3498db;"><i class="fas fa-thermometer-half"></i>
                                    </div>
                                    <div class="th-title-group">
                                        <div class="th-title" style="font-size: 0.8rem;">Suhu Ruangan</div>
                                        <div class="th-subtitle" style="font-size: 0.65rem;"><span
                                                class="th-dot blue"></span> Temp</div>
                                    </div>
                                </div>
                                <div class="th-body" style="padding: 5px 0;">
                                    <div id="thermo-winding" class="th-svg"></div>
                                    <div id="val-winding" class="th-value" style="font-size: 1rem; margin-top: 5px;">0.0
                                        °C</div>
                                </div>
                                <div id="status-box-winding" class="th-footer d-flex justify-content-center"
                                    style="padding: 6px; font-size: 0.7rem;">
                                    <div id="time-suhu-ruang" class="th-time font-weight-bold text-muted">--/--/----
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modern-vibe-card mb-2"
                            style="border: none; box-shadow: none; background: transparent; padding: 0;">
                            <div class="vibe-row-container"
                                style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">

                                <div class="vibe-section">
                                    <div class="vibe-indicator"></div>
                                    <div class="vibe-content" style="width: 100%;">
                                        <div class="vibe-info-row mb-1">
                                            <span class="vibe-label" style="font-size: 0.75rem;">VIBRASI BRG. DE <small
                                                    class="text-muted">(mm/s)</small></span>
                                        </div>
                                        <div class="vibe-tube-row flex-column align-items-start">
                                            <div class="d-flex align-items-center mb-1 w-100">
                                                <span
                                                    style="width: 25px; font-size: 10px; font-weight: bold; color: #555;">H</span>
                                                <div id="vibe-de-h-container" class="vibe-tube-wrapper"></div>
                                            </div>
                                            <div class="d-flex align-items-center mb-1 w-100">
                                                <span
                                                    style="width: 25px; font-size: 10px; font-weight: bold; color: #555;">V</span>
                                                <div id="vibe-de-v-container" class="vibe-tube-wrapper"></div>
                                            </div>
                                            <div class="d-flex align-items-center mb-1 w-100">
                                                <span
                                                    style="width: 25px; font-size: 10px; font-weight: bold; color: #555;">Ax</span>
                                                <div id="vibe-de-ax-container" class="vibe-tube-wrapper"></div>
                                            </div>
                                            <div class="d-flex align-items-center mb-1 w-100">
                                                <span
                                                    style="width: 25px; font-size: 10px; font-weight: bold; color: #555;">gE</span>
                                                <div id="vibe-de-ge-container" class="vibe-tube-wrapper"></div>
                                            </div>
                                        </div>
                                        <div class="vibe-time-individual mt-1" style="font-size: 0.65rem;">
                                            <i class="far fa-clock"></i> <span
                                                id="ext-time-vibe-de-h-container">--/--/----</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="vibe-vertical-divider" style="margin: 0 10px;"></div>

                                <div class="vibe-section">
                                    <div class="vibe-indicator"></div>
                                    <div class="vibe-content" style="width: 100%;">
                                        <div class="vibe-info-row mb-1">
                                            <span class="vibe-label" style="font-size: 0.75rem;">VIBRASI BRG. NDE <small
                                                    class="text-muted">(mm/s)</small></span>
                                        </div>
                                        <div class="vibe-tube-row flex-column align-items-start">
                                            <div class="d-flex align-items-center mb-1 w-100">
                                                <span
                                                    style="width: 25px; font-size: 10px; font-weight: bold; color: #555;">H</span>
                                                <div id="vibe-nde-h-container" class="vibe-tube-wrapper"></div>
                                            </div>
                                            <div class="d-flex align-items-center mb-1 w-100">
                                                <span
                                                    style="width: 25px; font-size: 10px; font-weight: bold; color: #555;">V</span>
                                                <div id="vibe-nde-v-container" class="vibe-tube-wrapper"></div>
                                            </div>
                                            <div class="d-flex align-items-center mb-1 w-100">
                                                <span
                                                    style="width: 25px; font-size: 10px; font-weight: bold; color: #555;">Ax</span>
                                                <div id="vibe-nde-ax-container" class="vibe-tube-wrapper"></div>
                                            </div>
                                            <div class="d-flex align-items-center mb-1 w-100">
                                                <span
                                                    style="width: 25px; font-size: 10px; font-weight: bold; color: #555;">gE</span>
                                                <div id="vibe-nde-ge-container" class="vibe-tube-wrapper"></div>
                                            </div>
                                        </div>
                                        <div class="vibe-time-individual mt-1" style="font-size: 0.65rem;">
                                            <i class="far fa-clock"></i> <span
                                                id="ext-time-vibe-nde-h-container">--/--/----</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="col-xl-6 col-lg-12 pl-xl-3 mb-2">

                        <div class="row justify-content-center">

                            <div class="col-sm-6 col-6 mb-3">
                                <div class="modern-gauge-card" style="padding: 10px;">
                                    <div class="gauge-card-header mb-0"><span style="font-size: 0.75rem;">Load
                                            Gen</span></div>
                                    <div id="gauge-beban-gen" class="modern-gauge-chart" style="min-height: 120px;">
                                    </div>
                                    <div class="gauge-card-footer mt-0"><span id="time-beban-gen"
                                            style="font-size: 0.65rem;">Data Kosong</span></div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-6 mb-3">
                                <div class="modern-gauge-card" style="padding: 10px;">
                                    <div class="gauge-card-header mb-0"><span style="font-size: 0.75rem;">Damper</span>
                                    </div>
                                    <div id="gauge-damper" class="modern-gauge-chart" style="min-height: 120px;"></div>
                                    <div class="gauge-card-footer mt-0"><span id="time-damper"
                                            style="font-size: 0.65rem;">Data Kosong</span></div>
                                </div>
                            </div>

                            <div class="col-md-4 col-4 mb-2">
                                <div class="modern-gauge-card px-1" style="padding: 5px;">
                                    <div class="gauge-card-header mb-0"><span style="font-size: 0.7rem;">Current
                                            (R)</span></div>
                                    <div id="gauge-load-current-r" class="modern-gauge-chart"
                                        style="min-height: 100px;">
                                    </div>
                                    <div class="gauge-card-footer mt-0"><span id="time-load-current-r"
                                            style="font-size: 0.6rem;">Data Kosong</span></div>
                                </div>
                            </div>

                            <div class="col-md-4 col-4 mb-2">
                                <div class="modern-gauge-card px-1" style="padding: 5px;">
                                    <div class="gauge-card-header mb-0"><span style="font-size: 0.7rem;">Current
                                            (S)</span></div>
                                    <div id="gauge-load-current-s" class="modern-gauge-chart"
                                        style="min-height: 100px;">
                                    </div>
                                    <div class="gauge-card-footer mt-0"><span id="time-load-current-s"
                                            style="font-size: 0.6rem;">Data Kosong</span></div>
                                </div>
                            </div>

                            <div class="col-md-4 col-4 mb-2">
                                <div class="modern-gauge-card px-1" style="padding: 5px;">
                                    <div class="gauge-card-header mb-0"><span style="font-size: 0.7rem;">Current
                                            (T)</span></div>
                                    <div id="gauge-load-current-t" class="modern-gauge-chart"
                                        style="min-height: 100px;">
                                    </div>
                                    <div class="gauge-card-footer mt-0"><span id="time-load-current-t"
                                            style="font-size: 0.6rem;">Data Kosong</span></div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="row mt-1 pt-2 border-top">
                    <div class="col-12">

                        <div class="row mb-2">
                            <div class="col-xl col-lg-4 col-md-6 col-12 mb-2">
                                <div class="status-card-modern p-2 rounded h-100" id="card-bunyi">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-wrapper-cond mr-2"
                                            style="width: 30px; height: 30px; font-size: 13px;"><i
                                                class="fas fa-server"></i></div>
                                        <div class="flex-grow-1">
                                            <div class="text-muted font-weight-bold text-uppercase"
                                                style="font-size: 0.65rem;">Bunyi Motor</div>
                                            <div class="text-secondary mt-1" style="font-size: 0.6rem;"><i
                                                    class="far fa-clock"></i> <span id="date-bunyi">--/--/----</span>
                                            </div>
                                        </div>
                                        <div><span id="stat-bunyi" class="badge badge-secondary px-2 py-1"
                                                style="font-size: 0.65rem;">--</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl col-lg-4 col-md-6 col-12 mb-2">
                                <div class="status-card-modern p-2 rounded h-100" id="card-panel">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-wrapper-cond mr-2"
                                            style="width: 30px; height: 30px; font-size: 13px;"><i
                                                class="fas fa-server"></i></div>
                                        <div class="flex-grow-1">
                                            <div class="text-muted font-weight-bold text-uppercase"
                                                style="font-size: 0.65rem;">Kondisi Panel Local</div>
                                            <div class="text-secondary mt-1" style="font-size: 0.6rem;"><i
                                                    class="far fa-clock"></i> <span id="date-panel">--/--/----</span>
                                            </div>
                                        </div>
                                        <div><span id="stat-panel" class="badge badge-secondary px-2 py-1"
                                                style="font-size: 0.65rem;">--</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl col-lg-4 col-md-6 col-12 mb-2">
                                <div class="status-card-modern p-2 rounded h-100" id="card-lengkap">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-wrapper-cond mr-2"
                                            style="width: 30px; height: 30px; font-size: 13px;"><i
                                                class="fas fa-server"></i></div>
                                        <div class="flex-grow-1">
                                            <div class="text-muted font-weight-bold text-uppercase"
                                                style="font-size: 0.65rem;">Kelengkapan Motor</div>
                                            <div class="text-secondary mt-1" style="font-size: 0.6rem;"><i
                                                    class="far fa-clock"></i> <span id="date-lengkap">--/--/----</span>
                                            </div>
                                        </div>
                                        <div><span id="stat-lengkap" class="badge badge-secondary px-2 py-1"
                                                style="font-size: 0.65rem;">--</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl col-lg-6 col-md-6 col-12 mb-2">
                                <div class="status-card-modern p-2 rounded h-100" id="card-bersih">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-wrapper-cond mr-2"
                                            style="width: 30px; height: 30px; font-size: 13px;"><i
                                                class="fas fa-server"></i></div>
                                        <div class="flex-grow-1">
                                            <div class="text-muted font-weight-bold text-uppercase"
                                                style="font-size: 0.65rem;">Kebersihan Motor</div>
                                            <div class="text-secondary mt-1" style="font-size: 0.6rem;"><i
                                                    class="far fa-clock"></i> <span id="date-bersih">--/--/----</span>
                                            </div>
                                        </div>
                                        <div><span id="stat-bersih" class="badge badge-secondary px-2 py-1"
                                                style="font-size: 0.65rem;">--</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl col-lg-6 col-md-12 col-12 mb-2">
                                <div class="status-card-modern p-2 rounded h-100" id="card-ground">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-wrapper-cond mr-2"
                                            style="width: 30px; height: 30px; font-size: 13px;"><i
                                                class="fas fa-server"></i></div>
                                        <div class="flex-grow-1">
                                            <div class="text-muted font-weight-bold text-uppercase"
                                                style="font-size: 0.65rem;">Grounding Motor</div>
                                            <div class="text-secondary mt-1" style="font-size: 0.6rem;"><i
                                                    class="far fa-clock"></i> <span id="date-ground">--/--/----</span>
                                            </div>
                                        </div>
                                        <div><span id="stat-ground" class="badge badge-secondary px-2 py-1"
                                                style="font-size: 0.65rem;">--</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-start p-2"
                            style="background-color: #fffdf5; border: 1px solid #ffeeba; border-left: 4px solid #ffc107; border-radius: 6px;">
                            <div class="mr-2 mt-1"><i class="fas fa-tools text-warning fa-lg"></i></div>
                            <div class="flex-grow-1" style="max-height: 80px; overflow-y: auto;">
                                <p id="teks-action"
                                    style="margin: 0 0 4px 0; font-size: 0.85rem; color: #343a40; font-weight: 500; font-style: italic;">
                                    "Belum ada data action yang direkam."
                                </p>
                                <div style="font-size: 0.7rem; color: #856404; text-align: right;">
                                    <i class="far fa-calendar-alt mr-1"></i> <span id="tanggal-action">--/--/----</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <section id="section-input" class="section-full">
        <div class="container-fluid px-custom-5 h-100 py-3">
            <div class="card card-custom bg-white shadow-sm">

                <form id="formInputMotor" class="p-3">
                    <input type="hidden" id="userLoggedIn" value="<?php
                    $user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
                    $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
                    echo $user . ($role ? ' / ' . $role : '');
                    ?>">

                    <div class="row mb-1">
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
                            // 1. Data Vibrasi
                            $vibInputs = [
                                ["vibrasi_de_h", "Vib DE (H)"],
                                ["vibrasi_de_v", "Vib DE (V)"],
                                ["vibrasi_de_ax", "Vib DE (Ax)"],
                                ["vibrasi_de_ge", "Vib DE (gE)"],
                                ["vibrasi_nde_h", "Vib NDE (H)"],
                                ["vibrasi_nde_v", "Vib NDE (V)"],
                                ["vibrasi_nde_ax", "Vib NDE (Ax)"],
                                ["vibrasi_nde_ge", "Vib NDE (gE)"]
                            ];
                            foreach ($vibInputs as $item): ?>
                                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                                    <div class="form-group mb-2">
                                        <label class="form-label-custom text-info"><?= $item[1] ?></label>
                                        <input type="number" step="0.01" name="<?= $item[0] ?>"
                                            class="form-control form-control-sm border-secondary" placeholder="-">
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php
                            // 2. Data Elektrikal & Suhu
                            $elecInputs = [
                                ["temp_de", "Temp DE (°C)"],
                                ["temp_nde", "Temp NDE (°C)"],
                                ["suhu_ruang", "Suhu Ruang (°C)"],
                                ["load_current_r", "Current R (A)"],
                                ["load_current_s", "Current S (A)"],
                                ["load_current_t", "Current T (A)"],
                                ["beban_gen", "Beban Gen (MW)"],
                                ["damper", "Damper (%)"]
                            ];
                            foreach ($elecInputs as $item): ?>
                                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                                    <div class="form-group mb-2">
                                        <label class="form-label-custom text-success"><?= $item[1] ?></label>
                                        <input type="number" step="0.01" name="<?= $item[0] ?>"
                                            class="form-control form-control-sm border-secondary" placeholder="-">
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php
                            // 3. Data Kondisi Fisik
                            $fisikInputs = [
                                ["bunyi", "Bunyi Motor"],
                                ["panel", "Panel Local"],
                                ["lengkap", "Kelengkapan"],
                                ["bersih", "Kebersihan"],
                                ["ground", "Grounding"]
                            ];
                            foreach ($fisikInputs as $item): ?>
                                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                                    <div class="form-group mb-2">
                                        <label class="form-label-custom text-danger"><?= $item[1] ?></label>
                                        <select name="<?= $item[0] ?>"
                                            class="form-control form-control-sm border-secondary">
                                            <option value="" selected disabled>- Pilih -</option>
                                            <option value="GOOD">GOOD</option>
                                            <option value="FAIR">FAIR</option>
                                            <option value="POOR">POOR</option>
                                        </select>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                                <div class="form-group mb-2">
                                    <label class="form-label-custom text-warning">Regreasing</label>
                                    <select name="regrease" class="form-control form-control-sm border-secondary">
                                        <option value="" selected disabled>- Pilih -</option>
                                        <option value="BELUM">BELUM</option>
                                        <option value="SELESAI">SELESAI</option>
                                    </select>
                                </div>
                            </div>

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