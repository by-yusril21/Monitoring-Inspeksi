<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Panggil Koneksi Database untuk Mengambil Setting PDF
require 'config/database.php';
$username_login = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';

$pdf_judul_1 = 'DOKUMEN RANGKUMAN DATA PMC SCHEDULE BULANAN MOTOR 6kV DAN 380V';
$pdf_judul_2 = 'PT Semen Tonasa - Electrical of Power Plant Elins Maintenance';
$pdf_logo_base64 = '';

$query_settings = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('pdf_judul_1', 'pdf_judul_2', 'pdf_logo_base64')";
$hasil_settings = mysqli_query($conn, $query_settings);
if ($hasil_settings) {
    while ($row = mysqli_fetch_assoc($hasil_settings)) {
        if ($row['setting_key'] == 'pdf_judul_1' && !empty($row['setting_value']))
            $pdf_judul_1 = $row['setting_value'];
        if ($row['setting_key'] == 'pdf_judul_2' && !empty($row['setting_value']))
            $pdf_judul_2 = $row['setting_value'];
        if ($row['setting_key'] == 'pdf_logo_base64')
            $pdf_logo_base64 = $row['setting_value'];
    }
}
?>

<style>
    /* 1. Menampilkan Scrollbar Horizontal & Menyembunyikan Vertikal */
    .table-responsive {
        scrollbar-width: thin !important;
    }

    .table-responsive::-webkit-scrollbar {
        width: 0px !important;
        height: 8px !important;
    }

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

    /* Header Baris 1, 2, dan 3 */
    .table-sticky-first thead tr:first-child th {
        background-color: #56a5b4 !important;
        font-weight: bold !important;
    }

    .table-sticky-first thead tr:nth-child(2) th {
        background-color: #75bfce !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }

    .table-sticky-first thead tr:nth-child(3) th {
        background-color: #8ed0de !important;
        font-size: 12px !important;
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
        <span id="nama-user-login" style="display: none;"><?php echo htmlspecialchars($username_login); ?></span>
        <span id="judul-1-pdf" style="display: none;"><?php echo htmlspecialchars($pdf_judul_1); ?></span>
        <span id="judul-2-pdf" style="display: none;"><?php echo htmlspecialchars($pdf_judul_2); ?></span>
        <span id="logo-base64-pdf" style="display: none;"><?php echo htmlspecialchars($pdf_logo_base64); ?></span>

        <div class="container-fluid" id="rekap-container"></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
    // =========================================================================
    // FUNGSI TRANSLATE NAMA UNIT
    // =========================================================================
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

    // =========================================================================
    // FUNGSI 1: EXPORT KE EXCEL 
    // =========================================================================
    function exportToExcel(tableId, fileName) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const cloneTable = table.cloneNode(true);
        let tableHTML = cloneTable.outerHTML;

        const colWidths = [
            200, 110, 140, 80, 60, // Motor, Date, Update By, Aksi, Section
            50, 50, 50, 50,        // Vib DE
            50, 50, 50, 50,        // Vib NDE
            60, 60, 60,            // Temp DE, NDE, Ruang
            50, 50, 50,            // Arus R, S, T
            70, 70,                // Beban, Damper
            80, 80, 80, 80, 80, 80,// Bunyi, Panel, Lengkap, Bersih, Ground, Regreasing
            250                    // Action
        ];

        let colgroup = '<colgroup>';
        colWidths.forEach(w => { colgroup += `<col style="width: ${w}px;">`; });
        colgroup += '</colgroup>';

        tableHTML = tableHTML.replace(/<table[^>]*>/i, (match) => match + colgroup);

        const template = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta charset="UTF-8">
                <style>
                    table { border-collapse: collapse; table-layout: fixed; }
                    th, td { border: 1px solid black; padding: 5px; text-align: center; vertical-align: middle; word-wrap: break-word; }
                    th { background-color: #56a5b4; color: black; font-weight: bold; }
                </style>
            </head>
            <body>
                ${tableHTML}
            </body>
            </html>`;

        const blob = new Blob(['\uFEFF' + template], { type: 'application/vnd.ms-excel' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');

        a.href = url;
        a.download = fileName ? fileName + '.xls' : 'Data_Rekap.xls';
        document.body.appendChild(a);
        a.click();

        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // =========================================================================
    // FUNGSI 2: EXPORT KE PDF
    // =========================================================================
    function exportToPDF(tableId, fileName, unitLengkap) {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            alert("Library PDF belum selesai dimuat, pastikan koneksi internet Anda aktif.");
            return;
        }

        const doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'cm', format: 'a4' });
        const table = document.getElementById(tableId);
        if (!table) return;
        const cloneTable = table.cloneNode(true);

        let judul1Text = document.getElementById("judul-1-pdf").innerText.trim();
        let judul2Text = document.getElementById("judul-2-pdf").innerText.trim();
        let logoBase64 = document.getElementById("logo-base64-pdf").innerText.trim();
        let currentUser = document.getElementById("nama-user-login").innerText.trim();

        let today = new Date();
        let dateString = ("0" + today.getDate()).slice(-2) + "/" + ("0" + (today.getMonth() + 1)).slice(-2) + "/" + today.getFullYear();
        let timeString = ("0" + today.getHours()).slice(-2) + ":" + ("0" + today.getMinutes()).slice(-2) + ":" + ("0" + today.getSeconds()).slice(-2);
        let infoString = "Tanggal unduh data : " + dateString + " " + timeString + " | Oleh : " + currentUser;
        let subHeaderString = "UNIT : " + unitLengkap;

        doc.setFontSize(10);
        doc.setFont("helvetica", "bold");
        doc.text(judul1Text, 0.5, 1.0);

        doc.setFontSize(9);
        doc.text(judul2Text, 0.5, 1.4);

        doc.setFontSize(8);
        doc.setFont("helvetica", "normal");
        doc.text(infoString, 0.5, 1.8);

        if (logoBase64 && logoBase64.indexOf("data:image") === 0) {
            doc.addImage(logoBase64, 'PNG', 27.5, 0.3, 1.6, 1.6);
        }

        doc.setLineWidth(0.05);
        doc.setDrawColor(205, 164, 52);
        doc.line(0.5, 2.1, 29.2, 2.1);

        doc.setFontSize(8);
        doc.setFont("helvetica", "bold");
        doc.setTextColor(85, 85, 85);
        doc.text(subHeaderString, 0.5, 2.5);

        doc.autoTable({
            html: cloneTable,
            startY: 2.7,
            margin: { top: 1, right: 0.5, bottom: 1, left: 0.5 },
            styles: {
                fontSize: 4,
                valign: 'middle',
                halign: 'center',
                lineWidth: 0.01,
                lineColor: [0, 0, 0],
                textColor: [0, 0, 0],
                cellPadding: 0.1,
                overflow: 'linebreak'
            },
            headStyles: {
                fillColor: [86, 165, 180],
                textColor: [0, 0, 0],
                fontStyle: 'bold',
                fontSize: 4
            },
            columnStyles: {
                0: { cellWidth: 2.4 }, 1: { cellWidth: 1.2 }, 2: { cellWidth: 2 }, 3: { cellWidth: 1.2 },
                4: { cellWidth: 1.1 }, 5: { cellWidth: 0.5 }, 6: { cellWidth: 0.5 }, 7: { cellWidth: 0.5 },
                8: { cellWidth: 0.5 }, 9: { cellWidth: 0.5 }, 10: { cellWidth: 0.5 }, 11: { cellWidth: 0.5 },
                12: { cellWidth: 0.5 }, 13: { cellWidth: 0.5 }, 14: { cellWidth: 0.5 }, 15: { cellWidth: 0.7 },
                16: { cellWidth: 0.5 }, 17: { cellWidth: 0.5 }, 18: { cellWidth: 0.5 }, 19: { cellWidth: 1 },
                20: { cellWidth: 1 }, 21: { cellWidth: 1 }, 22: { cellWidth: 1 }, 23: { cellWidth: 1.2 },
                24: { cellWidth: 1 }, 25: { cellWidth: 1 }, 26: { cellWidth: 1 }, 27: { cellWidth: 5.5 }
            },
            theme: 'grid',
            didDrawPage: function (data) {
                if (logoBase64 && logoBase64.indexOf("data:image") === 0) {
                    doc.setGState(new doc.GState({ opacity: 0.1 }));
                    doc.addImage(logoBase64, 'PNG', 7.85, 3.5, 14, 14);
                    doc.setGState(new doc.GState({ opacity: 1.0 }));
                }
            }
        });

        doc.save((fileName ? fileName : `Data_Terbaru_Unit_${unitName}`) + '.pdf');
    }

    // =========================================================================
    // FUNGSI UTAMA FETCH API DATA
    // =========================================================================
    async function fetchUnitData(unit) {
        const loadingEl = document.getElementById(`loading_${unit}`);

        // Tampilkan efek loading saat data ditarik
        if (loadingEl) {
            loadingEl.classList.remove('d-none');
            loadingEl.classList.add('d-flex');
        }

        try {
            const targetUrl = `api/fetch_latest_data.php?unit=${unit}`;
            const response = await fetch(targetUrl);
            const result = await response.json();

            // Sembunyikan loading setelah data diterima
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

                        const vibDE_H = motorData['VIB DE H'] || '-';
                        const vibDE_V = motorData['VIB DE V'] || '-';
                        const vibDE_Ax = motorData['VIB DE Ax'] || motorData['VIB DE AX'] || '-';
                        const vibDE_gE = motorData['VIB DE gE'] || motorData['VIB DE GE'] || '-';

                        const vibNDE_H = motorData['VIB NDE H'] || '-';
                        const vibNDE_V = motorData['VIB NDE V'] || '-';
                        const vibNDE_Ax = motorData['VIB NDE Ax'] || motorData['VIB NDE AX'] || '-';
                        const vibNDE_gE = motorData['VIB NDE gE'] || motorData['VIB NDE GE'] || '-';

                        const tempDE = motorData['TEMP DE (°C)'] || '-';
                        const tempNDE = motorData['TEMP NDE (°C)'] || '-';
                        const suhuRuang = motorData['SUHU RUANG/VENTILASI'] || '-';

                        const arusR = motorData['ARUS R'] || '-';
                        const arusS = motorData['ARUS S'] || '-';
                        const arusT = motorData['ARUS T'] || '-';

                        const beban = motorData['BEBAN GEN'] || '-';
                        const damper = motorData['DAMPER (%)'] || '-';

                        const regreasing = motorData['REGREASING'] || '-';
                        const actions = motorData['ACTIONS'] || '-';

                        const formatCond = (val) => {
                            if (!val || val === '-') return '-';
                            const v = val.toUpperCase();
                            if (v === 'GOOD') return `<span class="text-success font-weight-bold">${val}</span>`;
                            if (v === 'FAIR') return `<span class="text-warning font-weight-bold">${val}</span>`;
                            if (v === 'POOR') return `<span class="text-danger font-weight-bold">${val}</span>`;
                            return val;
                        };

                        const bunyi = formatCond(motorData['BUNYI BEARING'] || '-');
                        const panel = formatCond(motorData['PANEL LOKAL'] || '-');
                        const kelengkapan = formatCond(motorData['KELENGKAPAN'] || '-');
                        const kebersihan = formatCond(motorData['KEBERSIHAN'] || '-');
                        const grounding = formatCond(motorData['GROUNDING'] || '-');

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
                            
                            <td>${vibDE_H}</td>
                            <td>${vibDE_V}</td>
                            <td>${vibDE_Ax}</td>
                            <td>${vibDE_gE}</td>
                            
                            <td>${vibNDE_H}</td>
                            <td>${vibNDE_V}</td>
                            <td>${vibNDE_Ax}</td>
                            <td>${vibNDE_gE}</td>
                            
                            <td>${tempDE}</td>
                            <td>${tempNDE}</td>
                            <td>${suhuRuang}</td>
                            
                            <td>${arusR}</td>
                            <td>${arusS}</td>
                            <td>${arusT}</td>

                            <td>${beban}</td>
                            <td>${damper}</td>
                            
                            <td>${bunyi}</td>
                            <td>${panel}</td>
                            <td>${kelengkapan}</td>
                            <td>${kebersihan}</td>
                            <td>${grounding}</td>
                            <td><span class="border rounded px-2 py-1 bg-light">${regreasing}</span></td>
                            <td class="text-left-custom col-action" style="white-space: normal; min-width: 250px;">${actions}</td>
                        `;
                    } else if (!motorData && rowElement) {
                        rowElement.innerHTML = `
                            <td class="font-weight-bold text-muted sticky-motor text-left-custom">${motorName}</td>
                            <td colspan="27" class="text-center text-warning text-sm">
                                <i class="fas fa-info-circle mr-1"></i> Belum ada riwayat pengukuran
                            </td>
                        `;
                    }
                });

            } else if (result.status === 'empty') {
                const tbody = document.getElementById(`tbody_${unit}`);
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="28" class="text-center text-muted py-4"><i class="fas fa-folder-open fa-2x mb-2"></i><br>Database Master untuk unit ini masih kosong.</td></tr>`;
                }
            } else {
                const tbody = document.getElementById(`tbody_${unit}`);
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="28" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>Gagal memuat: ${result.message}</td></tr>`;
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
                tbody.innerHTML = `<tr><td colspan="28" class="text-center text-danger py-4"><i class="fas fa-wifi fa-2x mb-2"></i><br>Gagal terhubung ke server/API.</td></tr>`;
            }
        }
    }

    // =========================================================================
    // EVENT LISTENER UTAMA 
    // =========================================================================
    document.addEventListener("DOMContentLoaded", function () {

        if (typeof window.dataMotor === 'undefined') {
            document.getElementById('rekap-container').innerHTML = `
                <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle mr-2"></i> Error: File config.js tidak terbaca.</div>`;
            return;
        }

        const units = Object.keys(window.dataMotor);
        const container = document.getElementById('rekap-container');
        container.innerHTML = "";

        // 1. GENERATE TABEL KOSONG (KONDISI AWAL TERTUTUP/COLLAPSED)
        units.forEach(unit => {
            const listMotor = window.dataMotor[unit];
            if (listMotor.length === 0) return;

            const formatUnitLengkap = formatUnitName(unit);

            let cardHTML = `
            <div class="card card-outline card-info mb-4 shadow-sm collapsed-card" id="card_${unit}" style="border-radius: 10px; overflow: hidden;">
                <div class="card-header bg-white pt-3 pb-2">
                    <h3 class="card-title font-weight-bold text-dark" style="font-size: 1.1rem;">
                        <i class="fas fa-server text-info mr-2"></i> ${formatUnitLengkap}
                    </h3>
                    <div class="card-tools">
                        
                        <span id="export_btns_${unit}" style="display: none;">
                            <button type="button" class="btn btn-success btn-sm mr-1 shadow-sm" onclick="exportToExcel('table_${unit}', 'Data_Terbaru_Unit_${unit}')">
                                <i class="fas fa-file-excel mr-1"></i> Excel
                            </button>
                            <button type="button" class="btn btn-danger btn-sm mr-3 shadow-sm" onclick="exportToPDF('table_${unit}', 'Data_Terbaru_Unit_${unit}', '${formatUnitLengkap}')">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </button>
                        </span>

                        <span class="badge badge-info mr-2">View</span>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="card-body p-0 position-relative">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped m-0 text-sm text-nowrap table-sticky-first" id="table_${unit}">
                            <thead>
                                <tr>
                                    <th rowspan="3" class="sticky-motor text-left-custom" style="min-width: 220px;">NAMA MOTOR</th>
                                    <th rowspan="3" style="min-width: 130px;">Date</th>
                                    <th rowspan="3" style="min-width: 150px;">Update By</th>
                                    <th rowspan="3" style="min-width: 80px;">Aksi</th>
                                    <th rowspan="3" style="min-width: 80px;">Section<br>No</th>
                                    
                                    <th colspan="8" class="border-bottom-0">Vibrasi (mm/s)</th>
                                    <th colspan="3" class="border-bottom-0">Temp (°C)</th>
                                    <th colspan="3" class="border-bottom-0">Current (A)</th>
                                    
                                    <th rowspan="3" style="min-width: 80px;">Load<br>Generator</th>
                                    <th rowspan="3" style="min-width: 80px;">Opening<br>Damper</th>
                                    
                                    <th rowspan="3" style="min-width: 80px;">Bunyi<br>Motor</th>
                                    <th rowspan="3" style="min-width: 80px;">Kondisi<br>Panel</th>
                                    <th rowspan="3" style="min-width: 80px;">Kelengkapan</th>
                                    <th rowspan="3" style="min-width: 80px;">Kebersihan</th>
                                    <th rowspan="3" style="min-width: 80px;">Grounding</th>
                                    <th rowspan="3" style="min-width: 80px;">Regreasing</th>
                                    
                                    <th rowspan="3" style="min-width: 350px;" class="text-left-custom col-action">Action</th>
                                </tr>
                                
                                <tr>
                                    <th colspan="4" style="font-weight: normal;">DE</th>
                                    <th colspan="4" style="font-weight: normal;">NDE</th>
                                    
                                    <th rowspan="2" style="min-width: 50px; font-weight: normal;">DE</th>
                                    <th rowspan="2" style="min-width: 50px; font-weight: normal;">NDE</th>
                                    <th rowspan="2" style="min-width: 50px; font-weight: normal;">Ruang</th>
                                    
                                    <th rowspan="2" style="min-width: 45px; font-weight: normal;">R</th>
                                    <th rowspan="2" style="min-width: 45px; font-weight: normal;">S</th>
                                    <th rowspan="2" style="min-width: 45px; font-weight: normal;">T</th>
                                </tr>

                                <tr>
                                    <th style="min-width: 40px; font-weight: normal;">H</th>
                                    <th style="min-width: 40px; font-weight: normal;">V</th>
                                    <th style="min-width: 40px; font-weight: normal;">Ax</th>
                                    <th style="min-width: 40px; font-weight: normal;">gE</th>

                                    <th style="min-width: 40px; font-weight: normal;">H</th>
                                    <th style="min-width: 40px; font-weight: normal;">V</th>
                                    <th style="min-width: 40px; font-weight: normal;">Ax</th>
                                    <th style="min-width: 40px; font-weight: normal;">gE</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_${unit}">`;

            listMotor.forEach(motor => {
                const safeId = motor.replace(/[^a-zA-Z0-9]/g, '_');
                cardHTML += `
                                <tr id="row_${unit}_${safeId}">
                                    <td class="font-weight-bold text-dark sticky-motor text-left-custom">${motor}</td>
                                    <td colspan="27" class="text-muted text-center">
                                        <i class="fas fa-ellipsis-h text-black-50"></i> Menunggu instruksi...
                                    </td>
                                </tr>`;
            });

            cardHTML += `
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="overlay d-none" id="loading_${unit}" style="flex-direction: column; background-color: rgba(255,255,255,0.85);">
                    <i class="fas fa-sync-alt fa-spin fa-3x text-info"></i>
                    <div class="mt-3 text-info font-weight-bold" style="letter-spacing: 1px;">Mengambil Data...</div>
                </div>

            </div>`;

            container.innerHTML += cardHTML;
        });

        // =========================================================================
        // JQUERY EVENT LISTENER: PENDETEKSI ANIMASI ADMINLTE
        // =========================================================================
        // Event dipicu TEPAT setelah kotak selesai terbuka 100%
        $(document).on('expanded.lte.cardwidget', '.card', function () {
            let cardId = $(this).attr('id');
            if (cardId && cardId.startsWith('card_')) {
                let unit = cardId.replace('card_', '');

                // 1. Tampilkan Tombol Export
                document.getElementById(`export_btns_${unit}`).style.display = 'inline-block';

                // 2. Langsung Tarik Data dari API
                fetchUnitData(unit);
            }
        });

        // Event dipicu TEPAT setelah kotak selesai tertutup 100%
        $(document).on('collapsed.lte.cardwidget', '.card', function () {
            let cardId = $(this).attr('id');
            if (cardId && cardId.startsWith('card_')) {
                let unit = cardId.replace('card_', '');

                // Sembunyikan Tombol Export agar UI bersih
                document.getElementById(`export_btns_${unit}`).style.display = 'none';
            }
        });

        // =========================================================================
        // INTERVAL AUTO-REFRESH (Hanya untuk Card yang sedang Terbuka)
        // =========================================================================
        setInterval(() => {
            units.forEach(unit => {
                const card = document.getElementById(`card_${unit}`);
                // Hanya memakan bandwidth server API jika kotak sedang dipantau User
                if (card && !card.classList.contains('collapsed-card')) {
                    fetchUnitData(unit);
                }
            });
        }, 300000);

    });
</script>