/**
 * File: kondisi-motor.js
 * Deskripsi: Mensinkronkan Status Fisik (GOOD/FAIR/POOR) menjadi UI Card Modern & Kotak Action
 */

document.addEventListener("DOMContentLoaded", function () {
  
  // Fungsi Helper untuk mengatur Teks, Warna Badge, Tanggal, dan Border Kartu
  function updateConditionItem(itemKey, valStatus, valDate) {
    const statDom = document.getElementById(`stat-${itemKey}`);
    const dateDom = document.getElementById(`date-${itemKey}`);
    const cardDom = document.getElementById(`card-${itemKey}`); // ID Kartu Pembungkus

    if (!statDom || !dateDom) return;

    let statusText = String(valStatus || "--").trim().toUpperCase();
    let dateText = String(valDate || "--/--/----").trim();

    dateDom.innerText = dateText !== "" ? dateText : "--/--/----";

    // Reset Class
    statDom.className = "badge px-2 py-1"; 
    if (cardDom) cardDom.className = "status-card-modern p-2 rounded"; 

    // =========================================================
    // LOGIKA WARNA KARTU DAN BADGE
    // =========================================================
    if (statusText === "GOOD") {
      statDom.innerText = "GOOD";
      statDom.classList.add("badge-success");
      if (cardDom) cardDom.classList.add("border-left-good", "shadow-sm");
      
    } else if (statusText === "FAIR") {
      statDom.innerText = "FAIR";
      statDom.classList.add("badge-warning");
      if (cardDom) cardDom.classList.add("border-left-fair", "shadow-sm");
      
    } else if (statusText === "POOR") {
      statDom.innerText = "POOR";
      statDom.classList.add("badge-danger");
      if (cardDom) cardDom.classList.add("border-left-poor", "shadow-sm");
      
    } else {
      // Jika data kosong
      statDom.innerText = "--";
      statDom.classList.add("badge-secondary");
      if (cardDom) cardDom.classList.add("border-left-empty");
    }
  }

  // Mencari baris ke atas sampai ketemu data yang BUKAN strip/kosong
  function getLastValidCondition(allData, colIndexStatus) {
    const colIndexTime = 1; // Posisi kolom Timestamp (Kolom ke-2)

    for (let i = allData.length - 1; i >= 0; i--) {
      let valStatus = allData[i][colIndexStatus];

      if (valStatus !== null && valStatus !== undefined) {
        let strVal = String(valStatus).replace(/(<([^>]+)>)/gi, "").trim();

        if (strVal !== "" && strVal !== "-" && strVal !== "--" && strVal !== "---") {
          let timeVal = allData[i][colIndexTime]
            ? String(allData[i][colIndexTime]).replace(/(<([^>]+)>)/gi, "").trim()
            : "-";

          return {
            status: strVal,
            timestamp: timeVal
          };
        }
      }
    }
    return { status: "--", timestamp: "--/--/----" };
  }

  // Mensinkronkan data saat DataTable selesai digambar
  function syncConditionTable() {
    if (typeof $ !== "undefined" && $.fn.DataTable.isDataTable("#example1")) {
      const table = $("#example1").DataTable();

      if (table.data().any()) {
        const allData = table.rows().data();

        // Mengambil index yang akurat untuk format 28 Kolom
        let dataBunyi   = getLastValidCondition(allData, 21);
        let dataPanel   = getLastValidCondition(allData, 22);
        let dataLengkap = getLastValidCondition(allData, 23);
        let dataBersih  = getLastValidCondition(allData, 24);
        let dataGround  = getLastValidCondition(allData, 25);
        let dataAction  = getLastValidCondition(allData, 27); // Kolom paling terakhir (Action)

        // Terapkan ke UI Grid Kartu
        updateConditionItem("bunyi", dataBunyi.status, dataBunyi.timestamp);
        updateConditionItem("panel", dataPanel.status, dataPanel.timestamp);
        updateConditionItem("lengkap", dataLengkap.status, dataLengkap.timestamp);
        updateConditionItem("bersih", dataBersih.status, dataBersih.timestamp);
        updateConditionItem("ground", dataGround.status, dataGround.timestamp);

        // Terapkan ke Kotak Action
        const teksActionDom = document.getElementById("teks-action");
        const tanggalActionDom = document.getElementById("tanggal-action");
        
        if (teksActionDom) {
            teksActionDom.innerText = dataAction.status !== "--" ? `"${dataAction.status}"` : "Belum ada data action yang direkam.";
        }
        if (tanggalActionDom) {
            tanggalActionDom.innerText = dataAction.timestamp;
        }

      } else {
        // Jika tabel kosong
        const emptyData = { status: "--", timestamp: "--/--/----" };
        
        updateConditionItem("bunyi", emptyData.status, emptyData.timestamp);
        updateConditionItem("panel", emptyData.status, emptyData.timestamp);
        updateConditionItem("lengkap", emptyData.status, emptyData.timestamp);
        updateConditionItem("bersih", emptyData.status, emptyData.timestamp);
        updateConditionItem("ground", emptyData.status, emptyData.timestamp);

        if (document.getElementById("teks-action")) {
            document.getElementById("teks-action").innerText = "Belum ada riwayat data yang direkam pada motor ini.";
        }
        if (document.getElementById("tanggal-action")) {
            document.getElementById("tanggal-action").innerText = "--/--/----";
        }
      }
    }
  }

  // Hook DataTable Event
  if (typeof $ !== "undefined") {
    $("#example1").on("draw.dt", function () {
      syncConditionTable();
    });
  }
});