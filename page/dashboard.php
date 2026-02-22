<?php
// WAJIB: Session Start di baris paling awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definisi kolom tabel
$columns = [
    "No", "TIMESTAMP", "EMAIL ADDRESS", "PILIH SALAH SATU", "SECTION NO", 
    "VIBRASI/GETARAN", "TEMP. BEARING DE", "TEMP. BEARING NDE", "SUHU RUANGAN", 
    "BEBAN GENERATOR", "OPENING DAMPER", "LOAD CURRENT", "BUNYI MOTOR", 
    "PANEL LOCAL", "KELENGKAPAN", "KEBERSIHAN", "GROUNDING", "REGREASING", "ACTIONS"
];
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

    html { scroll-behavior: smooth; }
    .content-wrapper { background-color: var(--bg-color) !important; overflow-x: hidden; }
    .section-full { height: 100vh; display: flex; flex-direction: column; padding-top: 10px; padding-bottom: 10px; }
    .px-custom-5 { padding-left: 7px !important; padding-right: 7px !important; }
    
    .table-responsive-vh { flex: 1; overflow-y: auto; border: 1px solid var(--border-color); background-color: var(--card-bg); }
    .card-custom { border-radius: 0; box-shadow: none !important; border: 1px solid var(--border-color) !important; display: flex; flex-direction: column; height: 100%; margin-bottom: 0; background-color: var(--card-bg); }
    .form-label-custom { font-size: 12px; font-weight: 600; color: var(--text-main); margin-bottom: 3px; display: block; }
    
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

    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #b3b3b3; }
    html, body { -ms-overflow-style: none; scrollbar-width: none; }
    body::-webkit-scrollbar { display: none; }
    #section-tabel, #section-gauge, #section-input { scroll-margin-top: 25px; }
</style>

