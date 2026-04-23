/* =======================================================
   tabel-motor.js
   Inisialisasi DataTable Bawaan (Aman, Bersih, Sejajar, & Tata Letak Rapi)
   ======================================================= */

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

function safeGet(row, index) {
  return index < 0 || !row[index] ? "-" : row[index];
}

window.loadDataFromSheet = function (unit, sheetName) {
  if (!unit || !sheetName) return;

  const dt = $("#example1").DataTable();
  const limitData = dt.page.len();

  // =======================================================
  // FITUR LOADING: Tampilkan overlay sebelum fetch berjalan
  // =======================================================
  const card = $("#example1").closest(".card");
  if (card.find(".loading-overlay").length === 0) {
    card.append(`
      <div class="overlay loading-overlay" style="background-color: rgba(255,255,255,0.85); z-index: 9999; flex-direction: column;">
          <i class="fas fa-sync-alt fa-spin fa-3x text-info"></i>
          <div class="mt-3 text-info font-weight-bold" style="letter-spacing: 1px;">Mengambil Data...</div>
      </div>
    `);
  }

  dt.clear().draw();

  const url = `api/api_proxy.php?unit=${unit}&sheet=${encodeURIComponent(sheetName)}`;

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      // Hilangkan loading jika data kosong
      if (!Array.isArray(data) || data.length === 0) {
        card.find(".loading-overlay").remove();
        toastr.warning("Data tidak ditemukan.");
        return;
      }

      const headers = data[0];
      let rows = data.slice(1);

      if (rows.length === 0) {
        card.find(".loading-overlay").remove();
        toastr.info("Data kosong.");
        return;
      }

      if (limitData > 0 && rows.length > limitData) {
        rows = rows.slice(rows.length - limitData);
        toastr.info(`Menampilkan ${limitData} data terakhir.`);
      } else {
        toastr.success(`Memuat ${rows.length} data.`);
      }

      const idxTime = getColIndex(headers, ["Timestamp", "WAKTU"]);
      const idxEmail = getColIndex(headers, ["Email", "EMAIL ADDRESS"]);
      const idxUnit = getColIndex(headers, ["PILIH SALAH SATU", "AKSI"]);
      const idxSection1 = getColIndex(headers, "SECTION NO");
      const idxSection2 = getColIndex(headers, "SECTION NO 2");

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

      const idxTempDE = getColIndex(headers, [
        "TEMPERATURE BEARING DE",
        "TEMP. BEARING DE",
        "TEMP DE",
      ]);
      const idxTempNDE = getColIndex(headers, [
        "TEMPERATURE BEARING NDE",
        "TEMP. BEARING NDE",
        "TEMP NDE",
      ]);
      const idxSuhu = getColIndex(headers, ["SUHU RUANGAN", "VENTILASI"]);

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

      const idxBeban = getColIndex(headers, ["BEBAN GENERATOR", "BEBAN"]);
      const idxDamper = getColIndex(headers, ["OPENING DAMPER", "DAMPER"]);

      const idxBunyi = getColIndex(headers, ["BUNYI MOTOR", "BUNYI"]);
      const idxPanel = getColIndex(headers, ["PANEL LOCAL", "PANEL"]);
      const idxKelengkapan = getColIndex(headers, ["KELENGKAPAN"]);
      const idxKebersihan = getColIndex(headers, ["KEBERSIHAN"]);
      const idxGrounding = getColIndex(headers, ["GROUNDING", "PENTANAHAN"]);
      const idxRegreasing = getColIndex(headers, ["REGREASING", "REGREASE"]);
      const idxAction = getColIndex(headers, [
        "ACTIONS",
        "ACTION",
        "KETERANGAN",
      ]);

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
          safeGet(row, idxVibDE_H),
          safeGet(row, idxVibDE_V),
          safeGet(row, idxVibDE_Ax),
          safeGet(row, idxVibDE_gE),
          safeGet(row, idxVibNDE_H),
          safeGet(row, idxVibNDE_V),
          safeGet(row, idxVibNDE_Ax),
          safeGet(row, idxVibNDE_gE),
          safeGet(row, idxTempDE),
          safeGet(row, idxTempNDE),
          safeGet(row, idxSuhu),
          safeGet(row, idxCurr_R),
          safeGet(row, idxCurr_S),
          safeGet(row, idxCurr_T),
          safeGet(row, idxBeban),
          safeGet(row, idxDamper),
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

      setTimeout(() => {
        dt.columns.adjust();
        // Hilangkan loading setelah tabel selesai dirender
        card.find(".loading-overlay").remove();
      }, 150);
    })
    .catch((error) => {
      console.error("Error:", error);
      // Hilangkan loading jika error
      card.find(".loading-overlay").remove();
      toastr.error("Gagal koneksi ke server.");
    });
};

