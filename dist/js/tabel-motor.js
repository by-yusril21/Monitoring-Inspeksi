/* =======================================================
   tabel-motor.js
   Inisialisasi DataTable + Fungsi Load Data dari Sheet + Ekspor PDF Proporsional
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
   Inisialisasi DataTable & Export PDF Custom
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
        exportOptions: {
          columns: [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18,
            19,
          ],
        },
      },
      {
        extend: "pdfHtml5",
        text: '<i class="fas fa-file-pdf"></i> PDF',
        className: "btn btn-danger btn-sm",
        orientation: "landscape",
        pageSize: "A4",
        exportOptions: {
          columns: [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18,
            19,
          ],
          format: {
            header: function (data, columnIdx) {
              const customHeaders = [
                "No",
                "Date",
                "Update By",
                "Aksi",
                "Section\nNo",
                "Vib DE\n(mm/s)",
                "Vib NDE\n(mm/s)",
                "Temp DE\n(°C)",
                "Temp NDE\n(°C)",
                "Temp\nRuang\n(°C)",
                "Beban\nGen\n(MW)",
                "Opening\nDamper\n(%)",
                "Load\nCurrent\n(A)",
                "Bunyi\nMotor",
                "Kondisi\nPanel",
                "Lengkap\nMotor",
                "Bersih\nMotor",
                "Grounding\nMotor",
                "Regreasing\nMotor",
                "Action",
              ];
              return customHeaders[columnIdx];
            },
            body: function (data, row, column, node) {
              return data ? data.toString().replace(/<[^>]*>?/gm, "") : data;
            },
          },
        },
        title: "", // Title bawaan dikosongkan karena pakai custom header
        customize: function (doc) {
          // Hapus watermark teks bawaan
          doc.watermark = null;

          // =======================================================
          // 1. BACA DATA DARI JEMBATAN HTML (HASIL QUERY DATABASE)
          // =======================================================
          let judul1Text =
            $("#judul-1-pdf").text().trim() ||
            "DOKUMEN RANGKUMAN DATA PMC SCHEDULE BULANAN MOTOR";
          let judul2Text =
            $("#judul-2-pdf").text().trim() ||
            "PT Semen Tonasa - Electrical of Power Plant Elins Maintenance";
          let logoBase64 = $("#logo-base64-pdf").text().trim(); // Mengambil string Base64 gambar

          // =======================================================
          // 2. BACA DATA UNIT & MOTOR DARI DROPDOWN
          // =======================================================
          let unitText = $("#pilihUnit option:selected").text() || "Data Unit";
          if (unitText.toUpperCase().includes("PILIH")) {
            unitText = "Data Unit";
          }

          let motorText =
            $("#pilihMotor option:selected").text() || "Data Motor";
          if (motorText.toUpperCase().includes("PILIH")) {
            motorText = "Data Motor";
          }

          // =======================================================
          // 3. WAKTU & NAMA USER
          // =======================================================
          let currentUser = $("#nama-user-login").text().trim() || "Admin";
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

          // String Sub Header
          let subHeaderString =
            "UNIT : " + unitText + "  -  MOTOR : " + motorText;

          // =======================================================
          // 4. PEMBUATAN WATERMARK LOGO DI TENGAH
          // =======================================================
          // Validasi sederhana: pastikan string base64 mengandung 'data:image' agar tidak error
          if (logoBase64 !== "" && logoBase64.indexOf("data:image") === 0) {
            doc.background = function (page) {
              return [
                {
                  table: {
                    widths: ["*"],
                    heights: [595], // Tinggi kertas A4 Landscape
                    body: [
                      [
                        {
                          image: logoBase64,
                          width: 450, // Lebar logo watermark
                          opacity: 0.2, // Transparansi
                          alignment: "center",
                          margin: [0, 80, 0, 0], // Geser sedikit ke bawah
                          border: [false, false, false, false],
                        },
                      ],
                    ],
                  },
                },
              ];
            };
          }

          // =======================================================
          // 5. PEMBUATAN HEADER KOP SURAT
          // =======================================================
          let headerColumns = [
            {
              stack: [
                {
                  text: judul1Text, // Teks dinamis dari database
                  fontSize: 10,
                  bold: true,
                  alignment: "left",
                },
                {
                  text: judul2Text, // Teks dinamis dari database
                  fontSize: 9,
                  bold: true,
                  alignment: "left",
                  margin: [0, 2, 0, 0],
                },
                {
                  text: infoString,
                  fontSize: 8,
                  alignment: "left",
                  margin: [0, 2, 0, 2],
                },
              ],
              width: "*",
              alignment: "left",
            },
          ];

          // Menambahkan logo kecil di pojok kanan atas kop surat
          if (logoBase64 !== "" && logoBase64.indexOf("data:image") === 0) {
            headerColumns.push({
              image: logoBase64,
              width: 45,
              alignment: "right",
            });
          }

          let customHeader = {
            stack: [
              { columns: headerColumns },
              {
                canvas: [
                  {
                    type: "line",
                    x1: 0,
                    y1: 0,
                    x2: 801.89, // Lebar full A4 Landscape dengan margin 20pt
                    y2: 0,
                    lineWidth: 1.5,
                    lineColor: "#cda434", // Warna Kuning Emas
                  },
                ],
                margin: [0, 6, 0, 6],
              },
              {
                text: subHeaderString,
                fontSize: 8,
                color: "#555",
                bold: true,
                alignment: "left",
                margin: [0, 0, 0, 5],
              },
            ],
            margin: [0, 0, 0, 10], // Spasi antara header dan tabel
          };

          // Menyuntikkan custom header ke dalam dokumen PDF
          doc.content.splice(0, 0, customHeader);

          // =======================================================
          // 6. STYLING TABEL PDF
          // =======================================================
          doc.defaultStyle.fontSize = 5; // Ukuran font isi tabel
          doc.styles.tableHeader.fontSize = 4; // Ukuran font header tabel
          doc.styles.tableHeader.fillColor = "#1a3b5c"; // Warna background biru gelap
          doc.styles.tableHeader.color = "#ffffff"; // Warna tulisan header putih
          doc.defaultStyle.alignment = "center";
          doc.pageMargins = [20, 20, 20, 20]; // Margin halaman PDF

          doc.content[1].table.widths = [
            "1%",
            "8%",
            "13%",
            "5%",
            "5%",
            "3%",
            "3%",
            "3%",
            "3%",
            "3%",
            "3%",
            "3%",
            "3%",
            "3%",
            "3%",
            "3.5%",
            "3%",
            "3%",
            "3.5%",
            "25%",
          ];

          doc.content[1].layout = {
            hLineWidth: function (i) {
              return 0.5;
            },
            vLineWidth: function (i) {
              return 0.5;
            },
            hLineColor: function (i) {
              return "#aaa";
            },
            vLineColor: function (i) {
              return "#aaa";
            },
            paddingLeft: function (i) {
              return 3;
            },
            paddingRight: function (i) {
              return 3;
            },
          };
        },
      },
    ],

    // Bahasa dan teks UI bawaan DataTables
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

    // Memindahkan filter custom jika ada
    initComplete: function () {
      const filterContent = $("#my-filter-source").html();
      if (filterContent) {
        $("#my-filter-placeholder").html(filterContent);
        $("#my-filter-source").remove();
      }
    },
  });

  // Reload data saat page-length (Jumlah baris yang ditampilkan) berubah
  $("#example1").on("length.dt", function () {
    const unit = localStorage.getItem("mon_selectedUnit");
    const motor = localStorage.getItem("mon_selectedMotor");
    if (unit && motor) window.loadDataFromSheet(unit, motor);
  });
});