<div class="content-wrapper">

    <section id="section-tabel" class="section-full">
        <div id="my-filter-source" class="d-none">
            <div class="d-inline-flex align-items-center">
                <select id="pilihUnit" class="form-control form-control-sm mr-2" style="width: 160px;">
                    <option value="">-- Pilih Unit --</option>
                    <option value="C6KV">PLTU UNIT C 6KV</option>
                    <option value="C380">PLTU UNIT C 380</option>
                    <option value="D6KV">PLTU UNIT D 6KV</option>
                    <option value="D380">PLTU UNIT D 380</option>
                    <option value="UTILITY">PLTU UNIT UTILITY</option>
                </select>
                <select id="pilihMotor" class="form-control form-control-sm mr-2" style="width: 230px;" disabled>
                    <option value="">-- Pilih Motor --</option>
                </select>
                <button type="button" id="btnRefresh" class="btn btn-info btn-sm">Update</button>
            </div>
        </div>

        <div class="container-fluid px-custom-5 h-100">
            <div class="card card-custom">
                <div class="card-header py-2 bg-white d-flex justify-content-between align-items-center">
                    <h3 id="label-title" class="m-0">DATABASE MONITORING MOTOR</h3>
                </div>
                <div class="card-body p-0 flex-fill d-flex flex-column">
                    <div class="table-responsive-vh">
                        <table id="example1" class="table table-bordered table-striped table-hover table-sm text-nowrap m-0">
                            <thead>
                                <tr>
                                    <?php foreach ($columns as $col): ?>
                                        <th class="<?= ($col == 'ACTIONS') ? 'text-center' : ''; ?>" 
                                            <?= ($col == 'No') ? 'style="width: 50px;"' : ''; ?>>
                                            <?= $col ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody></tbody>
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

                        <div class="gauge-row">

                            <div class="thermo-wrapper">

                                <div class="thermo-item">
                                    <div class="thermo-title">Bearing DE</div>
                                    <div id="thermo-de"></div>
                                    <div class="data-display" id="val-de">-- °C</div>
                                    <div id="time-temp-de" class="gauge-timestamp"><i class="far fa-clock"></i> -</div>
                                </div>

                                <div class="thermo-item">
                                    <div class="thermo-title">Bearing NDE</div>
                                    <div id="thermo-nde"></div>
                                    <div class="data-display" id="val-nde">-- °C</div>
                                    <div id="time-temp-nde" class="gauge-timestamp"><i class="far fa-clock"></i> -</div>
                                </div>

                                <div class="thermo-item">
                                    <div class="thermo-title">Suhu Ruangan</div>
                                    <div id="thermo-winding"></div>
                                    <div class="data-display" id="val-winding">-- °C</div>
                                    <div id="time-suhu-ruang" class="gauge-timestamp"><i class="far fa-clock"></i> -</div>
                                </div>
                            </div>
                        </div>
                        <div class="gauge-row">
                            <div class="gauge-card">
                                <div class="gauge-header">Vibrasi / Getaran</div>
                                <div id="gauge-vibrasi" class="gauge-chart-container"></div>
                                <div id="time-vibrasi" class="gauge-timestamp"><i class="far fa-clock"></i> -</div>
                            </div>
                            
                            <div class="gauge-card">
                                <div class="gauge-header">Beban Generator</div>
                                <div id="gauge-beban-gen" class="gauge-chart-container"></div>
                                <div id="time-beban-gen" class="gauge-timestamp"><i class="far fa-clock"></i> -</div>
                            </div>

                        </div>

                        <div class="gauge-row">

                            <div class="gauge-card">
                                <div class="gauge-header">Opening Damper</div>
                                <div id="gauge-damper" class="gauge-chart-container"></div>
                                <div id="time-damper" class="gauge-timestamp"><i class="far fa-clock"></i> -</div>
                            </div>
                            
                            <div class="gauge-card">
                                <div class="gauge-header">Load Current</div>
                                <div id="gauge-load-current" class="gauge-chart-container"></div>
                                <div id="time-load-current" class="gauge-timestamp"><i class="far fa-clock"></i> -</div>
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
                                <th>Tanggal Update</th>
                                <th>Diupdate Oleh</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="cond-label">Bunyi Motor</td>
                                <td id="date-bunyi" class="cond-date">--/--/----</td>
                                <td id="updater-bunyi" class="cond-name">--</td>
                                <td style="text-align: center;"><span id="stat-bunyi" class="status-badge status-good"></span></td>
                            </tr>
                            <tr>
                                <td class="cond-label">Panel Local</td>
                                <td id="date-panel" class="cond-date">--/--/----</td>
                                <td id="updater-panel" class="cond-name">--</td>
                                <td style="text-align: center;"><span id="stat-panel" class="status-badge status-fair"></span></td>
                            </tr>
                            <tr>
                                <td class="cond-label">Kelengkapan</td>
                                <td id="date-lengkap" class="cond-date">--/--/----</td>
                                <td id="updater-lengkap" class="cond-name">--</td>
                                <td style="text-align: center;"><span id="stat-lengkap" class="status-badge status-good"></span></td>
                            </tr>
                            <tr>
                                <td class="cond-label">Kebersihan</td>
                                <td id="date-bersih" class="cond-date">--/--/----</td>
                                <td id="updater-bersih" class="cond-name">--</td>
                                <td style="text-align: center;"><span id="stat-bersih" class="status-badge status-poor"></span></td>
                            </tr>
                            <tr>
                                <td class="cond-label">Grounding</td>
                                <td id="date-ground" class="cond-date">--/--/----</td>
                                <td id="updater-ground" class="cond-name">--</td>
                                <td style="text-align: center;"><span id="stat-ground" class="status-badge status-good"></span></td>
                            </tr>
                        </tbody>
                    </table>

                <div class="condition-divider" style="margin: 15px 0 10px 0;"></div>

                    <div class="gauge-dashboard-title" style="font-size: 0.85rem; border: none; text-align: left; margin-bottom: 5px;">
                        <i class="fas fa-oil-can mr-2 text-info"></i> Jadwal Pemeliharaan
                    </div>

                    <table class="condition-table regreasing-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Tgl Terakhir</th>
                                <th>Jadwal Next</th>
                                <th>Sisa Waktu</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="cond-label">Regreasing</td>
                                <td id="date-regrease-last" class="cond-date">--/--/----</td>
                                <td id="date-regrease-next" class="cond-date">--/--/----</td>
                                <td id="time-left-regrease" class="cond-date" style="font-weight: bold; color: #d9534f;">-- Hari</td>
                                <td style="text-align: center;"><span id="stat-regrease" class="status-badge status-done">-</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="gauge-timestamp mt-auto">
                        <i class="far fa-clock"></i> Last Inspection: <span id="time-kondisi">-</span>
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
                        <i class="fas fa-plus-circle mr-1 text-primary"></i> INPUT DATA MONITORING
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
                                <select name="pilih_salah_satu" id="pilihTipe" class="form-control form-control-sm border-secondary font-weight-bold">
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
                                <input type="text" name="section_no" id="inputSectionNo" class="form-control form-control-sm border-secondary font-weight-bold" style="background-color: #e9ecef;" readonly placeholder="Syncing Tabel...">
                            </div>
                        </div> 
                    </div>

                    <div id="parameterSection">
                        <hr class="my-2">
                        <div class="row">
                        <?php 
                        $allInputs = [
                            ["vibrasi", "Vibrasi/Getaran", "number"], ["temp_de", "Temp. Bearing DE", "number"],
                            ["temp_nde", "Temp. Bearing NDE", "number"], ["suhu_ruang", "Suhu Ruangan", "number"],
                            ["beban_gen", "Beban Generator", "number"], ["damper", "Opening Damper", "number"],
                            ["load_current", "Load Current", "number"],
                            ["bunyi", "Bunyi Motor", "select", ["GOOD", "FAIR", "POOR"]],
                            ["panel", "Panel Local", "select", ["GOOD", "FAIR", "POOR"]], 
                            ["lengkap", "Kelengkapan", "select", ["GOOD", "FAIR", "POOR"]],
                            ["bersih", "Kebersihan", "select", ["GOOD", "FAIR", "POOR"]], 
                            ["ground", "Grounding", "select", ["GOOD", "FAIR", "POOR"]],
                            ["regrease", "Regreasing", "select", ["BELUM", "SELESAI"]]
                        ];
                        foreach($allInputs as $item): ?>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                            <div class="form-group mb-2">
                                <label class="form-label-custom"><?= $item[1] ?></label>
                                <?php if($item[2] == "number"): ?>
                                    <input type="number" step="0.01" name="<?= $item[0] ?>" class="form-control form-control-sm border-secondary" placeholder="-">
                                <?php else: ?>
                                    <select name="<?= $item[0] ?>" class="form-control form-control-sm border-secondary">
                                        <option value="" selected disabled>- Pilih -</option>
                                        <?php foreach($item[3] as $opt): ?>
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
                                <textarea name="action" class="form-control form-control-sm border-secondary flex-grow-1" style="min-height: 120px;" placeholder="Isi keterangan action di sini..."></textarea>
                            </div>
                            
                            <button type="button" id="btnKirim" class="btn btn-primary btn-sm btn-block shadow-sm font-weight-bold">
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