$(document).ready(function () {
  const table = $("#example1").DataTable({
    dom:
      "<'row mb-1 align-items-center'<'col-auto pr-0'f><'col-auto pl-0'l><'col d-flex justify-content-end'B>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

    scrollX: true,
    scrollY: "50vh",
    scrollCollapse: true,
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: false,
    info: true,
    autoWidth: false,

    columnDefs: [
      { defaultContent: "-", targets: "_all" },
      { className: "col-no", targets: 0 },
      { className: "col-date", targets: 1 },
      { className: "col-update", targets: 2 },
      { className: "col-aksi", targets: [3, 4] },
      { className: "col-vib", targets: [5, 6, 7, 8, 9, 10, 11, 12] },
      { className: "col-temp", targets: [13, 14, 15, 16, 17, 18] },
      { className: "col-load", targets: [19, 20] },
      { className: "col-kondisi", targets: [21, 22, 23, 24, 25, 26] },
      { className: "col-action", targets: 27 },
    ],

    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "Semua"],
    ],

    buttons: [
      {
        text: '<i class="fas fa-file-excel"></i> Excel',
        className: "btn btn-success btn-sm ml-2",
        action: function (e, dt, node, config) {
          const unit = localStorage.getItem("mon_selectedUnit") || "Unit";
          const motor = localStorage.getItem("mon_selectedMotor") || "Motor";
          exportMotorToExcel(unit, motor);
        },
      },
      {
        extend: "pdfHtml5",
        text: '<i class="fas fa-file-pdf"></i> PDF',
        className: "btn btn-danger btn-sm ml-1",
        orientation: "landscape",
        pageSize: "A4",
        exportOptions: {
          columns: [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18,
            19, 20, 21, 22, 23, 24, 25, 26, 27,
          ],
          format: {
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
                          opacity: 0.06,
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

          if (doc.styles.tableBodyEven) {
            delete doc.styles.tableBodyEven.fillColor;
          }
          if (doc.styles.tableBodyOdd) {
            delete doc.styles.tableBodyOdd.fillColor;
          }

          const headerRow1 = [
            {
              text: "No",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Date",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Update By",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Aksi",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Section\nNo",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            { text: "Vibrasi (mm/s)", colSpan: 8, style: "tableHeader" },
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            { text: "Temp (°C)", colSpan: 3, style: "tableHeader" },
            "",
            "",
            { text: "Load Current (A)", colSpan: 3, style: "tableHeader" },
            "",
            "",
            {
              text: "Load Generator\n(MW)",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Opening\nDamper (%)",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Bunyi\nMotor",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Kondisi\nPanel",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Kelengkapan\nMotor",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Kebersihan\nMotor",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Grounding\nMotor",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Regreasing\nBearing",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
            {
              text: "Action",
              rowSpan: 3,
              style: "tableHeader",
              margin: [0, 4, 0, 0],
            },
          ];

          const headerRow2 = [
            "",
            "",
            "",
            "",
            "",
            { text: "DE", colSpan: 4, style: "tableHeader" },
            "",
            "",
            "",
            { text: "NDE", colSpan: 4, style: "tableHeader" },
            "",
            "",
            "",
            {
              text: "DE",
              rowSpan: 2,
              style: "tableHeader",
              margin: [0, 2, 0, 0],
            },
            {
              text: "NDE",
              rowSpan: 2,
              style: "tableHeader",
              margin: [0, 2, 0, 0],
            },
            {
              text: "Ruang",
              rowSpan: 2,
              style: "tableHeader",
              margin: [0, 2, 0, 0],
            },
            {
              text: "R",
              rowSpan: 2,
              style: "tableHeader",
              margin: [0, 2, 0, 0],
            },
            {
              text: "S",
              rowSpan: 2,
              style: "tableHeader",
              margin: [0, 2, 0, 0],
            },
            {
              text: "T",
              rowSpan: 2,
              style: "tableHeader",
              margin: [0, 2, 0, 0],
            },
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
          ];

          const headerRow3 = [
            "",
            "",
            "",
            "",
            "",
            { text: "H", style: "tableHeader" },
            { text: "V", style: "tableHeader" },
            { text: "Ax", style: "tableHeader" },
            { text: "gE", style: "tableHeader" },
            { text: "H", style: "tableHeader" },
            { text: "V", style: "tableHeader" },
            { text: "Ax", style: "tableHeader" },
            { text: "gE", style: "tableHeader" },
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
          ];

          doc.content[1].table.body.splice(0, 1);
          doc.content[1].table.body.unshift(headerRow3);
          doc.content[1].table.body.unshift(headerRow2);
          doc.content[1].table.body.unshift(headerRow1);
          doc.content[1].table.headerRows = 3;

          doc.content[1].table.widths = [
            "1%",
            "5%",
            "8%",
            "5%",
            "4%",
            "2%",
            "2%",
            "2%",
            "2%",
            "2%",
            "2%",
            "2%",
            "2%",
            "2%",
            "2%",
            "2%",
            "2%",
            "2%",
            "2%",
            "3%",
            "3%",
            "3%",
            "3%",
            "3.5%",
            "3%",
            "3%",
            "3%",
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

          doc.content[1].table.body.forEach(function (row, index) {
            if (index >= 3) {
              if (typeof row[27] === "object" && row[27] !== null) {
                row[27].alignment = "left";
              } else {
                row[27] = { text: row[27], alignment: "left" };
              }
            }
          });
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

      setTimeout(function () {
        $("#area-tabel").css("opacity", "1");
      }, 100);
    },
  });

  $("#example1").on("length.dt", function () {
    const unit = localStorage.getItem("mon_selectedUnit");
    const motor = localStorage.getItem("mon_selectedMotor");
    if (unit && motor) window.loadDataFromSheet(unit, motor);
  });

  $(window).on("resize", function () {
    if ($.fn.DataTable.isDataTable("#example1")) {
      table.columns.adjust();
    }
  });
});

function exportMotorToExcel(unit, motor) {
  const headerHTML = $(".dataTables_scrollHeadInner table thead").html();
  let bodyHTML = $(".dataTables_scrollBody table tbody").html();

  if (!headerHTML || !bodyHTML) {
    toastr.error("Gagal mengambil struktur tabel.");
    return;
  }

  let tempContainer = document.createElement("div");
  tempContainer.innerHTML = `<table><tbody>${bodyHTML}</tbody></table>`;

  let trs = tempContainer.querySelectorAll("tr");
  trs.forEach((tr) => {
    let tds = tr.querySelectorAll("td");
    if (tds.length > 27) {
      tds[27].style.whiteSpace = "normal";
      tds[27].style.wordWrap = "break-word";
      tds[27].style.textAlign = "left";
    }
  });
  bodyHTML = tempContainer.querySelector("tbody").innerHTML;

  const colWidths = [
    40, 180, 250, 150, 150, 50, 50, 50, 50, 50, 50, 50, 50, 60, 60, 60, 120,
    120, 120, 150, 150, 120, 120, 120, 120, 120, 120, 500,
  ];

  let colgroup = "<colgroup>";
  colWidths.forEach((w) => {
    colgroup += `<col style="width: ${w}px;">`;
  });
  colgroup += "</colgroup>";

  const fullTableHTML = `
        <table border="1">
            ${colgroup}
            <thead>${headerHTML}</thead>
            <tbody>${bodyHTML}</tbody>
        </table>`;

  let cleanHTML = fullTableHTML.replace(/<th[^>]*><\/th>/g, "");

  const template = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <style>
                table { border-collapse: collapse; table-layout: fixed; } 
                th, td { 
                    border: 1px solid black; 
                    padding: 5px; 
                    text-align: center; 
                    vertical-align: middle;
                }
                thead tr:nth-child(1) th { background-color: #e2e6ea; font-weight: bold; }
                thead tr:nth-child(2) th { background-color: #f1f3f5; }
                thead tr:nth-child(3) th { background-color: #f8f9fa; }
                .col-action { text-align: left !important; white-space: normal !important; } 
            </style>
        </head>
        <body>
            <h3 style="text-align:left;">DOKUMEN RANGKUMAN DATA PMC SCHEDULE BULANAN MOTOR</h3>
            <p style="text-align:left;">UNIT: ${unit} - MOTOR: ${motor}</p>
            ${cleanHTML}
        </body>
        </html>`;

  const blob = new Blob(["\uFEFF" + template], {
    type: "application/vnd.ms-excel",
  });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `Rekap_Motor_${motor}_${unit}.xls`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}
