<?php
// Pastikan tidak ada spasi sebelum tag <?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'config/database.php';

// Nilai default jika database kosong
$API_TOKEN = "SemenTonasa2026";
$SCRIPT_URLS = ["C6KV" => "", "C380" => "", "D6KV" => "", "D380" => "", "UTILITY6KV" => "", "UTILITY380" => ""];

// Konfigurasi Detail Masing-masing Unit
$units_config = [
    'C6KV' => ['title' => 'PLTU UNIT C - MOTOR 6KV', 'color' => 'info', 'data' => []],
    'C380' => ['title' => 'PLTU UNIT C - MOTOR 380V', 'color' => 'info', 'data' => []],
    'UTILITY6KV' => ['title' => 'PLTU UNIT UTILITY - MOTOR 6KV', 'color' => 'primary', 'data' => []],
    'D6KV' => ['title' => 'PLTU UNIT D - MOTOR 6KV', 'color' => 'success', 'data' => []],
    'D380' => ['title' => 'PLTU UNIT D - MOTOR 380V', 'color' => 'success', 'data' => []],
    'UTILITY380' => ['title' => 'PLTU UNIT UTILITY - MOTOR 380V', 'color' => 'primary', 'data' => []]
];

// Menangkap parameter unit dari klik tombol di jadwal-regreasing
$filter_unit = isset($_GET['unit']) ? strtoupper(trim($_GET['unit'])) : null;

// Mengambil Data dari Database MySQL
if (isset($conn)) {
    $query = "SELECT setting_key, setting_value FROM settings";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $key = strtolower(trim($row['setting_key']));
            $val = trim($row['setting_value']);

            if ($key == 'api_token') {
                $API_TOKEN = $val;
            }

            if ($filter_unit && $key == 'script_url_' . strtolower($filter_unit)) {
                $SCRIPT_URLS[$filter_unit] = $val;
            }

            if ($filter_unit && $key == 'regreasing_filter_' . strtolower($filter_unit)) {
                $units_config[$filter_unit]['data'] = array_values(array_filter(array_map('trim', explode(",", $val))));
            }
        }
    }
}
?>

