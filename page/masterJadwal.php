<?php
// Pastikan tidak ada spasi sebelum tag <?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'config/database.php';

$API_TOKEN = "SemenTonasa2026";
$SCRIPT_URLS = ["C6KV" => "", "C380" => "", "D6KV" => "", "D380" => "", "UTILITY6KV" => "", "UTILITY380" => ""];

// Konfigurasi Master 6 Unit (Agar bisa dilooping di HTML)
$units_config = [
    'C6KV' => ['title' => 'PLTU UNIT C - MOTOR 6KV', 'color' => 'info', 'data' => []],
    'C380' => ['title' => 'PLTU UNIT C - MOTOR 380V', 'color' => 'info', 'data' => []],
    'UTILITY6KV' => ['title' => 'PLTU UNIT UTILITY - MOTOR 6KV', 'color' => 'primary', 'data' => []],
    'D6KV' => ['title' => 'PLTU UNIT D - MOTOR 6KV', 'color' => 'success', 'data' => []],
    'D380' => ['title' => 'PLTU UNIT D - MOTOR 380V', 'color' => 'success', 'data' => []],
    'UTILITY380' => ['title' => 'PLTU UNIT UTILITY - MOTOR 380V', 'color' => 'primary', 'data' => []]
];

if (isset($conn)) {
    $query = "SELECT setting_key, setting_value FROM settings";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $key = strtolower(trim($row['setting_key']));
            $val = trim($row['setting_value']);

            if ($key == 'api_token')
                $API_TOKEN = $val;
            else if (strpos($key, 'script_url_') === 0) {
                $unit_code = strtoupper(str_replace('script_url_', '', $key));
                if (isset($SCRIPT_URLS[$unit_code]))
                    $SCRIPT_URLS[$unit_code] = $val;
            }

            // Mengisi Daftar Motor ke Array Otomatis
            if (strpos($key, 'regreasing_filter_') === 0) {
                $unit_code = strtoupper(str_replace('regreasing_filter_', '', $key));
                if (isset($units_config[$unit_code])) {
                    $units_config[$unit_code]['data'] = array_values(array_filter(array_map('trim', explode(",", $val))));
                }
            }
        }
    }
}
?>

<style>
    .table-master-slim th,
    .table-master-slim td {
        vertical-align: middle !important;
        padding: 0.3rem 0.5rem !important;
        font-size: 13px !important;
    }

    .table-master-slim th {
        background-color: #f4f6f9;
        color: #333;
        font-size: 12px !important;
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
        border-radius: 0.25rem;
    }
</style>

