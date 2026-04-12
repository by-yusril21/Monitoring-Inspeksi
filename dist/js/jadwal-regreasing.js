// =========================================================================
// Fungsi Export Tabel ke Excel
// =========================================================================
function exportToExcel(tableID, filename = "") {
  const tableSelect = document.getElementById(tableID);
  if (!tableSelect) {
    alert("Tabel tidak ditemukan!");
    return;
  }

  const today = new Date();
  const dateStr = today.toISOString().split("T")[0];
  const finalFilename = filename
    ? `${filename}_${dateStr}.xls`
    : `Data_Regreasing_${dateStr}.xls`;

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
// Fungsi Export Tabel ke PDF (Dengan Kop Surat)
// =========================================================================
function exportToPDF(tableId, fileName, unitLengkap) {
  if (!window.jspdf || !window.jspdf.jsPDF) {
    alert(
      "Library PDF belum selesai dimuat, pastikan koneksi internet Anda aktif.",
    );
    return;
  }

  const doc = new window.jspdf.jsPDF({
    orientation: "landscape",
    unit: "cm",
    format: "a4",
  });
  const table = document.getElementById(tableId);
  if (!table) return;

  const cloneTable = table.cloneNode(true);

  let judul1Text = document.getElementById("judul-1-pdf").innerText.trim();
  let judul2Text = document.getElementById("judul-2-pdf").innerText.trim();
  let logoBase64 = document.getElementById("logo-base64-pdf").innerText.trim();
  let currentUser = document.getElementById("nama-user-login").innerText.trim();

  let today = new Date();
  let dateString =
    ("0" + today.getDate()).slice(-2) +
    "/" +
    ("0" + (today.getMonth() + 1)).slice(-2) +
    "/" +
    today.getFullYear();
  let timeString =
    ("0" + today.getHours()).slice(-2) +
    ":" +
    ("0" + today.getMinutes()).slice(-2) +
    ":" +
    ("0" + today.getSeconds()).slice(-2);
  let infoString =
    "Tanggal unduh data : " +
    dateString +
    " " +
    timeString +
    " | Oleh : " +
    currentUser;
  let subHeaderString = "JADWAL REGREASING - " + unitLengkap;

  doc.setFontSize(10);
  doc.setFont("helvetica", "bold");
  doc.text(judul1Text, 0.5, 1.0);

  doc.setFontSize(9);
  doc.text(judul2Text, 0.5, 1.4);

  doc.setFontSize(8);
  doc.setFont("helvetica", "normal");
  doc.text(infoString, 0.5, 1.8);

  if (logoBase64 && logoBase64.indexOf("data:image") === 0) {
    doc.addImage(logoBase64, "PNG", 27.5, 0.3, 1.6, 1.6);
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
      fontSize: 8,
      valign: "middle",
      halign: "center",
      lineWidth: 0.01,
      lineColor: [0, 0, 0],
      textColor: [0, 0, 0],
      cellPadding: 0.2,
      overflow: "linebreak",
    },
    headStyles: {
      fillColor: [86, 165, 180],
      textColor: [0, 0, 0],
      fontStyle: "bold",
      fontSize: 8,
    },
    columnStyles: {
      0: { halign: "left", cellWidth: 8 },
      1: { cellWidth: 7 },
      2: { cellWidth: 4.5 },
      3: { cellWidth: 4.5 },
      4: { cellWidth: 4 },
    },
    theme: "grid",
    didDrawPage: function (data) {
      if (logoBase64 && logoBase64.indexOf("data:image") === 0) {
        doc.setGState(new doc.GState({ opacity: 0.1 }));
        doc.addImage(logoBase64, "PNG", 7.85, 3.5, 14, 14);
        doc.setGState(new doc.GState({ opacity: 1.0 }));
      }
    },
  });

  const finalFileName = fileName
    ? `${fileName}_${today.toISOString().split("T")[0]}`
    : `Jadwal_Regreasing_${unitLengkap}`;
  doc.save(finalFileName + ".pdf");
}

// =========================================================================
// FUNGSI TARIK DATA DARI PROXY SERVER (OPTIMASI PARALLEL FETCH)
// =========================================================================
document.addEventListener("DOMContentLoaded", async function () {
  // Daftar semua unit dan ID tabelnya
  const unitConfigs = [
    { kode: "C6KV", tableId: "table-unit-c-6kv" },
    { kode: "D6KV", tableId: "table-unit-d-6kv" },
    { kode: "C380", tableId: "table-unit-c-380v" },
    { kode: "D380", tableId: "table-unit-d-380v" },
    { kode: "UTILITY6KV", tableId: "table-unit-utility-6kv" },
    { kode: "UTILITY380", tableId: "table-unit-utility-380v" },
  ];

  // Melakukan request API ke-6 unit secara bersamaan (Parallel)
  const fetchPromises = unitConfigs.map(async (unit) => {
    const tableEl = document.getElementById(unit.tableId);
    if (!tableEl) return;

    const tableRows = tableEl.querySelectorAll(`tbody tr.data-row`);
    if (tableRows.length === 0) return; // Hemat Bandwidth jika tidak ada motor dicentang

    try {
      const response = await fetch(`api/api_proxy.php?unit=${unit.kode}`);
      const dataRekap = await response.json();

      if (dataRekap.error || dataRekap.status === "error") {
        throw new Error(
          dataRekap.message || dataRekap.error || "Gagal memuat proxy",
        );
      }

      // Ubah Format Array dari Apps Script menjadi Object Map
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

      // Render ke tabel
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
      console.error(`Error proses regreasing ${unit.tableId}:`, error);
      tableRows.forEach((tr) => {
        tr.querySelector(".terakhir-regreasing").innerHTML =
          `<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat</span>`;
        tr.querySelector(".jadwal-selanjutnya").innerHTML = "-";
        tr.querySelector(".sisa-waktu").innerHTML = "-";
        tr.querySelector(".status-updater").innerHTML = "-";
      });
    }
  });

  // Tunggu semua proses tabel selesai bersamaan
  await Promise.all(fetchPromises);
});
