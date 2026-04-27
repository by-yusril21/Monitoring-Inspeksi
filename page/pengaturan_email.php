<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Panggil file konfigurasi global
require_once 'config/config.php';

// Format data URL dan Token untuk digunakan di Javascript
$unit_apis = [];
foreach ($SCRIPT_URLS as $kode_unit => $url) {
    $unit_apis[$kode_unit] = [
        'url' => $url,
        'token' => $API_TOKEN
    ];
}
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <div class="callout callout-info bg-white shadow-sm mb-4" style="border-left-color: #17a2b8;">
                <h5 class="text-info font-weight-bold"><i class="fas fa-info-circle mr-2"></i> Tentang Halaman Ini</h5>
                <p class="mb-0 text-dark">
                    Halaman ini digunakan untuk mengelola daftar penerima email notifikasi dari sistem automasi
                    maintenance.
                    Anda dapat menambahkan, mengedit, atau menghapus email personil untuk setiap unit PLTU.
                    Personil yang terdaftar dengan status <span class="badge badge-success">AKTIF</span> akan secara
                    otomatis menerima email laporan inspeksi harian dan peringatan alarm jadwal <em>regreasing</em>
                    motor.
                </p>
            </div>

            <div id="email-container" class="row">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEmail" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Tambah Email Notifikasi</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEmail">
                <div class="modal-body">
                    <input type="hidden" id="inputNo">
                    <input type="hidden" id="inputAction" value="add_email">

                    <div class="form-group">
                        <label>Target Unit</label>
                        <select class="form-control" id="inputUnit" required>
                            <option value="">-- Pilih Unit --</option>
                            <option value="C6KV">Unit C - 6kV</option>
                            <option value="C380">Unit C - 380V</option>
                            <option value="D6KV">Unit D - 6kV</option>
                            <option value="D380">Unit D - 380V</option>
                            <option value="UTILITY6KV">Utility - 6kV</option>
                            <option value="UTILITY380">Utility - 380V</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Personil</label>
                        <input type="text" class="form-control" id="inputNama" required placeholder="Cth: Ahmad Fulan">
                    </div>
                    <div class="form-group">
                        <label>Alamat Email</label>
                        <input type="email" class="form-control" id="inputEmail" required
                            placeholder="Cth: email@sig.id">
                    </div>
                    <div class="form-group" id="grupStatus" style="display:none;">
                        <label>Status Notifikasi</label>
                        <select class="form-control" id="inputStatus">
                            <option value="AKTIF">AKTIF</option>
                            <option value="NONAKTIF">NONAKTIF</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const configAPI = <?php echo json_encode($unit_apis); ?>;
    const listUnit = Object.keys(configAPI);

    function formatUnitName(unitKode) {
        const u = unitKode.toUpperCase();
        if (u === 'C6KV') return 'PLTU UNIT C MOTOR 6kV';
        if (u === 'C380' || u === 'C380V') return 'PLTU UNIT C MOTOR 380V';
        if (u === 'D6KV') return 'PLTU UNIT D MOTOR 6kV';
        if (u === 'D380' || u === 'D380V') return 'PLTU UNIT D MOTOR 380V';
        if (u === 'UTILITY6KV') return 'PLTU UNIT UTILITY MOTOR 6kV';
        if (u === 'UTILITY380') return 'PLTU UNIT UTILITY MOTOR 380V';
        return 'PLTU UNIT ' + unitKode;
    }

    document.addEventListener("DOMContentLoaded", function () {
        initKerangkaCard(); // 1. Buat kerangkanya dulu
        loadDataSemuaUnit(); // 2. Tembak request barengan (Paralel)
    });

    // FUNGSI 1: Membuat Kerangka HTML tiap unit dengan Overlay Loading
    function initKerangkaCard() {
        const container = document.getElementById('email-container');
        let htmlGrid = '';

        listUnit.forEach(unit => {
            htmlGrid += `
                <div class="col-lg-6 col-md-12">
                    <div class="card card-outline card-primary mb-4 card-unit shadow-sm" id="card-${unit}" style="height: calc(100% - 1.5rem); position: relative;">
                        <div class="overlay loading-overlay" style="background-color: rgba(255,255,255,0.85); z-index: 10; flex-direction: column;">
                            <i class="fas fa-sync-alt fa-spin fa-2x text-primary"></i>
                            <div class="mt-2 text-primary font-weight-bold" style="font-size: 13px;">Mengambil data...</div>
                        </div>

                        <div class="card-header bg-white">
                            <h3 class="card-title font-weight-bold text-dark mt-1">
                                <i class="fas fa-industry text-primary mr-2"></i> ${formatUnitName(unit)}
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered m-0 text-sm">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>Nama Personil</th>
                                            <th>Alamat Email</th>
                                            <th style="width: 80px;">Status</th>
                                            <th style="width: 120px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-${unit}">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
        container.innerHTML = htmlGrid;
    }

    // FUNGSI 2: Memicu proses ambil data untuk setiap unit (Tidak saling tunggu)
    function loadDataSemuaUnit() {
        listUnit.forEach(unit => {
            fetchDataPerUnit(unit);
        });
    }

    // FUNGSI 3: Ambil dan Render data spesifik per-Unit (Independen)
    async function fetchDataPerUnit(unit) {
        const tbody = document.getElementById(`tbody-${unit}`);
        const card = document.getElementById(`card-${unit}`);
        const loadingOverlay = card.querySelector('.loading-overlay');

        // Pastikan loading muncul (berguna kalau fungsi ini dipanggil untuk refresh 1 tabel saja)
        loadingOverlay.style.display = 'flex';

        if (!configAPI[unit] || configAPI[unit].url === "") {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">Gagal: URL API Kosong</td></tr>`;
            loadingOverlay.style.display = 'none';
            return;
        }

        const url = configAPI[unit].url;
        const token = configAPI[unit].token;

        try {
            const response = await fetch(`${url}?action=get_emails&token=${token}`);
            const result = await response.json();

            if (result.status === 'success') {
                const dataUnit = result.data || [];

                if (dataUnit.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">Data Kosong <button class="btn btn-xs btn-primary ml-2" onclick="bukaModalTambah('${unit}')"><i class="fas fa-plus"></i></button></td></tr>`;
                } else {
                    let htmlTabel = '';
                    dataUnit.forEach((item, index) => {
                        let badgeStatus = item.status === 'AKTIF' ? '<span class="badge badge-success">AKTIF</span>' : '<span class="badge badge-secondary">NONAKTIF</span>';
                        htmlTabel += `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${item.nama}</td>
                                <td>${item.email}</td>
                                <td class="text-center">${badgeStatus}</td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-primary mx-1" onclick="bukaModalTambah('${unit}')" title="Tambah Personil"><i class="fas fa-plus"></i></button>
                                    <button class="btn btn-xs btn-warning mx-1" onclick="bukaModalEdit('${item.no}', '${unit}', '${item.nama}', '${item.email}', '${item.status}')" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-xs btn-danger mx-1" onclick="hapusEmail('${item.no}', '${unit}', '${item.nama}')" title="Hapus"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>`;
                    });
                    tbody.innerHTML = htmlTabel;
                }
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">Gagal: ${result.message}</td></tr>`;
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">Gagal Terhubung ke Server Google</td></tr>`;
        }

        // Matikan loading saat tabel unit ini sudah selesai
        loadingOverlay.style.display = 'none';
    }

    // FUNGSI POST (Tambah, Edit, Hapus)
    async function kirimDataAppsScript(payloadData) {
        const targetUnit = payloadData.unit;
        const SCRIPT_URL = configAPI[targetUnit].url;
        payloadData.token = configAPI[targetUnit].token;

        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

        try {
            const response = await fetch(SCRIPT_URL, {
                method: 'POST',
                body: JSON.stringify(payloadData),
                headers: { 'Content-Type': 'text/plain;charset=utf-8' }
            });
            const result = await response.json();
            if (result.status === 'success') {
                Swal.fire('Berhasil!', result.message, 'success');
                $('#modalEmail').modal('hide');

                // KUNCI EFISIENSI: Hanya Refresh/Reload Unit yang datanya baru saja diubah!
                fetchDataPerUnit(targetUnit);
            } else {
                Swal.fire('Gagal!', result.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Gagal terhubung ke Google', 'error');
        }
    }

    // --- MANAJEMEN MODAL ---
    function bukaModalTambah(unitDefault = '') {
        document.getElementById('formEmail').reset();
        document.getElementById('inputAction').value = 'add_email';
        let selectUnit = document.getElementById('inputUnit');

        if (unitDefault !== '') {
            selectUnit.value = unitDefault;
            selectUnit.disabled = true;
        } else {
            selectUnit.value = '';
            selectUnit.disabled = false;
        }

        document.getElementById('grupStatus').style.display = 'none';
        $('#modalEmail').modal('show');
    }

    function bukaModalEdit(no, unit, nama, email, status) {
        document.getElementById('inputNo').value = no;
        document.getElementById('inputAction').value = 'update_email';
        let selectUnit = document.getElementById('inputUnit');
        selectUnit.value = unit;
        selectUnit.disabled = true;
        document.getElementById('inputNama').value = nama;
        document.getElementById('inputEmail').value = email;
        document.getElementById('inputStatus').value = status;
        document.getElementById('grupStatus').style.display = 'block';
        $('#modalEmail').modal('show');
    }

    document.getElementById('formEmail').addEventListener('submit', function (e) {
        e.preventDefault();
        document.getElementById('inputUnit').disabled = false;
        kirimDataAppsScript({
            action: document.getElementById('inputAction').value,
            no: document.getElementById('inputNo').value,
            unit: document.getElementById('inputUnit').value,
            nama: document.getElementById('inputNama').value,
            email: document.getElementById('inputEmail').value,
            status: document.getElementById('inputStatus').value
        });
    });

    function hapusEmail(no, unit, nama) {
        Swal.fire({
            title: 'Hapus?', text: `Yakin menghapus email ${nama} dari unit ${unit}?`, icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) kirimDataAppsScript({ action: 'delete_email', no: no, unit: unit });
        });
    }
</script>