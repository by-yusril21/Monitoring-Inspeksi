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
            <div id="email-container">
                <div id="loading-global" class="text-center py-5">
                    <i class="fas fa-sync fa-spin fa-3x text-primary"></i>
                    <h5 class="mt-3">Mengambil data dari Server Database Unit...</h5>
                </div>
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

    // UPDATE 1: Ambil otomatis daftar unit dari config.php, tidak perlu hardcode lagi
    const listUnit = Object.keys(configAPI);
    let dataEmailGlobal = [];

    // UPDATE 2: Fungsi untuk mengubah kode unit menjadi nama lengkap
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
        loadDataSemuaUnit();
    });

    async function loadDataSemuaUnit() {
        document.getElementById('loading-global').style.display = 'block';
        dataEmailGlobal = [];

        const fetchPromises = listUnit.map(async (unit) => {
            if (configAPI[unit] && configAPI[unit].url !== "") {
                const url = configAPI[unit].url;
                const token = configAPI[unit].token;
                try {
                    const response = await fetch(`${url}?action=get_emails&token=${token}`);
                    const result = await response.json();
                    if (result.status === 'success') {
                        return { unit: unit, data: result.data, status: 'ok' };
                    }
                    return { unit: unit, status: 'error', msg: result.message };
                } catch (error) {
                    return { unit: unit, status: 'error', msg: 'Koneksi Gagal' };
                }
            }
            return { unit: unit, status: 'error', msg: 'URL Kosong' };
        });

        const results = await Promise.all(fetchPromises);
        results.forEach(res => {
            if (res.status === 'ok' && res.data) {
                res.data.forEach(item => { item.unit = res.unit; dataEmailGlobal.push(item); });
            }
        });

        document.getElementById('loading-global').style.display = 'none';
        renderTabelUnit(results);
    }

    function renderTabelUnit(statusFetch) {
        const container = document.getElementById('email-container');
        container.querySelectorAll('.row-unit').forEach(e => e.remove());

        let htmlGrid = '<div class="row row-unit">';

        listUnit.forEach(unit => {
            const dataUnitIni = dataEmailGlobal.filter(item => item.unit === unit);
            const statusUnit = statusFetch.find(s => s.unit === unit);
            let htmlTabel = '';

            if (statusUnit.status === 'error') {
                htmlTabel = `<tr><td colspan="5" class="text-center text-danger py-3">Gagal: ${statusUnit.msg}</td></tr>`;
            } else if (dataUnitIni.length === 0) {
                // Tombol tambah khusus jika unit masih kosong
                htmlTabel = `<tr><td colspan="5" class="text-center text-muted py-3">Data Kosong <button class="btn btn-xs btn-primary ml-2" onclick="bukaModalTambah('${unit}')"><i class="fas fa-plus"></i></button></td></tr>`;
            } else {
                dataUnitIni.forEach((item, index) => {
                    let badgeStatus = item.status === 'AKTIF' ? '<span class="badge badge-success">AKTIF</span>' : '<span class="badge badge-secondary">NONAKTIF</span>';
                    htmlTabel += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${item.nama}</td>
                            <td>${item.email}</td>
                            <td class="text-center">${badgeStatus}</td>
                            <td class="text-center">
                                <button class="btn btn-xs btn-primary mx-1" onclick="bukaModalTambah('${item.unit}')" title="Tambah Personil ke Unit ini"><i class="fas fa-plus"></i></button>
                                <button class="btn btn-xs btn-warning mx-1" onclick="bukaModalEdit('${item.no}', '${item.unit}', '${item.nama}', '${item.email}', '${item.status}')" title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-xs btn-danger mx-1" onclick="hapusEmail('${item.no}', '${item.unit}', '${item.nama}')" title="Hapus"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                });
            }

            htmlGrid += `
                <div class="col-lg-6 col-md-12">
                    <div class="card card-outline card-primary mb-4 card-unit shadow-sm" style="height: calc(100% - 1.5rem);">
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
                                    <tbody>${htmlTabel}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>`;
        });

        htmlGrid += '</div>';
        container.insertAdjacentHTML('beforeend', htmlGrid);
    }

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
                loadDataSemuaUnit();
            } else { Swal.fire('Gagal!', result.message, 'error'); }
        } catch (error) { Swal.fire('Error', 'Gagal terhubung ke Google', 'error'); }
    }

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
        // Buka disabled sebentar agar value ikut terkirim
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