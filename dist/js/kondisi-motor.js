/**
 * File: kondisi-motor.js
 * Deskripsi: Mensinkronkan Tabel Kondisi Fisik Motor dengan Data Terakhir di DataTables
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

    // 1. Update Tanggal dan Nama
    dateDom.innerText = dateText !== "" ? dateText : "--/--/----";
    nameDom.innerText = nameText !== "" ? nameText : "--";

    // 2. KUNCI PERBAIKAN: Hapus style inline lama agar tidak menimpa warna class
    statDom.removeAttribute("style");
    statDom.className = "status-badge"; // Reset ke class dasar

    // 3. Terapkan warna sesuai keyword dari Google Sheets
    if (
      statusText === "GOOD" ||
      statusText === "BAIK" ||
      statusText === "NORMAL"
    ) {
      statDom.innerText = statusText;
      statDom.classList.add("status-good");
    } else if (
      statusText === "FAIR" ||
      statusText === "SEDANG" ||
      statusText === "WARNING"
    ) {
      statDom.innerText = statusText;
      statDom.classList.add("status-fair");
    } else if (
      statusText === "POOR" ||
      statusText === "BURUK" ||
      statusText === "JELEK"
    ) {
      statDom.innerText = statusText;
      statDom.classList.add("status-poor");
    } else if (
      statusText === "SELESAI" ||
      statusText === "DONE" ||
      statusText === "SUDAH"
    ) {
      statDom.innerText = statusText;
      statDom.classList.add("status-done");
    }
    // Jika data tidak ditemukan (kosong atau "--")
    else {
      statDom.innerText = "--";
      // Gunakan style inline kembali hanya untuk status kosong ini
      statDom.style.backgroundColor = "#e2e3e5";
      statDom.style.color = "#383d41";
      statDom.style.border = "1px solid #d6d8db";
    }
  }

  // JANTUNG LOGIKA: Mencari baris ke atas sampai ketemu data yang BUKAN strip
  function getLastValidCondition(allData, colIndexStatus) {
    const colIndexTime = 1; // Timestamp
    const colIndexEmail = 2; // Email/Nama

    // Looping mundur dari baris paling bawah tabel
    for (let i = allData.length - 1; i >= 0; i--) {
      let valStatus = allData[i][colIndexStatus];

      if (valStatus !== null && valStatus !== undefined) {
        // Hapus tag HTML jika ada bawaan dari datatables, lalu hilangkan spasi
        let strVal = String(valStatus)
          .replace(/(<([^>]+)>)/gi, "")
          .trim();

        // Abaikan baris jika isinya kosong, "-", "--", atau "---"
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
    // Jika dicari sampai baris paling atas tetap tidak ketemu
    return { status: "--", timestamp: "--/--/----", updater: "--" };
  }

  // Mensinkronkan data saat DataTable selesai digambar
  function syncConditionTable() {
    if (typeof $ !== "undefined" && $.fn.DataTable.isDataTable("#example1")) {
      const table = $("#example1").DataTable();

      if (table.data().any()) {
        const allData = table.rows().data();

        // Mengambil index kolom yang sesuai dengan DataTables Anda
        let dataBunyi = getLastValidCondition(allData, 12);
        let dataPanel = getLastValidCondition(allData, 13);
        let dataLengkap = getLastValidCondition(allData, 14);
        let dataBersih = getLastValidCondition(allData, 15);
        let dataGround = getLastValidCondition(allData, 16);

        // Eksekusi pembaruan elemen UI di tabel kanan
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

        // Update waktu inspeksi terakhir secara global di bawah kotak
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

        const timeGlobalDom = document.getElementById("time-kondisi");
        if (timeGlobalDom) timeGlobalDom.innerText = "-";
      }
    }
  }

  // Trigger agar selalu terpanggil setiap ada refresh / pergantian motor
  if (typeof $ !== "undefined") {
    $("#example1").on("draw.dt", function () {
      syncConditionTable();
    });
  }
});
