<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    /* 1. Menampilkan Scrollbar Horizontal & Menyembunyikan Vertikal */
    .table-responsive {
        /* Pengecualian di Firefox: Tampilkan scrollbar tipis */
        scrollbar-width: thin !important;
    }

    /* Pengaturan untuk browser WebKit (Chrome, Safari, Edge, Opera) */
    .table-responsive::-webkit-scrollbar {
        width: 0px !important;
        /* HILANGKAN scrollbar vertikal (Atas-Bawah) */
        height: 8px !important;
        /* TAMPILKAN scrollbar horizontal (Kiri-Kanan) */
    }

    /* Mempercantik tampilan scrollbar horizontal */
    .table-responsive::-webkit-scrollbar-track {
        background: #f4f6f9;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* 2. TEMA ABU-ABU HEADER TABEL */
    .table-sticky-first thead th {
        color: #000000 !important;
        border-color: #282929 !important;
        vertical-align: middle !important;
        text-align: center;
    }

    .table-sticky-first thead tr:first-child th {
        background-color: #56a5b4 !important;
        font-weight: bold !important;
    }

    .table-sticky-first thead tr:nth-child(2) th {
        background-color: #75bfce !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }

    /* 3. Sticky Column (Kolom Nama Motor) - UNTUK LAYAR BESAR */
    .sticky-motor {
        position: sticky !important;
        left: 0;
        z-index: 2;
        background-color: #f9f9f9 !important;
        box-shadow: inset -2px 0 0 rgba(0, 0, 0, 0.1);
    }

    thead .sticky-motor {
        background-color: #ebebeb !important;
        color: #000000 !important;
        z-index: 3;
    }

    .table-hover tbody tr:hover .sticky-motor {
        background-color: #c7c7c7 !important;
    }

    /* Matikan Sticky di Layar HP (Maksimal Lebar 768px) */
    @media (max-width: 768px) {
        .sticky-motor {
            position: static !important;
            box-shadow: none !important;
        }

        thead .sticky-motor {
            z-index: 1;
        }
    }

    /* 4. Memangkas Padding & Rata Tengah Vertikal */
    .table-sticky-first th,
    .table-sticky-first td {
        padding-left: 2px !important;
        padding-right: 2px !important;
        padding-top: 4px !important;
        padding-bottom: 4px !important;
        vertical-align: middle !important;
        text-align: center;
    }

    /* 5. Rata kiri khusus untuk Nama Motor & Actions */
    .text-left-custom {
        text-align: left !important;
    }
</style>

<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid" id="rekap-container">
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

        // 1. GENERATE TABEL KOSONG UNTUK SETIAP UNIT DENGAN OVERLAY LOADING
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
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped m-0 text-sm text-nowrap table-sticky-first" id="table_${unit}">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="sticky-motor text-left-custom" style="min-width: 220px;">NAMA MOTOR</th>
                                    <th rowspan="2" style="min-width: 130px;">Date</th>
                                    <th rowspan="2" style="min-width: 150px;">Update By</th>
                                    <th rowspan="2" style="min-width: 80px;">Aksi</th>
                                    <th rowspan="2" style="min-width: 80px;">Section<br>No</th>
                                    
                                    <th colspan="2" class="border-bottom-0">Vibrasi mm/s</th>
                                    <th colspan="2" class="border-bottom-0">Temp°C</th>
                                    
                                    <th rowspan="2" style="min-width: 80px;">Load<br>Generator</th>
                                    <th rowspan="2" style="min-width: 80px;">Opening<br>Damper</th>
                                    <th rowspan="2" style="min-width: 80px;">Current</th>
                                    <th rowspan="2" style="min-width: 80px;">Temp<br>Ruang</th>
                                    <th rowspan="2" style="min-width: 80px;">Bunyi<br>Motor</th>
                                    <th rowspan="2" style="min-width: 80px;">Kondisi<br>Panel</th>
                                    <th rowspan="2" style="min-width: 80px;">Kelengkapan</th>
                                    <th rowspan="2" style="min-width: 80px;">Kebersihan</th>
                                    <th rowspan="2" style="min-width: 80px;">Grounding</th>
                                    <th rowspan="2" style="min-width: 80px;">Regreasing</th>
                                    <th rowspan="2" style="min-width: 350px;" class="text-left-custom">Action</th>
                                </tr>
                                <tr>
                                    <th style="min-width: 40px; font-weight: normal;">DE</th>
                                    <th style="min-width: 40px; font-weight: normal;">NDE</th>
                                    <th style="min-width: 40px; font-weight: normal;">DE</th>
                                    <th style="min-width: 40px; font-weight: normal;">NDE</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_${unit}">`;

            listMotor.forEach(motor => {
                const safeId = motor.replace(/[^a-zA-Z0-9]/g, '_');
                cardHTML += `
                                <tr id="row_${unit}_${safeId}">
                                    <td class="font-weight-bold text-dark sticky-motor text-left-custom">${motor}</td>
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
                
                <div class="overlay" id="loading_${unit}" style="flex-direction: column; background-color: rgba(255,255,255,0.85);">
                    <i class="fas fa-sync-alt fa-spin fa-3x text-info"></i>
                    <div class="mt-3 text-info font-weight-bold" style="letter-spacing: 1px;">Mengambil Data...</div>
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

                // Hapus efek loading setelah data berhasil ditarik
                if (loadingEl) {
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
                            const regreasing = motorData['REGREASING'] || '-';
                            const actions = motorData['ACTIONS'] || '-';

                            // --- [FUNGSI BARU] Pewarnaan Kondisi GOOD, FAIR, POOR ---
                            const formatCond = (val) => {
                                if (!val || val === '-') return '-';
                                const v = val.toUpperCase();
                                if (v === 'GOOD') return `<span class="text-success font-weight-bold">${val}</span>`;
                                if (v === 'FAIR') return `<span class="text-warning font-weight-bold">${val}</span>`;
                                if (v === 'POOR') return `<span class="text-danger font-weight-bold">${val}</span>`;
                                return val; // Jika isinya selain 3 kata di atas, biarkan warna biasa
                            };

                            const bunyi = formatCond(motorData['BUNYI BEARING'] || '-');
                            const panel = formatCond(motorData['PANEL LOKAL'] || '-');
                            const kelengkapan = formatCond(motorData['KELENGKAPAN'] || '-');
                            const kebersihan = formatCond(motorData['KEBERSIHAN'] || '-');
                            const grounding = formatCond(motorData['GROUNDING'] || '-');
                            // --------------------------------------------------------

                            // Teks warna untuk status (Normal / Danger)
                            let badgeStatus = `<span>${status}</span>`;
                            const statusUpper = status.toUpperCase();
                            if (statusUpper.includes('NORMAL')) badgeStatus = `<span class="text-success font-weight-bold">${status}</span>`;
                            else if (statusUpper.includes('WARNING')) badgeStatus = `<span class="text-warning font-weight-bold">${status}</span>`;
                            else if (statusUpper.includes('DANGER') || statusUpper.includes('ALARM')) badgeStatus = `<span class="text-danger font-weight-bold">${status}</span>`;

                            rowElement.innerHTML = `
                                <td class="font-weight-bold text-dark sticky-motor text-left-custom">${motorName}</td>
                                <td class="text-muted">${waktu}</td>
                                <td>${email}</td>
                                <td>${badgeStatus}</td>
                                <td>${section}</td>
                                <td>${vibDE}</td>
                                <td>${vibNDE}</td>
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

        setInterval(() => {
            console.log("Melakukan auto-refresh data...");
            units.forEach(unit => {
                fetchUnitData(unit);
            });
        }, 300000);

    });
</script>