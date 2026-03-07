/**
 * File: regreasing-motor.js
 * Deskripsi: Menghitung Jadwal Regreasing (Siklus 3 Bulan) + Memunculkan Nama Updater + Toastr
 */

document.addEventListener("DOMContentLoaded", function () {
  const INTERVAL_BULAN = 3; // Siklus Regreasing = 3 Bulan
  let lastWarnedMotor = ""; // Variabel Anti-Spam untuk Toastr

  function parseDateString(dateStr) {
    if (!dateStr || dateStr === "-" || dateStr === "--") return null;
    let justDate = dateStr.split(" ")[0];
    let parts;

    if (justDate.includes("/")) {
      parts = justDate.split("/");
      if (parts[2].length === 4) {
        return new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
      }
    } else if (justDate.includes("-")) {
      return new Date(justDate);
    }
    return new Date();
  }

  function formatDateUI(dateObj) {
    if (!dateObj || isNaN(dateObj.getTime())) return "--/--/----";
    let d = String(dateObj.getDate()).padStart(2, "0");
    let m = String(dateObj.getMonth() + 1).padStart(2, "0");
    let y = dateObj.getFullYear();
    return `${d}/${m}/${y}`;
  }

  function getRegreasingData(allData, colIndexRegrease) {
    const colIndexTime = 1; // Posisi Waktu/Timestamp
    const colIndexEmail = 2; // Posisi Nama/Email Updater

    let lastSelesaiTime = null;
    let lastBelumTime = null;
    let latestStatus = null;
    let latestUpdater = null; // Variabel baru untuk menampung nama

    for (let i = allData.length - 1; i >= 0; i--) {
      let valRegrease = allData[i][colIndexRegrease];

      if (valRegrease !== null && valRegrease !== undefined) {
        let strVal = String(valRegrease)
          .replace(/(<([^>]+)>)/gi, "")
          .trim()
          .toUpperCase();

        if (strVal === "SELESAI" || strVal === "BELUM") {
          let timeVal = allData[i][colIndexTime]
            ? String(allData[i][colIndexTime])
                .replace(/(<([^>]+)>)/gi, "")
                .trim()
            : "-";
          let updaterVal = allData[i][colIndexEmail]
            ? String(allData[i][colIndexEmail])
                .replace(/(<([^>]+)>)/gi, "")
                .trim()
            : "-";

          // Simpan status dan nama updater dari data terbaru
          if (latestStatus === null) {
            latestStatus = strVal;
            latestUpdater = updaterVal;
          }

          if (strVal === "SELESAI" && lastSelesaiTime === null) {
            lastSelesaiTime = timeVal;
            break;
          } else if (strVal === "BELUM" && lastBelumTime === null) {
            lastBelumTime = timeVal;
          }
        }
      }
    }

    let baseTime = lastSelesaiTime || lastBelumTime || "-";
    return {
      status: latestStatus || "--",
      baseTime: baseTime,
      updater: latestUpdater || "--", // Kembalikan nilai nama
    };
  }

  function syncRegreasingTable() {
    if (typeof $ !== "undefined" && $.fn.DataTable.isDataTable("#example1")) {
      const table = $("#example1").DataTable();

      const lastDateDom = document.getElementById("date-regrease-last");
      const nextDateDom = document.getElementById("date-regrease-next");
      const timeLeftDom = document.getElementById("time-left-regrease");
      const updaterDom = document.getElementById("updater-regrease"); // Ganti dari statDom

      if (!lastDateDom || !nextDateDom || !timeLeftDom || !updaterDom) return;

      if (table.data().any()) {
        const allData = table.rows().data();

        // Menggunakan index 19 (sama seperti di file kondisi-motor.js)
        let dataRegrease = getRegreasingData(allData, 18);

        // 1. UPDATE KOLOM NAMA (UPDATE BY)
        updaterDom.innerText =
          dataRegrease.updater !== "--" ? dataRegrease.updater : "--";

        // 2. PERHITUNGAN KALENDER & TOASTR
        if (dataRegrease.baseTime !== "-") {
          let lastDateObj = parseDateString(dataRegrease.baseTime);
          lastDateDom.innerText = formatDateUI(lastDateObj);

          if (lastDateObj) {
            let nextDateObj = new Date(lastDateObj.getTime());
            nextDateObj.setMonth(nextDateObj.getMonth() + INTERVAL_BULAN);
            nextDateDom.innerText = formatDateUI(nextDateObj);

            let today = new Date();
            today.setHours(0, 0, 0, 0);
            nextDateObj.setHours(0, 0, 0, 0);

            let selisihWaktu = nextDateObj.getTime() - today.getTime();
            let sisaHari = Math.ceil(selisihWaktu / (1000 * 3600 * 24));

            let currentMotor = $("#pilihMotor").val() || "Motor";

            // LOGIKA WARNA SISA WAKTU & TOASTR ALERT
            if (sisaHari > 14) {
              timeLeftDom.innerHTML = `${sisaHari} Hari`;
              timeLeftDom.style.color = "#28a745"; // Hijau
              lastWarnedMotor = "";
            } else if (sisaHari > 0 && sisaHari <= 14) {
              timeLeftDom.innerHTML = `${sisaHari} Hari`;
              timeLeftDom.style.color = "#ffc107"; // Kuning Warning
              lastWarnedMotor = "";
            } else {
              timeLeftDom.innerHTML = `Lewat ${Math.abs(sisaHari)} Hari`;
              timeLeftDom.style.color = "#dc3545"; // Merah Critical

              // EKSEKUSI TOASTR OVERDUE
              if (lastWarnedMotor !== currentMotor) {
                if (typeof toastr !== "undefined") {
                  toastr.error(
                    `Jadwal regreasing <b>${currentMotor}</b> sudah lewat ${Math.abs(sisaHari)} hari! Segera jadwalkan pemeliharaan.`,
                    "⚠️ Regreasing Overdue!",
                  );
                }
                lastWarnedMotor = currentMotor;
              }
            }
          }
        } else {
          lastDateDom.innerText = "--/--/----";
          nextDateDom.innerText = "--/--/----";
          timeLeftDom.innerText = "-- Hari";
          timeLeftDom.style.color = "#6c757d";
          lastWarnedMotor = "";
        }
      }
    }
  }

  // Trigger setiap kali tabel DataTable di-refresh
  if (typeof $ !== "undefined") {
    $("#example1").on("draw.dt", function () {
      syncRegreasingTable();
    });
  }
});
