// Fungsi Export Tabel ke Excel
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

// Fungsi Tarik Data API
document.addEventListener("DOMContentLoaded", async function () {
  if (
    typeof window.SCRIPT_URLS === "undefined" ||
    typeof window.API_TOKEN === "undefined"
  ) {
    console.error(
      "Konfigurasi API tidak ditemukan. Pastikan config.js sudah di-load.",
    );
    return;
  }

  const apiUrl = window.SCRIPT_URLS.C6KV;
  const token = window.API_TOKEN;
  const tableRows = document.querySelectorAll("#table-unit-c-6kv tbody tr");

  try {
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
    console.error("Error saat memproses data regreasing:", error);
    tableRows.forEach((tr) => {
      tr.querySelector(".terakhir-regreasing").innerHTML =
        `<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat</span>`;
      tr.querySelector(".jadwal-selanjutnya").innerHTML = "-";
      tr.querySelector(".sisa-waktu").innerHTML = "-";
      tr.querySelector(".status-updater").innerHTML = "-";
    });
  }
});
