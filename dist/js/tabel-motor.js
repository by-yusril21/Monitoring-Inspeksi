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

  for (let i = 0; i < headers.length; i++) {
    const h = String(headers[i]).toUpperCase().trim();
    for (let k of keywords) {
      if (h === k.toUpperCase().trim()) return i;
    }
  }

  for (let i = 0; i < headers.length; i++) {
    const h = String(headers[i]).toUpperCase();
    for (let k of keywords) {
      if (h.includes(k.toUpperCase())) {
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
   loadDataFromSheet — dibuat global (window)
   ------------------------------------------------------- */
window.loadDataFromSheet = function (unit, sheetName) {
  if (!unit || !sheetName) return;

  const dt = $("#example1").DataTable();
  const limitData = dt.page.len();

  dt.clear().draw();

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

      if (limitData > 0 && rows.length > limitData) {
        rows = rows.slice(rows.length - limitData);
        toastr.info(`Menampilkan ${limitData} data terakhir.`);
      } else {
        toastr.success(`Memuat ${rows.length} data.`);
      }

      /* ── Mapping kolom General ── */
      const idxTime = getColIndex(headers, "Timestamp");
      const idxEmail = getColIndex(headers, "Email");
      const idxUnit = getColIndex(headers, ["PILIH SALAH SATU", "AKSI"]);
      const idxSection1 = getColIndex(headers, "SECTION NO");
      const idxSection2 = getColIndex(headers, "SECTION NO 2");

      /* ── UPDATE: Mapping kolom Vibrasi (Dipecah jadi 8) ── */
      const idxVibDE_H = getColIndex(headers, [
        "VIBRASI DE H",
        "VIB DE H",
        "VIBRASI BEARING DE H",
      ]);
      const idxVibDE_V = getColIndex(headers, [
        "VIBRASI DE V",
        "VIB DE V",
        "VIBRASI BEARING DE V",
      ]);
      const idxVibDE_Ax = getColIndex(headers, [
        "VIBRASI DE AX",
        "VIB DE AX",
        "VIBRASI BEARING DE AX",
      ]);
      const idxVibDE_gE = getColIndex(headers, [
        "VIBRASI DE GE",
        "VIB DE GE",
        "VIBRASI BEARING DE GE",
      ]);

      const idxVibNDE_H = getColIndex(headers, [
        "VIBRASI NDE H",
        "VIB NDE H",
        "VIBRASI BEARING NDE H",
      ]);
      const idxVibNDE_V = getColIndex(headers, [
        "VIBRASI NDE V",
        "VIB NDE V",
        "VIBRASI BEARING NDE V",
      ]);
      const idxVibNDE_Ax = getColIndex(headers, [
        "VIBRASI NDE AX",
        "VIB NDE AX",
        "VIBRASI BEARING NDE AX",
      ]);
      const idxVibNDE_gE = getColIndex(headers, [
        "VIBRASI NDE GE",
        "VIB NDE GE",
        "VIBRASI BEARING NDE GE",
      ]);

      /* ── Mapping kolom Temp, Beban, Damper ── */
      const idxTempDE = getColIndex(headers, [
        "TEMPERATURE BEARING DE",
        "TEMP. BEARING DE",
      ]);
      const idxTempNDE = getColIndex(headers, [
        "TEMPERATURE BEARING NDE",
        "TEMP. BEARING NDE",
      ]);
      const idxSuhu = getColIndex(headers, ["SUHU RUANGAN", "VENTILASI"]);

      /* ── UPDATE: Mapping kolom Current (Dipecah jadi 3) ── */
      const idxCurr_R = getColIndex(headers, [
        "CURRENT R",
        "LOAD CURRENT R",
        "ARUS R",
      ]);
      const idxCurr_S = getColIndex(headers, [
        "CURRENT S",
        "LOAD CURRENT S",
        "ARUS S",
      ]);
      const idxCurr_T = getColIndex(headers, [
        "CURRENT T",
        "LOAD CURRENT T",
        "ARUS T",
      ]);

      const idxBeban = getColIndex(headers, "BEBAN GENERATOR");
      const idxDamper = getColIndex(headers, "OPENING DAMPER");

      /* ── Mapping Status Visual ── */
      const idxBunyi = getColIndex(headers, "BUNYI MOTOR");
      const idxPanel = getColIndex(headers, "PANEL LOCAL");
      const idxKelengkapan = getColIndex(headers, "KELENGKAPAN");
      const idxKebersihan = getColIndex(headers, "KEBERSIHAN");
      const idxGrounding = getColIndex(headers, ["GROUNDING", "PENTANAHAN"]);
      const idxRegreasing = getColIndex(headers, "REGREASING");
      const idxAction = getColIndex(headers, "ACTIONS");

      /* ── Format baris (Total 28 Kolom) ── */
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
          safeGet(row, idxVibDE_H), // 5
          safeGet(row, idxVibDE_V), // 6
          safeGet(row, idxVibDE_Ax), // 7
          safeGet(row, idxVibDE_gE), // 8
          safeGet(row, idxVibNDE_H), // 9
          safeGet(row, idxVibNDE_V), // 10
          safeGet(row, idxVibNDE_Ax), // 11
          safeGet(row, idxVibNDE_gE), // 12
          safeGet(row, idxTempDE), // 13
          safeGet(row, idxTempNDE), // 14
          safeGet(row, idxSuhu), // 15
          safeGet(row, idxCurr_R), // 18
          safeGet(row, idxCurr_S), // 19
          safeGet(row, idxCurr_T), // 20
          safeGet(row, idxBeban), // 16
          safeGet(row, idxDamper), // 17
          safeGet(row, idxBunyi), // 21
          safeGet(row, idxPanel), // 22
          safeGet(row, idxKelengkapan), // 23
          safeGet(row, idxKebersihan), // 24
          safeGet(row, idxGrounding), // 25
          safeGet(row, idxRegreasing), // 26
          actionVal && actionVal !== "-"
            ? actionVal
            : '<span class="badge badge-success">Tercatat</span>', // 27
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
          // UPDATE: Render index kolom 0 sampai 27 (28 kolom)
          columns: [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18,
            19, 20, 21, 22, 23, 24, 25, 26, 27,
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
          // UPDATE: Render index kolom 0 sampai 27 (28 kolom)
          columns: [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18,
            19, 20, 21, 22, 23, 24, 25, 26, 27,
          ],
          format: {
            header: function (data, columnIdx) {
              // UPDATE: Penamaan header untuk PDF harus persis 28 item agar tidak error
              const customHeaders = [
                "No",
                "Date",
                "Update By",
                "Aksi",
                "Section\nNo",
                "Vib DE\nH",
                "Vib DE\nV",
                "Vib DE\nAx",
                "Vib DE\ngE",
                "Vib NDE\nH",
                "Vib NDE\nV",
                "Vib NDE\nAx",
                "Vib NDE\ngE",
                "Temp\nDE",
                "Temp\nNDE",
                "Temp\nRuang",
                "Load\nCurrent R",
                "Load\nCurrent S",
                "Load\nCurrent T",
                "Beban\nGenerator(MW)",
                "Opening\nDamper",
                "Bunyi\nMotor",
                "Kondisi\nPanel",
                "kelengkapan\nMotor",
                "Kebersihan\nMotor",
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
        title: "",
        customize: function (doc) {
          doc.watermark = null;

          let judul1Text =
            $("#judul-1-pdf").text().trim() ||
            "DOKUMEN RANGKUMAN DATA PMC SCHEDULE BULANAN MOTOR";
          let judul2Text =
            $("#judul-2-pdf").text().trim() ||
            "PT Semen Tonasa - Electrical of Power Plant Elins Maintenance";
          let logoBase64 = $("#logo-base64-pdf").text().trim();

          let unitText = $("#pilihUnit option:selected").text() || "Data Unit";
          if (unitText.toUpperCase().includes("PILIH")) unitText = "Data Unit";

          let motorText =
            $("#pilihMotor option:selected").text() || "Data Motor";
          if (motorText.toUpperCase().includes("PILIH"))
            motorText = "Data Motor";

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
          let subHeaderString =
            "UNIT : " + unitText + "  -  MOTOR : " + motorText;

          if (logoBase64 !== "" && logoBase64.indexOf("data:image") === 0) {
            doc.background = function (page) {
              return [
                {
                  table: {
                    widths: ["*"],
                    heights: [595],
                    body: [
                      [
                        {
                          image: logoBase64,
                          width: 450,
                          opacity: 0.2,
                          alignment: "center",
                          margin: [0, 80, 0, 0],
                          border: [false, false, false, false],
                        },
                      ],
                    ],
                  },
                },
              ];
            };
          }

          let headerColumns = [
            {
              stack: [
                {
                  text: judul1Text,
                  fontSize: 10,
                  bold: true,
                  alignment: "left",
                },
                {
                  text: judul2Text,
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
                    x2: 801.89,
                    y2: 0,
                    lineWidth: 1.5,
                    lineColor: "#cda434",
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
            margin: [0, 0, 0, 10],
          };

          doc.content.splice(0, 0, customHeader);

          doc.defaultStyle.fontSize = 4;
          doc.styles.tableHeader.fontSize = 3.5;
          doc.styles.tableHeader.fillColor = "#1a3b5c";
          doc.styles.tableHeader.color = "#ffffff";
          doc.defaultStyle.alignment = "center";
          doc.pageMargins = [20, 20, 20, 20];

          // UPDATE: Mengatur lebar 28 kolom agar pas dan presisi di kertas PDF Landscape (Total = 100%)
          doc.content[1].table.widths = [
            "1%",
            "5%",
            "8%",
            "5%",
            "4%", // 1-5
            "2%",
            "2%",
            "2%",
            "2%", // Vib DE
            "2%",
            "2%",
            "2%",
            "2%", // Vib NDE
            "2%",
            "2%",
            "2%", // Temp
            "2%",
            "2%", // Gen, Damper
            "2%",
            "3%",
            "3%", // Arus R S T
            "3%",
            "3%",
            "3.5%",
            "3%",
            "3%",
            "3%", // Visual & Status
            "25%", // Action
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

    initComplete: function () {
      const filterContent = $("#my-filter-source").html();
      if (filterContent) {
        $("#my-filter-placeholder").html(filterContent);
        $("#my-filter-source").remove();
      }
    },
  });

  $("#example1").on("length.dt", function () {
    const unit = localStorage.getItem("mon_selectedUnit");
    const motor = localStorage.getItem("mon_selectedMotor");
    if (unit && motor) window.loadDataFromSheet(unit, motor);
  });
});
