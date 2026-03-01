// =========================================================================
// Fungsi Export Tabel ke Excel
// =========================================================================
function exportToExcel(tableID, filename = "") {
  const tableSelect = document.getElementById(tableID);

  // Membungkus HTML tabel ke format standar excel
  const html = `
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta charset="UTF-8">
        <style>
            table, th, td { border: 1px solid black; border-collapse: collapse; text-align: center; vertical-align: middle; }
            th { background-color: #f4f6f9; font-weight: bold; }
            th:first-child { text-align: left; } /* Memastikan Excel juga rata kiri di kolom pertama */
        </style>
    </head>
    <body>
        ${tableSelect.outerHTML}
    </body>
    </html>`;

  // Buat Blob file
  const blob = new Blob([html], { type: "application/vnd.ms-excel" });

  // Buat link download otomatis
  const downloadLink = document.createElement("a");
  const url = URL.createObjectURL(blob);
  downloadLink.href = url;
  downloadLink.download = filename ? filename + ".xls" : "data_motor.xls";

  document.body.appendChild(downloadLink);
  downloadLink.click();
  document.body.removeChild(downloadLink);
}

// =========================================================================
// Fungsi Tarik Data API Utama
// =========================================================================
document.addEventListener("DOMContentLoaded", function () {
  // 1. Cek apakah config.js sudah dimuat
  if (
    typeof window.SCRIPT_URLS === "undefined" ||
    typeof window.API_TOKEN === "undefined"
  ) {
    console.error(
      "Konfigurasi API tidak ditemukan. Pastikan config.js sudah di-load.",
    );
    return;
  }

  const token = window.API_TOKEN;

  // 2. Buat Fungsi Reusable untuk memuat data berdasarkan URL API dan ID Tabel
  async function loadDataMotor(apiUrl, tableId) {
    const tableRows = document.querySelectorAll(`#${tableId} tbody tr`);

    // Jika tabel tidak ada di HTML atau URL API masih kosong di config.js, hentikan eksekusi
    if (tableRows.length === 0 || !apiUrl || apiUrl === "") {
      console.warn(
        `Melewati ${tableId}: URL API belum diatur atau tabel tidak ditemukan.`,
      );
      return;
    }

    try {
      // Ambil data dari Apps Script
      const response = await fetch(
        `${apiUrl}?token=${token}&sheet=REKAP_REGREASING`,
      );
      const dataRekap = await response.json();

      if (dataRekap.error || dataRekap.status === "error") {
        throw new Error(
          dataRekap.message ||
            dataRekap.error ||
            "Gagal memuat sheet REKAP_REGREASING",
        );
      }

      // Mapping data agar mudah dicari berdasarkan nama motor
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

      // Masukkan data ke dalam masing-masing baris tabel HTML
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
          if (!isNaN(sisa)) {
            if (sisa > 14) {
              colSisa.innerHTML = `<span class="badge badge-success">${sisa} Hari Lagi</span>`;
            } else if (sisa > 0 && sisa <= 14) {
              colSisa.innerHTML = `<span class="badge badge-warning text-dark">${sisa} Hari Lagi</span>`;
            } else if (sisa === 0) {
              colSisa.innerHTML = `<span class="badge badge-warning text-dark">Hari Ini!</span>`;
            } else {
              colSisa.innerHTML = `<span class="badge badge-danger">Lewat ${Math.abs(sisa)} Hari</span>`;
            }
          } else {
            colSisa.innerHTML = "-";
          }

          colStatus.innerHTML = `<span class="text-muted">${motorData.email}</span>`;
        } else {
          colTerakhir.innerHTML = "Belum ada data";
          colSelanjutnya.innerHTML = "-";
          colSisa.innerHTML = "-";
          colStatus.innerHTML = "-";
        }
      });
    } catch (error) {
      console.error(
        `Error saat memproses data regreasing untuk ${tableId}:`,
        error,
      );
      tableRows.forEach((tr) => {
        tr.querySelector(".terakhir-regreasing").innerHTML =
          `<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat</span>`;
        tr.querySelector(".jadwal-selanjutnya").innerHTML = "-";
        tr.querySelector(".sisa-waktu").innerHTML = "-";
        tr.querySelector(".status-updater").innerHTML = "-";
      });
    }
  }

  // =========================================================================
  // 3. JALANKAN FUNGSI UNTUK MASING-MASING UNIT DI SINI
  // =========================================================================

  // Eksekusi untuk MOTOR UNIT C 6KV
  loadDataMotor(window.SCRIPT_URLS.C6KV, "table-unit-c-6kv");

  // Eksekusi untuk MOTOR UNIT D 6KV
  loadDataMotor(window.SCRIPT_URLS.D6KV, "table-unit-d-6kv");

  // Eksekusi untuk MOTOR UNIT C 380V
  loadDataMotor(window.SCRIPT_URLS.C380, "table-unit-c-380v");

  // Eksekusi untuk MOTOR UNIT D 380V
  loadDataMotor(window.SCRIPT_URLS.D380, "table-unit-d-380v");
});
