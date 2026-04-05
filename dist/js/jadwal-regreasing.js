// =========================================================================
// Fungsi Export Tabel ke Excel (Disempurnakan dengan Tanggal)
// =========================================================================
function exportToExcel(tableID, filename = "") {
  const tableSelect = document.getElementById(tableID);
  if (!tableSelect) {
      alert("Tabel tidak ditemukan!");
      return;
  }

  // Membuat format tanggal YYYY-MM-DD untuk nama file
  const today = new Date();
  const dateStr = today.toISOString().split('T')[0];
  const finalFilename = filename ? `${filename}_${dateStr}.xls` : `Data_Regreasing_${dateStr}.xls`;

  const html = `
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta charset="UTF-8">
        <style>
            table, th, td { border: 1px solid black; border-collapse: collapse; text-align: center; vertical-align: middle; }
            th { background-color: #f4f6f9; font-weight: bold; }
            th:first-child { text-align: left; } 
        </style>
    </head>
    <body>
        ${tableSelect.outerHTML}
    </body>
    </html>`;

  const blob = new Blob([html], { type: "application/vnd.ms-excel" });
  const downloadLink = document.createElement("a");
  const url = URL.createObjectURL(blob);
  downloadLink.href = url;
  downloadLink.download = finalFilename;

  document.body.appendChild(downloadLink);
  downloadLink.click();
  document.body.removeChild(downloadLink);
}

// =========================================================================
// Fungsi Tarik Data dari PROXY SERVER
// =========================================================================
document.addEventListener("DOMContentLoaded", function () {
  
  async function loadDataMotor(kodeUnit, tableId) {
    // Safety check: Pastikan tabel ada di halaman
    const tableEl = document.getElementById(tableId);
    if (!tableEl) return;

    const tableRows = tableEl.querySelectorAll(`tbody tr.data-row`);

    // Jika tidak ada motor yang dicentang di Settings, hentikan proses (Hemat Bandwidth)
    if (tableRows.length === 0) return;

    try {
      // Menembak file PHP di server sendiri
      const response = await fetch(`api/api_proxy.php?unit=${kodeUnit}`);
      const dataRekap = await response.json();

      if (dataRekap.error || dataRekap.status === "error") {
        throw new Error(dataRekap.message || dataRekap.error || "Gagal memuat data dari server proxy");
      }

      const dataMotorMap = {};
      for (let i = 1; i < dataRekap.length; i++) {
        const row = dataRekap[i];
        const namaMotor = row[1];

        dataMotorMap[namaMotor] = {
          terakhir: row[3],
          selanjutnya: row[4],
          sisaHari: row[5],
          email: row[7],
        };
      }

      tableRows.forEach((tr) => {
        const motorName = tr.getAttribute("data-motor");
        const colTerakhir = tr.querySelector(".terakhir-regreasing");
        const colSelanjutnya = tr.querySelector(".jadwal-selanjutnya");
        const colSisa = tr.querySelector(".sisa-waktu");
        const colStatus = tr.querySelector(".status-updater");

        const motorData = dataMotorMap[motorName];

        if (motorData && motorData.terakhir && motorData.terakhir !== "-") {
          colTerakhir.innerHTML = motorData.terakhir;
          colSelanjutnya.innerHTML = motorData.selanjutnya;

          let sisa = parseInt(motorData.sisaHari);

          // Logika Pewarnaan Sisa Waktu
          if (!isNaN(sisa)) {
            if (sisa > 14) {
              colSisa.innerHTML = `<span class="text-success font-weight-bold">${sisa} Hari</span>`;
            } else if (sisa > 0 && sisa <= 14) {
              colSisa.innerHTML = `<span class="text-warning font-weight-bold">${sisa} Hari</span>`;
            } else if (sisa === 0) {
              colSisa.innerHTML = `<span class="text-warning font-weight-bold">Hari Ini!</span>`;
            } else {
              colSisa.innerHTML = `<span class="text-danger font-weight-bold">Lewat ${Math.abs(sisa)} Hari</span>`;
            }
          } else {
            colSisa.innerHTML = "-";
          }

          colStatus.innerHTML = `<span class="text-muted">${motorData.email}</span>`;
        } else {
          colTerakhir.innerHTML = "Belum ada riwayat";
          colSelanjutnya.innerHTML = "-";
          colSisa.innerHTML = "-";
          colStatus.innerHTML = "-";
        }
      });
    } catch (error) {
      console.error(`Error saat memproses data regreasing untuk ${tableId}:`, error);
      tableRows.forEach((tr) => {
        tr.querySelector(".terakhir-regreasing").innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat</span>`;
        tr.querySelector(".jadwal-selanjutnya").innerHTML = "-";
        tr.querySelector(".sisa-waktu").innerHTML = "-";
        tr.querySelector(".status-updater").innerHTML = "-";
      });
    }
  }

  // =========================================================================
  // JALANKAN FUNGSI (Hanya mengirim kode unit saja)
  // =========================================================================
  loadDataMotor("C6KV", "table-unit-c-6kv");
  loadDataMotor("D6KV", "table-unit-d-6kv");
  loadDataMotor("C380", "table-unit-c-380v");
  loadDataMotor("D380", "table-unit-d-380v");
});