<style>
    /* ========================================================= */
    /* KUSTOMISASI TABEL RAMPING */
    /* ========================================================= */
    .table-master-slim th,
    .table-master-slim td {
        vertical-align: middle !important;
        padding: 0.6rem 0.6rem !important;
        font-size: 13px !important;
    }

    .table-master-slim th {
        background-color: #f4f6f9;
        color: #333;
        font-size: 12px !important;
        text-transform: uppercase;
    }

    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.85);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10;
        border-radius: 15px;
    }

    .table-animated tbody tr {
        animation: slideInRow 0.4s ease-out forwards;
        opacity: 0;
    }

    @keyframes slideInRow {
        from {
            opacity: 0;
            transform: translateY(15px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        0% {
            opacity: 0;
            transform: translateY(30px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .center-wrapper {
        min-height: calc(100vh - 120px);
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        padding-top: 2rem;
        padding-bottom: 3rem;
    }
</style>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid center-wrapper">

            <?php if (!$filter_unit || !isset($units_config[$filter_unit])): ?>
                <div class="col-md-6 col-lg-5 animate-fade-in-up text-center mt-5">
                    <div class="alert alert-warning shadow-sm py-4" style="border-radius: 15px;">
                        <i class="fas fa-exclamation-triangle fa-4x mb-3 text-white"></i>
                        <h4 class="font-weight-bold text-white">Oops! Unit Belum Dipilih</h4>
                        <p class="text-white" style="font-size: 15px;">Silakan kembali ke halaman <b>Jadwal Regreasing</b>
                            dan klik tombol <span class="badge badge-dark">Edit Master</span> pada unit yang ingin Anda
                            ubah.</p>
                        <a href="index.php?page=jadwal-regreasing" class="btn btn-dark mt-3 font-weight-bold shadow-sm px-4"
                            style="border-radius: 20px;">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Jadwal
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php $unit = $units_config[$filter_unit]; ?>

                <div class="col-md-11 col-lg-9 animate-fade-in-up">

                    <div class="card card-outline card-<?php echo $unit['color']; ?> shadow-lg position-relative"
                        style="border-radius: 15px; border-top-width: 4px;">

                        <div class="loading-overlay d-none"><i
                                class="fas fa-circle-notch fa-spin fa-3x text-<?php echo $unit['color']; ?>"></i></div>

                        <div class="card-header bg-white"
                            style="padding: 15px 20px; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                            <h3 class="card-title font-weight-bold text-dark m-0 mt-1" style="font-size: 16px;">
                                <i class="fas fa-server text-<?php echo $unit['color']; ?> mr-2"></i>
                                <?php echo $unit['title']; ?>
                            </h3>

                            <div class="card-tools m-0">
                                <a href="index.php?page=jadwal-regreasing"
                                    class="btn btn-outline-secondary btn-sm font-weight-bold shadow-sm mr-1 px-3"
                                    style="border-radius: 20px;">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                                <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-sm px-3"
                                    style="border-radius: 20px;" onclick="loadDataMaster()">
                                    <i class="fas fa-sync-alt mr-1"></i> Muat Ulang
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive"
                                style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                                <table class="table table-hover table-bordered m-0 table-master-slim table-animated">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:60px;">NO</th>
                                            <th class="text-left" style="padding-left: 20px !important;">NAMA MOTOR</th>
                                            <th class="text-center" style="width:180px;">TANGGAL AWAL</th>
                                            <th class="text-center" style="width:100px;">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-animasi">
                                        <?php if (empty($unit['data'])): ?>
                                            <tr>
                                                <td colspan='4' class='text-center text-muted py-4'><i
                                                        class="fas fa-info-circle mb-2 fa-2x text-lightblue"></i><br>Belum ada
                                                    motor yang dicentang di pengaturan untuk unit ini.</td>
                                            </tr>
                                        <?php else:
                                            $no = 1;
                                            foreach ($unit['data'] as $motor): ?>
                                                <tr class="data-master-row" data-motor="<?php echo htmlspecialchars($motor); ?>"
                                                    data-unit="<?php echo $filter_unit; ?>"
                                                    style="animation-delay: <?php echo $no * 0.05; ?>s;">
                                                    <td class="text-center text-muted">
                                                        <?php echo $no++; ?>
                                                    </td>

                                                    <td class="text-left font-weight-bold text-dark"
                                                        style="font-size: 14px !important; padding-left: 20px !important;">
                                                        <?php echo htmlspecialchars($motor); ?>
                                                    </td>

                                                    <td class="text-center tgl-awal"><i
                                                            class="fas fa-spinner fa-spin text-muted"></i></td>
                                                    <td class="text-center btn-aksi">-</td>
                                                </tr>
                                            <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endif; ?>

        </div>
    </section>
</div>

<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-warning py-3">
                <h6 class="modal-title font-weight-bold text-dark m-0"><i class="fas fa-calendar-alt mr-2"></i>Edit
                    Jadwal Awal</h6>
                <button type="button" class="close text-dark" data-dismiss="modal"
                    style="padding: 0.8rem 1rem;"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <form id="form-edit-jadwal">
                    <input type="hidden" id="edit-unit-code">
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1 text-muted">Nama Motor</label>
                        <input type="text" class="form-control font-weight-bold" id="edit-nama-motor" readonly
                            style="background-color: #f8f9fa; border: 1px dashed #ccc; border-radius: 8px;">
                    </div>
                    <div class="form-group mb-1">
                        <label class="text-sm mb-1 font-weight-bold text-dark">Pilih Tanggal Baru</label>
                        <input type="date" class="form-control" id="edit-tanggal" required
                            style="cursor: pointer; border-radius: 8px; border: 1px solid #ced4da;">
                    </div>
                </form>
            </div>
            <div class="modal-footer p-3 justify-content-between bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold" data-dismiss="modal"
                    style="border-radius: 20px; padding: 0.25rem 1rem;">Batal</button>
                <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-sm px-4"
                    id="btn-simpan-modal" onclick="simpanDataEdit()" style="border-radius: 20px;">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const SCRIPT_URL = "<?php echo isset($SCRIPT_URLS[$filter_unit]) ? $SCRIPT_URLS[$filter_unit] : ''; ?>";
    const TOKEN_RAHASIA = "<?php echo $API_TOKEN; ?>";
    const CURRENT_UNIT = "<?php echo $filter_unit; ?>";

    document.addEventListener("DOMContentLoaded", () => {
        if (CURRENT_UNIT) loadDataMaster();
    });

    async function loadDataMaster() {
        if (!CURRENT_UNIT) return;

        const loader = document.querySelector(".loading-overlay");
        const rows = document.querySelectorAll('.data-master-row');

        if (loader) loader.classList.remove('d-none');

        if (!SCRIPT_URL || SCRIPT_URL.trim() === "") {
            rows.forEach(r => {
                r.querySelector('.tgl-awal').innerHTML = '<span class="text-danger" style="font-size:12px;"><i class="fas fa-link"></i> URL API Kosong</span>';
                r.querySelector('.btn-aksi').innerHTML = '-';
            });
            if (loader) loader.classList.add('d-none');
            return;
        }

        try {
            const response = await fetch(`${SCRIPT_URL}?token=${TOKEN_RAHASIA}&action=get_master`);
            const result = await response.json();
            const masterMap = {};

            if (result.status === "success" && result.data) {
                result.data.forEach(item => masterMap[item.namaMotor.trim()] = item.tanggalAwal);
            }

            rows.forEach(row => {
                const motorName = row.getAttribute('data-motor').trim();
                const tgl = masterMap[motorName];

                if (tgl !== undefined) {
                    row.querySelector('.tgl-awal').innerHTML = tgl
                        ? `<span class="font-weight-bold text-primary" style="font-size:14px;">${tgl}</span>`
                        : `<span class="text-danger" style="font-size:12px;"><i class="fas fa-times-circle"></i> Belum Diatur</span>`;

                    row.querySelector('.btn-aksi').innerHTML = `<button class="btn btn-primary btn-sm shadow-sm" style="border-radius:6px;" onclick="bukaModalEdit('${motorName}', '${tgl || ''}', '${CURRENT_UNIT}')"><i class="fas fa-edit"></i> Edit</button>`;
                } else {
                    row.querySelector('.tgl-awal').innerHTML = `<span class="text-warning" style="font-size:12px;"><i class="fas fa-exclamation-triangle"></i> Tidak ada di DB Master</span>`;
                    row.querySelector('.btn-aksi').innerHTML = '-';
                }
            });
        } catch (error) {
            rows.forEach(r => r.querySelector('.tgl-awal').innerHTML = '<span class="text-danger"><i class="fas fa-wifi"></i> Error Koneksi</span>');
        } finally {
            const tbody = document.getElementById('tbody-animasi');
            if (tbody) {
                tbody.style.display = 'none';
                tbody.offsetHeight;
                tbody.style.display = '';
            }
            if (loader) loader.classList.add('d-none');
        }
    }

    function bukaModalEdit(namaMotor, tanggalSistem, unitCode) {
        document.getElementById("edit-nama-motor").value = namaMotor;
        document.getElementById("edit-unit-code").value = unitCode;

        let formatHtmlInput = "";
        if (tanggalSistem && tanggalSistem.includes("/")) {
            let p = tanggalSistem.split("/");
            if (p.length === 3) formatHtmlInput = `${p[2]}-${p[1]}-${p[0]}`;
        }
        document.getElementById("edit-tanggal").value = formatHtmlInput;
        $('#modal-edit').modal('show');
    }

    async function simpanDataEdit() {
        const inputTanggal = document.getElementById("edit-tanggal").value;
        if (!inputTanggal) return Swal.fire('Oops!', 'Pilih tanggal terlebih dahulu!', 'warning');

        const btnSimpan = document.getElementById("btn-simpan-modal");
        const p = inputTanggal.split("-");

        const payload = {
            token: TOKEN_RAHASIA,
            action: "update_master",
            namaMotor: document.getElementById("edit-nama-motor").value,
            tanggalBaru: `${p[2]}/${p[1]}/${p[0]}`
        };

        btnSimpan.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';
        btnSimpan.disabled = true;

        try {
            const response = await fetch(SCRIPT_URL, { method: "POST", redirect: "follow", body: JSON.stringify(payload) });
            const responseText = await response.text();
            let result;

            try { result = JSON.parse(responseText); }
            catch (e) { result = { status: "success" }; }

            if (result.status === "success") {
                if (typeof $ !== 'undefined') $('#modal-edit').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Jadwal berhasil diperbarui.', timer: 2000, showConfirmButton: false });
                loadDataMaster();
            } else {
                Swal.fire('Gagal!', result.message, 'error');
            }
        } catch (error) {
            if (typeof $ !== 'undefined') $('#modal-edit').modal('hide');
            Swal.fire({ icon: 'success', title: 'Tersimpan!', text: 'Sistem otomatis diperbarui.', timer: 2000, showConfirmButton: false });
            setTimeout(() => loadDataMaster(), 2000);
        } finally {
            btnSimpan.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan';
            btnSimpan.disabled = false;
        }
    }
</script>