<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    /* 1. Menyembunyikan Scrollbar Horizontal */
    .table-responsive::-webkit-scrollbar {
        display: none;
    }

    .table-responsive {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* 2. Sticky Column Menggunakan Class Khusus (.sticky-motor) */
    .sticky-motor {
        position: sticky !important;
        left: 0;
        z-index: 2;
        background-color: #ffffff !important;
        box-shadow: inset -2px 0 0 rgba(0, 0, 0, 0.1);
    }

    thead .sticky-motor {
        background-color: #17a2b8 !important;
        /* Warna cyan header */
        z-index: 3;
    }

    .table-hover tbody tr:hover .sticky-motor {
        background-color: #f4f6f9 !important;
        /* Warna abu-abu saat di-hover */
    }

    /* 3. Memangkas Padding & Rata Tengah Vertikal */
    .table-sticky-first th,
    .table-sticky-first td {
        padding-left: 1px !important;
        padding-right: 1px !important;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        vertical-align: middle !important;
        text-align: center;
        /* Default semua teks rata tengah */
    }

    /* 4. Rata kiri khusus untuk Nama Motor & Actions */
    .text-left-custom {
        text-align: left !important;
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-bold text-primary">Rekap Data Terbaru Lengkap</h1>
                    <p class="text-muted">Status ke-19 parameter aktual seluruh motor di lapangan.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid" id="rekap-container">
            <div class="text-center py-5">
                <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
                <p class="font-weight-bold">Mempersiapkan Tabel Data Master...</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        if (typeof window.dataMotor === 'undefined') {
            document.getElementById('rekap-container').innerHTML = `
                <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle mr-2"></i> Error: File config.js tidak terbaca.</div>`;
            return;
        }

        const units = Object.keys(window.dataMotor);
        const container = document.getElementById('rekap-container');
        container.innerHTML = "";

        // 1. GENERATE TABEL KOSONG UNTUK SETIAP UNIT (DENGAN HEADER 2 TINGKAT)
        units.forEach(unit => {
            const listMotor = window.dataMotor[unit];
            if (listMotor.length === 0) return;

            let cardHTML = `
            <div class="card card-outline card-info mb-4 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="card-header bg-white pt-3 pb-2">
                    <h3 class="card-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                        <i class="fas fa-server text-info mr-2"></i> PLTU UNIT ${unit}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info mr-2"><i class="fas fa-arrows-alt-h mr-1"></i> Geser ke kanan</span>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body p-0 position-relative">
                    <div id="loading_${unit}" class="overlay d-flex justify-content-center align-items-center" style="background: rgba(255,255,255,0.8); z-index: 10;">
                        <div class="text-center">
                            <i class="fas fa-sync fa-spin fa-2x text-info"></i>
                            <div class="mt-2 text-sm font-weight-bold text-muted">Menarik Data ${unit}...</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped m-0 text-sm text-nowrap table-sticky-first" id="table_${unit}">
                            <thead class="bg-info text-white">
                                <tr>
                                    <th rowspan="2" class="sticky-motor text-left-custom" style="min-width: 220px;">NAMA MOTOR</th>
                                    <th rowspan="2" style="min-width: 130px;">Date</th>
                                    <th rowspan="2" style="min-width: 150px;">Update By</th>
                                    <th rowspan="2" style="min-width: 90px;">Aksi</th>
                                    <th rowspan="2" style="min-width: 90px;">Section<br>No</th>
                                    
                                    <th colspan="2" class="border-bottom-0">VIB(mm/s)</th>
                                    
                                    <th colspan="2" class="border-bottom-0">Temp°C</th>
                                    
                                    <th rowspan="2" style="min-width: 90px;">Load<br>Generator</th>
                                    <th rowspan="2" style="min-width: 90px;">Opening<br>Damper</th>
                                    <th rowspan="2" style="min-width: 80px;">Current</th>
                                    <th rowspan="2" style="min-width: 100px;">Temp<br>Ruang</th>
                                    <th rowspan="2" style="min-width: 100px;">Bunyi<br>Motor</th>
                                    <th rowspan="2" style="min-width: 100px;">Kondisi<br>Panel</th>
                                    <th rowspan="2" style="min-width: 100px;">Kelengkapan</th>
                                    <th rowspan="2" style="min-width: 100px;">Kebersihan</th>
                                    <th rowspan="2" style="min-width: 100px;">Grounding</th>
                                    <th rowspan="2" style="min-width: 50px;">Regreasing</th>
                                    <th rowspan="2" style="min-width: 500px;" class="text-left-custom">Action</th>
                                </tr>
                                <tr>
                                    <th style="min-width: 20px; font-weight: normal;">DE</th>
                                    <th style="min-width: 20px; font-weight: normal;">NDE</th>
                                    <th style="min-width: 20px; font-weight: normal;">DE</th>
                                    <th style="min-width: 20px; font-weight: normal;">NDE</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_${unit}">`;

            listMotor.forEach(motor => {
                const safeId = motor.replace(/[^a-zA-Z0-9]/g, '_');
                cardHTML += `
                                <tr id="row_${unit}_${safeId}">
                                    <td class="font-weight-bold text-primary sticky-motor text-left-custom">${motor}</td>
                                    <td colspan="19" class="text-muted text-center">
                                        <i class="fas fa-ellipsis-h text-black-50"></i> Menunggu sinkronisasi...
                                    </td>
                                </tr>`;
            });

            cardHTML += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>`;

            container.innerHTML += cardHTML;
        });

        // 2. FUNGSI UNTUK MENARIK DATA
        async function fetchUnitData(unit) {
            const loadingEl = document.getElementById(`loading_${unit}`);

            try {
                const targetUrl = `api/fetch_latest_data.php?unit=${unit}`;
                const response = await fetch(targetUrl);
                const result = await response.json();

                if (loadingEl) {
                    loadingEl.classList.remove('d-flex');
                    loadingEl.classList.add('d-none');
                }

                if (result.status === 'success') {
                    const dataArray = result.data;
                    const listMotor = window.dataMotor[unit];

                    listMotor.forEach(motorName => {
                        const safeId = motorName.replace(/[^a-zA-Z0-9]/g, '_');
                        const rowElement = document.getElementById(`row_${unit}_${safeId}`);

                        const motorData = dataArray.find(item => item['NAMA MOTOR'] === motorName);

                        if (motorData && rowElement) {
                            const waktu = motorData['TIMESTAMP'] || '-';
                            const email = motorData['EMAIL ADDRESS'] || '-';
                            const status = motorData['STATUS'] || '-';
                            const section = motorData['SECTION NO'] || '-';
                            const vibDE = motorData['VIB DE (MM/S)'] || '-';
                            const vibNDE = motorData['VIB NDE (MM/S)'] || '-';
                            const tempDE = motorData['TEMP DE (°C)'] || '-';
                            const tempNDE = motorData['TEMP NDE (°C)'] || '-';
                            const beban = motorData['BEBAN GEN'] || '-';
                            const damper = motorData['DAMPER (%)'] || '-';
                            const arus = motorData['ARUS (A)'] || '-';
                            const suhuRuang = motorData['SUHU RUANG/VENTILASI'] || '-';
                            const bunyi = motorData['BUNYI BEARING'] || '-';
                            const panel = motorData['PANEL LOKAL'] || '-';
                            const kelengkapan = motorData['KELENGKAPAN'] || '-';
                            const kebersihan = motorData['KEBERSIHAN'] || '-';
                            const grounding = motorData['GROUNDING'] || '-';
                            const regreasing = motorData['REGREASING'] || '-';
                            const actions = motorData['ACTIONS'] || '-';

                            let badgeStatus = `<span class="badge badge-secondary px-2 py-1">${status}</span>`;
                            const statusUpper = status.toUpperCase();
                            if (statusUpper.includes('NORMAL')) badgeStatus = `<span class="badge badge-success px-2 py-1">${status}</span>`;
                            else if (statusUpper.includes('WARNING')) badgeStatus = `<span class="badge badge-warning px-2 py-1">${status}</span>`;
                            else if (statusUpper.includes('DANGER') || statusUpper.includes('ALARM')) badgeStatus = `<span class="badge badge-danger px-2 py-1">${status}</span>`;

                            // Suntikkan 20 Kolom HTML dengan class yang disesuaikan
                            rowElement.innerHTML = `
                                <td class="font-weight-bold text-dark sticky-motor text-left-custom">${motorName}</td>
                                <td class="text-muted">${waktu}</td>
                                <td>${email}</td>
                                <td>${badgeStatus}</td>
                                <td>${section}</td>
                                <td class="font-weight-bold text-primary">${vibDE}</td>
                                <td class="font-weight-bold text-danger">${vibNDE}</td>
                                <td>${tempDE}</td>
                                <td>${tempNDE}</td>
                                <td>${beban}</td>
                                <td>${damper}</td>
                                <td>${arus}</td>
                                <td>${suhuRuang}</td>
                                <td>${bunyi}</td>
                                <td>${panel}</td>
                                <td>${kelengkapan}</td>
                                <td>${kebersihan}</td>
                                <td>${grounding}</td>
                                <td><span class="border rounded px-2 py-1 bg-light">${regreasing}</span></td>
                                <td class="text-left-custom" style="white-space: normal; min-width: 250px;">${actions}</td>
                            `;
                        } else if (!motorData && rowElement) {
                            rowElement.innerHTML = `
                                <td class="font-weight-bold text-muted sticky-motor text-left-custom">${motorName}</td>
                                <td colspan="19" class="text-center text-warning text-sm">
                                    <i class="fas fa-info-circle mr-1"></i> Belum ada riwayat pengukuran
                                </td>
                            `;
                        }
                    });

                } else if (result.status === 'empty') {
                    const tbody = document.getElementById(`tbody_${unit}`);
                    if (tbody) {
                        tbody.innerHTML = `<tr><td colspan="20" class="text-center text-muted py-4"><i class="fas fa-folder-open fa-2x mb-2"></i><br>Database Master untuk unit ini masih kosong.</td></tr>`;
                    }
                } else {
                    const tbody = document.getElementById(`tbody_${unit}`);
                    if (tbody) {
                        tbody.innerHTML = `<tr><td colspan="20" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>Gagal memuat: ${result.message}</td></tr>`;
                    }
                }

            } catch (error) {
                console.error(`Error fetch Unit ${unit}:`, error);
                if (loadingEl) {
                    loadingEl.classList.remove('d-flex');
                    loadingEl.classList.add('d-none');
                }
                const tbody = document.getElementById(`tbody_${unit}`);
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="20" class="text-center text-danger py-4"><i class="fas fa-wifi fa-2x mb-2"></i><br>Gagal terhubung ke server/API.</td></tr>`;
                }
            }
        }

        units.forEach(unit => {
            fetchUnitData(unit);
        });

    });
</script>