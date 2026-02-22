/**
 * File: regreasing-motor.js
 * Deskripsi: Menghitung Jadwal Regreasing (Siklus 3 Bulan) + Peringatan Toastr
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
    const colIndexTime = 1;

    let lastSelesaiTime = null;
    let lastBelumTime = null;
    let latestStatus = null;

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

          if (latestStatus === null) {
            latestStatus = strVal;
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
    return { status: latestStatus || "--", baseTime: baseTime };
  }

  function syncRegreasingTable() {
    if (typeof $ !== "undefined" && $.fn.DataTable.isDataTable("#example1")) {
      const table = $("#example1").DataTable();

      const lastDateDom = document.getElementById("date-regrease-last");
      const nextDateDom = document.getElementById("date-regrease-next");
      const timeLeftDom = document.getElementById("time-left-regrease");
      const statDom = document.getElementById("stat-regrease");

      if (!lastDateDom || !nextDateDom || !timeLeftDom || !statDom) return;

      if (table.data().any()) {
        const allData = table.rows().data();
        let dataRegrease = getRegreasingData(allData, 17);

        // 1. UPDATE BADGE STATUS
        statDom.innerText =
          dataRegrease.status !== "--" ? dataRegrease.status : "N/A";
        statDom.className = "status-badge";
        if (dataRegrease.status === "SELESAI") {
          statDom.classList.add("status-done");
        } else if (dataRegrease.status === "BELUM") {
          statDom.classList.add("status-poor");
        } else {
          statDom.classList.add("status-pending");
        }

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

            // LOGIKA WARNA & TOASTR ALERT
            if (sisaHari > 14) {
              timeLeftDom.innerHTML = `${sisaHari} Hari`;
              timeLeftDom.style.color = "#28a745";
              lastWarnedMotor = ""; // Reset flag jika aman
            } else if (sisaHari > 0 && sisaHari <= 14) {
              timeLeftDom.innerHTML = `${sisaHari} Hari`;
              timeLeftDom.style.color = "#ffc107";
              lastWarnedMotor = ""; // Reset flag jika aman
            } else {
              timeLeftDom.innerHTML = `Lewat ${Math.abs(sisaHari)} Hari`;
              timeLeftDom.style.color = "#dc3545";

              // EKSEKUSI TOASTR OVERDUE
              if (lastWarnedMotor !== currentMotor) {
                if (typeof toastr !== "undefined") {
                  toastr.error(
                    `Jadwal regreasing <b>${currentMotor}</b> sudah lewat ${Math.abs(sisaHari)} hari! Segera jadwalkan pemeliharaan.`,
                    "⚠️ Regreasing Overdue!",
                  );
                }
                // Kunci flag agar tidak muncul lagi saat user ganti page tabel
                lastWarnedMotor = currentMotor;
              }
            }
          }
        } else {
          lastDateDom.innerText = "--/--/----";
          nextDateDom.innerText = "--/--/----";
          timeLeftDom.innerText = "-- Hari";
          timeLeftDom.style.color = "#6c757d";
          lastWarnedMotor = ""; // Reset flag
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
