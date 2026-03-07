/**
 * File: kondisi-motor.js
 * Deskripsi: Mensinkronkan Tabel Kondisi Fisik (GOOD/FAIR/POOR) & Regreasing (BELUM/SELESAI)
 */

document.addEventListener("DOMContentLoaded", function () {
  // Fungsi Helper untuk mengatur Teks, Warna Badge, Tanggal, dan Nama Updater
  function updateConditionItem(itemKey, valStatus, valDate, valName) {
    const statDom = document.getElementById(`stat-${itemKey}`);
    const dateDom = document.getElementById(`date-${itemKey}`);
    const nameDom = document.getElementById(`updater-${itemKey}`);

    if (!statDom || !dateDom || !nameDom) return;

    let statusText = String(valStatus || "--")
      .trim()
      .toUpperCase();
    let dateText = String(valDate || "--/--/----").trim();
    let nameText = String(valName || "--").trim();

    dateDom.innerText = dateText !== "" ? dateText : "--/--/----";
    nameDom.innerText = nameText !== "" ? nameText : "--";

    statDom.removeAttribute("style");
    statDom.className = "status-badge"; // Reset class bawaan

    // =========================================================
    // REVISI LOGIKA STRICT (Hanya GOOD, FAIR, POOR, SELESAI, BELUM)
    // =========================================================
    if (statusText === "GOOD") {
      statDom.innerText = "GOOD";
      statDom.classList.add("status-good");
    } else if (statusText === "FAIR") {
      statDom.innerText = "FAIR";
      statDom.classList.add("status-fair");
    } else if (statusText === "POOR") {
      statDom.innerText = "POOR";
      statDom.classList.add("status-poor");
    } else if (statusText === "SELESAI") {
      statDom.innerText = "SELESAI";
      statDom.classList.add("status-done");
    } else if (statusText === "BELUM") {
      statDom.innerText = "BELUM";
      statDom.classList.add("status-belum");
    } else {
      // Jika data kosong atau kata di luar 5 kata di atas
      statDom.innerText = "--";
      statDom.style.backgroundColor = "#e2e3e5";
      statDom.style.color = "#383d41";
      statDom.style.border = "1px solid #d6d8db";
    }
  }

  // Mencari baris ke atas sampai ketemu data yang BUKAN strip
  function getLastValidCondition(allData, colIndexStatus) {
    const colIndexTime = 1; // Posisi kolom Waktu/Timestamp
    const colIndexEmail = 2; // Posisi kolom Nama/Email

    for (let i = allData.length - 1; i >= 0; i--) {
      let valStatus = allData[i][colIndexStatus];

      if (valStatus !== null && valStatus !== undefined) {
        let strVal = String(valStatus)
          .replace(/(<([^>]+)>)/gi, "")
          .trim();

        if (
          strVal !== "" &&
          strVal !== "-" &&
          strVal !== "--" &&
          strVal !== "---"
        ) {
          let timeVal = allData[i][colIndexTime]
            ? String(allData[i][colIndexTime])
                .replace(/(<([^>]+)>)/gi, "")
                .trim()
            : "-";
          let fullNameOrEmail = allData[i][colIndexEmail]
            ? String(allData[i][colIndexEmail])
                .replace(/(<([^>]+)>)/gi, "")
                .trim()
            : "-";

          return {
            status: strVal,
            timestamp: timeVal,
            updater: fullNameOrEmail,
          };
        }
      }
    }
    return { status: "--", timestamp: "--/--/----", updater: "--" };
  }

  // Mensinkronkan data saat DataTable selesai digambar
  function syncConditionTable() {
    if (typeof $ !== "undefined" && $.fn.DataTable.isDataTable("#example1")) {
      const table = $("#example1").DataTable();

      if (table.data().any()) {
        const allData = table.rows().data();

        // Index bergeser +2 karena tambahan kolom Vibrasi DE dan NDE sebelumnya
        let dataBunyi = getLastValidCondition(allData, 13);
        let dataPanel = getLastValidCondition(allData, 14);
        let dataLengkap = getLastValidCondition(allData, 15);
        let dataBersih = getLastValidCondition(allData, 16);
        let dataGround = getLastValidCondition(allData, 17);

        let dataAction = getLastValidCondition(allData, 19); // Action/Regreasing

        // Eksekusi pembaruan elemen UI tabel kondisi fisik
        updateConditionItem(
          "bunyi",
          dataBunyi.status,
          dataBunyi.timestamp,
          dataBunyi.updater,
        );
        updateConditionItem(
          "panel",
          dataPanel.status,
          dataPanel.timestamp,
          dataPanel.updater,
        );
        updateConditionItem(
          "lengkap",
          dataLengkap.status,
          dataLengkap.timestamp,
          dataLengkap.updater,
        );
        updateConditionItem(
          "bersih",
          dataBersih.status,
          dataBersih.timestamp,
          dataBersih.updater,
        );
        updateConditionItem(
          "ground",
          dataGround.status,
          dataGround.timestamp,
          dataGround.updater,
        );

        // Update tampilan Dashboard Action/Regreasing
        const teksActionDom = document.getElementById("teks-action");
        const tanggalActionDom = document.getElementById("tanggal-action");
        if (teksActionDom) teksActionDom.innerText = dataAction.status;
        if (tanggalActionDom) tanggalActionDom.innerText = dataAction.timestamp;

        // Jika Anda juga melempar data Regreasing ini ke Tabel lewat fungsi updateConditionItem:
        // Misalnya: updateConditionItem("regreasing", dataAction.status, dataAction.timestamp, dataAction.updater);

        // Update waktu inspeksi terakhir secara global
        const timeGlobalDom = document.getElementById("time-kondisi");
        if (timeGlobalDom) {
          let lastRowTime = allData[allData.length - 1][1];
          timeGlobalDom.innerText = lastRowTime
            ? String(lastRowTime)
                .replace(/(<([^>]+)>)/gi, "")
                .trim()
            : "-";
        }
      } else {
        // Default jika tabel kosong
        const emptyData = {
          status: "--",
          timestamp: "--/--/----",
          updater: "--",
        };
        updateConditionItem(
          "bunyi",
          emptyData.status,
          emptyData.timestamp,
          emptyData.updater,
        );
        updateConditionItem(
          "panel",
          emptyData.status,
          emptyData.timestamp,
          emptyData.updater,
        );
        updateConditionItem(
          "lengkap",
          emptyData.status,
          emptyData.timestamp,
          emptyData.updater,
        );
        updateConditionItem(
          "bersih",
          emptyData.status,
          emptyData.timestamp,
          emptyData.updater,
        );
        updateConditionItem(
          "ground",
          emptyData.status,
          emptyData.timestamp,
          emptyData.updater,
        );

        if (document.getElementById("teks-action"))
          document.getElementById("teks-action").innerText = "--";
        if (document.getElementById("tanggal-action"))
          document.getElementById("tanggal-action").innerText = "--/--/----";

        const timeGlobalDom = document.getElementById("time-kondisi");
        if (timeGlobalDom) timeGlobalDom.innerText = "-";
      }
    }
  }

  // Trigger agar selalu terpanggil setiap ada refresh / pergantian data
  if (typeof $ !== "undefined") {
    $("#example1").on("draw.dt", function () {
      syncConditionTable();
    });
  }
});