<div class="content-wrapper">
    <section class="content-header pt-3 pb-2">
        <div class="container-fluid">
            <h1 class="m-0 font-weight-bold" style="font-size: 1.5rem;">Master Jadwal Regreasing</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="mb-3">
                <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-sm"
                    onclick="loadDataMaster()"><i class="fas fa-sync-alt mr-1"></i> Refresh Data API</button>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?php foreach (['C6KV', 'C380', 'UTILITY6KV'] as $uCode):
                        $unit = $units_config[$uCode]; ?>
                        <div class="card card-outline card-<?php echo $unit['color']; ?> mb-3 shadow-sm position-relative">
                            <div class="loading-overlay d-none"><i
                                    class="fas fa-circle-notch fa-spin fa-2x text-<?php echo $unit['color']; ?>"></i></div>
                            <div class="card-header bg-white py-2">
                                <h3 class="card-title font-weight-bold text-dark mt-1" style="font-size: 14px;"><i
                                        class="fas fa-server text-<?php echo $unit['color']; ?> mr-1"></i>
                                    <?php echo $unit['title']; ?></h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered m-0 table-sm table-master-slim">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:40px;">NO</th>
                                                <th>NAMA MOTOR</th>
                                                <th class="text-center" style="width:130px;">TANGGAL AWAL</th>
                                                <th class="text-center" style="width:70px;">AKSI</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($unit['data'])): ?>
                                                <tr>
                                                    <td colspan='4' class='text-center text-muted'>Belum ada data</td>
                                                </tr>
                                            <?php else:
                                                $no = 1;
                                                foreach ($unit['data'] as $motor): ?>
                                                    <tr class="data-master-row" data-motor="<?php echo htmlspecialchars($motor); ?>"
                                                        data-unit="<?php echo $uCode; ?>">
                                                        <td class="text-center text-muted"><?php echo $no++; ?></td>
                                                        <td class="font-weight-bold"><?php echo htmlspecialchars($motor); ?></td>
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
                    <?php endforeach; ?>
                </div>

                <div class="col-md-6">
                    <?php foreach (['D6KV', 'D380', 'UTILITY380'] as $uCode):
                        $unit = $units_config[$uCode]; ?>
                        <div class="card card-outline card-<?php echo $unit['color']; ?> mb-3 shadow-sm position-relative">
                            <div class="loading-overlay d-none"><i
                                    class="fas fa-circle-notch fa-spin fa-2x text-<?php echo $unit['color']; ?>"></i></div>
                            <div class="card-header bg-white py-2">
                                <h3 class="card-title font-weight-bold text-dark mt-1" style="font-size: 14px;"><i
                                        class="fas fa-server text-<?php echo $unit['color']; ?> mr-1"></i>
                                    <?php echo $unit['title']; ?></h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered m-0 table-sm table-master-slim">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:40px;">NO</th>
                                                <th>NAMA MOTOR</th>
                                                <th class="text-center" style="width:130px;">TANGGAL AWAL</th>
                                                <th class="text-center" style="width:70px;">AKSI</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($unit['data'])): ?>
                                                <tr>
                                                    <td colspan='4' class='text-center text-muted'>Belum ada data</td>
                                                </tr>
                                            <?php else:
                                                $no = 1;
                                                foreach ($unit['data'] as $motor): ?>
                                                    <tr class="data-master-row" data-motor="<?php echo htmlspecialchars($motor); ?>"
                                                        data-unit="<?php echo $uCode; ?>">
                                                        <td class="text-center text-muted"><?php echo $no++; ?></td>
                                                        <td class="font-weight-bold"><?php echo htmlspecialchars($motor); ?></td>
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
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning py-2">
                <h6 class="modal-title font-weight-bold text-dark m-0"><i class="fas fa-calendar-alt mr-2"></i>Edit
                    Jadwal</h6>
                <button type="button" class="close text-dark" data-dismiss="modal"
                    style="padding: 0.5rem 1rem;"><span>&times;</span></button>
            </div>
            <div class="modal-body p-3">
                <form id="form-edit-jadwal">
                    <input type="hidden" id="edit-unit-code">
                    <div class="form-group mb-2">
                        <label class="text-sm mb-1">Nama Motor</label>
                        <input type="text" class="form-control form-control-sm font-weight-bold" id="edit-nama-motor"
                            readonly style="background-color: #f4f6f9;">
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-sm mb-1">Pilih Tanggal Baru</label>
                        <input type="date" class="form-control form-control-sm" id="edit-tanggal" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer p-2 justify-content-between bg-light">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning btn-sm font-weight-bold" id="btn-simpan-modal"
                    onclick="simpanDataEdit()"><i class="fas fa-save mr-1"></i> Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    const SCRIPT_URLS = <?php echo json_encode($SCRIPT_URLS); ?>;
    const TOKEN_RAHASIA = "<?php echo $API_TOKEN; ?>";

    document.addEventListener("DOMContentLoaded", () => loadDataMaster());

    async function loadDataMaster() {
        const loaders = document.querySelectorAll(".loading-overlay");
        loaders.forEach(l => l.classList.remove('d-none'));

        const unitCodes = ["C6KV", "C380", "UTILITY6KV", "D6KV", "D380", "UTILITY380"];

        const fetchPromises = unitCodes.map(async (unit) => {
            const urlAPI = SCRIPT_URLS[unit];
            const rows = document.querySelectorAll(`.data-master-row[data-unit="${unit}"]`);

            if (!urlAPI || urlAPI.trim() === "") {
                rows.forEach(r => {
                    r.querySelector('.tgl-awal').innerHTML = '<span class="text-danger" style="font-size:11px;">URL Kosong</span>';
                    r.querySelector('.btn-aksi').innerHTML = '-';
                });
                return;
            }

            try {
                const response = await fetch(`${urlAPI}?token=${TOKEN_RAHASIA}&action=get_master`);
                const result = await response.json();
                const masterMap = {};

                if (result.status === "success" && result.data) {
                    result.data.forEach(item => masterMap[item.namaMotor.trim()] = item.tanggalAwal);
                }

                rows.forEach(row => {
                    const motorName = row.getAttribute('data-motor').trim();
                    const tgl = masterMap[motorName];

                    if (tgl !== undefined) {
                        row.querySelector('.tgl-awal').innerHTML = tgl ? `<span class="font-weight-bold text-dark">${tgl}</span>` : `<span class="text-danger" style="font-size:11px;">Belum Diatur</span>`;
                        row.querySelector('.btn-aksi').innerHTML = `<button class="btn btn-primary btn-xs px-2 shadow-sm" onclick="bukaModalEdit('${motorName}', '${tgl || ''}', '${unit}')"><i class="fas fa-edit"></i></button>`;
                    } else {
                        row.querySelector('.tgl-awal').innerHTML = `<span class="text-warning" style="font-size:11px;"><i class="fas fa-exclamation-triangle"></i> Tidak ada DB Master</span>`;
                        row.querySelector('.btn-aksi').innerHTML = '-';
                    }
                });
            } catch (error) {
                rows.forEach(r => r.querySelector('.tgl-awal').innerHTML = '<span class="text-danger"><i class="fas fa-wifi"></i> Error</span>');
            }
        });

        await Promise.all(fetchPromises);
        loaders.forEach(l => l.classList.add('d-none'));
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
        if (!inputTanggal) return Swal.fire('Oops!', 'Pilih tanggal!', 'warning');

        const unitCode = document.getElementById("edit-unit-code").value;
        const btnSimpan = document.getElementById("btn-simpan-modal");
        const p = inputTanggal.split("-");

        const payload = {
            token: TOKEN_RAHASIA, action: "update_master",
            namaMotor: document.getElementById("edit-nama-motor").value,
            tanggalBaru: `${p[2]}/${p[1]}/${p[0]}`
        };

        btnSimpan.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>...';
        btnSimpan.disabled = true;

        try {
            const response = await fetch(SCRIPT_URLS[unitCode], { method: "POST", redirect: "follow", body: JSON.stringify(payload) });
            const responseText = await response.text();
            let result;
            try { result = JSON.parse(responseText); } catch (e) { result = { status: "success" }; }

            if (result.status === "success") {
                $('#modal-edit').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 2000, showConfirmButton: false });
                loadDataMaster();
            } else Swal.fire('Gagal!', result.message, 'error');
        } catch (error) {
            $('#modal-edit').modal('hide');
            Swal.fire({ icon: 'success', title: 'Tersimpan!', timer: 2000, showConfirmButton: false });
            setTimeout(() => loadDataMaster(), 2000);
        } finally {
            btnSimpan.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan';
            btnSimpan.disabled = false;
        }
    }
</script>