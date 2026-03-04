/* =======================================================
   tabel-motor.js
   Inisialisasi DataTable + Fungsi Load Data dari Sheet
   Membutuhkan: config.js (dimuat lebih dulu)
   ======================================================= */

/* -------------------------------------------------------
   Helper: ambil index kolom berdasarkan keyword
   (DIPERBAIKI: Menggunakan Exact Match terlebih dahulu, lalu Filter Kata)
   ------------------------------------------------------- */
function getColIndex(headers, keywords) {
  if (!Array.isArray(keywords)) keywords = [keywords];

  // Tahap 1: Cari Exact Match (Kecocokan Sempurna) terlebih dahulu
  for (let i = 0; i < headers.length; i++) {
    const h = String(headers[i]).toUpperCase().trim();
    for (let k of keywords) {
      if (h === k.toUpperCase().trim()) return i;
    }
  }

  // Tahap 2: Jika Exact Match tidak ketemu, pakai Includes dengan proteksi
  for (let i = 0; i < headers.length; i++) {
    const h = String(headers[i]).toUpperCase();
    for (let k of keywords) {
      if (h.includes(k.toUpperCase())) {
        // Proteksi agar Temp dan Vibrasi tidak bertabrakan
        if (k.toUpperCase().includes("VIBRASI") && h.includes("TEMP")) continue;
        if (k.toUpperCase().includes("TEMP") && h.includes("VIBRASI")) continue;
        return i;
      }
    }
  }
  return -1;
}

/* -------------------------------------------------------
   Helper: ambil nilai aman dari row
   ------------------------------------------------------- */
function safeGet(row, index) {
  return index < 0 || !row[index] ? "-" : row[index];
}

/* -------------------------------------------------------
   loadDataFromSheet — dibuat global (window) agar bisa
   dipanggil dari navbar.js
   ------------------------------------------------------- */
window.loadDataFromSheet = function (unit, sheetName) {
  if (!unit || !sheetName) return;

  const dt = $("#example1").DataTable();
  const limitData = dt.page.len();

  dt.clear().draw();

  // PENTING: Arahkan URL ke file PHP Proxy di folder api
  const url = `api/api_proxy.php?unit=${unit}&sheet=${encodeURIComponent(sheetName)}`;

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (!Array.isArray(data) || data.length === 0) {
        toastr.warning("Data tidak ditemukan.");
        return;
      }

      const headers = data[0];
      let rows = data.slice(1);

      if (rows.length === 0) {
        toastr.info("Data kosong.");
        return;
      }

      /* Batasi tampilan sesuai page length */
      if (limitData > 0 && rows.length > limitData) {
        rows = rows.slice(rows.length - limitData);
        toastr.info(`Menampilkan ${limitData} data terakhir.`);
      } else {
        toastr.success(`Memuat ${rows.length} data.`);
      }

      /* ── Mapping kolom ── */
      const idxTime = getColIndex(headers, "Timestamp");
      const idxEmail = getColIndex(headers, "Email");
      const idxUnit = getColIndex(headers, "PILIH SALAH SATU");
      const idxSection1 = getColIndex(headers, "SECTION NO");
      const idxSection2 = getColIndex(headers, "SECTION NO 2");

      // Vibrasi dipecah menjadi 2
      const idxVibrasiDE = getColIndex(headers, [
        "VIBRASI BEARING DE",
        "VIBRASI DE",
      ]);
      const idxVibrasiNDE = getColIndex(headers, [
        "VIBRASI BEARING NDE",
        "VIBRASI NDE",
      ]);

      const idxTempDE = getColIndex(headers, [
        "TEMPERATURE BEARING DE",
        "TEMP. BEARING DE",
      ]);
      const idxTempNDE = getColIndex(headers, [
        "TEMPERATURE BEARING NDE",
        "TEMP. BEARING NDE",
      ]);
      const idxSuhu = getColIndex(headers, ["SUHU RUANGAN", "VENTILASI"]);
      const idxBeban = getColIndex(headers, "BEBAN GENERATOR");
      const idxDamper = getColIndex(headers, "OPENING DAMPER");
      const idxCurrent = getColIndex(headers, "LOAD CURRENT");
      const idxBunyi = getColIndex(headers, "BUNYI MOTOR");
      const idxPanel = getColIndex(headers, "PANEL LOCAL");
      const idxKelengkapan = getColIndex(headers, "KELENGKAPAN");
      const idxKebersihan = getColIndex(headers, "KEBERSIHAN");
      const idxGrounding = getColIndex(headers, ["GROUNDING", "PENTANAHAN"]);
      const idxRegreasing = getColIndex(headers, "REGREASING");
      const idxAction = getColIndex(headers, "ACTIONS");

      /* ── Format baris ── */
      const formattedData = rows.map((row, index) => {
        let valSection = safeGet(row, idxSection1);
        if (!valSection || valSection === "-" || valSection === "") {
          valSection = safeGet(row, idxSection2);
        }

        const actionVal = safeGet(row, idxAction);

        return [
          index + 1,
          safeGet(row, idxTime),
          safeGet(row, idxEmail),
          safeGet(row, idxUnit),
          valSection,
          safeGet(row, idxVibrasiDE), // Kolom 5
          safeGet(row, idxVibrasiNDE), // Kolom 6
          safeGet(row, idxTempDE), // Kolom 7
          safeGet(row, idxTempNDE), // Kolom 8
          safeGet(row, idxSuhu), // Kolom 9
          safeGet(row, idxBeban), // Kolom 10
          safeGet(row, idxDamper), // Kolom 11
          safeGet(row, idxCurrent), // Kolom 12
          safeGet(row, idxBunyi),
          safeGet(row, idxPanel),
          safeGet(row, idxKelengkapan),
          safeGet(row, idxKebersihan),
          safeGet(row, idxGrounding),
          safeGet(row, idxRegreasing),
          actionVal && actionVal !== "-"
            ? actionVal
            : '<span class="badge badge-success">Tercatat</span>',
        ];
      });

      dt.rows.add(formattedData).draw();
    })
    .catch((error) => {
      console.error("Error:", error);
      toastr.error("Gagal koneksi ke server.");
    });
};

/* =======================================================
   Inisialisasi DataTable
   ======================================================= */
$(document).ready(function () {
  $("#example1").DataTable({
    dom:
      "<'row m-0 bg-white border-bottom-0 p-2 align-items-center'" +
      "<'col-sm-12 col-md-6 d-flex align-items-center' <'#my-filter-placeholder'>>" +
      "<'col-sm-12 col-md-6 d-flex justify-content-end align-items-center' f l B >>" +
      "<'row m-0'<'col-12 p-0'tr>>" +
      "<'row m-0 p-2 bg-white'<'col-md-5'i><'col-md-7'p>>",

    responsive: false,
    scrollX: true,
    lengthChange: true,
    autoWidth: false,
    searching: true,
    paging: true,
    info: true,
    processing: true,
    ordering: false,

    columnDefs: [{ defaultContent: "-", targets: "_all" }],

    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "Semua"],
    ],

    buttons: [
      {
        extend: "excel",
        text: '<i class="fas fa-file-excel"></i> Excel',
        className: "btn btn-success btn-sm",
      },
    ],

    language: {
      search: "",
      searchPlaceholder: "Cari data...",
      lengthMenu: "_MENU_",
      processing: "<i class='fas fa-spinner fa-spin'></i> Loading...",
      emptyTable: "Silakan Pilih Unit dan Motor terlebih dahulu",
      zeroRecords: "Data tidak ditemukan",
      info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
      paginate: { previous: "Kembali", next: "Lanjut" },
    },

    /* Pindahkan filter dari #my-filter-source ke placeholder DataTable */
    initComplete: function () {
      const filterContent = $("#my-filter-source").html();
      if (filterContent) {
        $("#my-filter-placeholder").html(filterContent);
        $("#my-filter-source").remove();
      }
    },
  });

  /* Reload data saat page-length berubah */
  $("#example1").on("length.dt", function () {
    const unit = localStorage.getItem("mon_selectedUnit");
    const motor = localStorage.getItem("mon_selectedMotor");
    if (unit && motor) window.loadDataFromSheet(unit, motor);
  });
});
