window.SCRIPT_URLS = {
  C6KV: "https://script.google.com/macros/s/AKfycbwc72bVBL0w12SYeD_oYeyA9GFI2e4nA2PgiIcald9gb7KyuekfzkOD_EhvystikAc/exec",
  C380: "https://script.google.com/macros/s/AKfycbw3Jw1GMtoIHeePHQv_hy6oeY7TkIPjdI4n9VI2m6T91WeztL5WDpA8VBbbQCr_OKVO/exec",
  D6KV: "",
  D380: "",
  UTILITY: "",
};
const API_TOKEN = "SemenTonasa2026";

const dataMotor = {
  C6KV: [
    "BOILER FEED WATER PUMP A",
    "BOILER FEED WATER PUMP B",
    "COAL MILL C",
    "FORCED DRAFT FAN C",
    "PULVERIZED FAN C",
    "INDUCED DRAFT FAN C",
    "VENT GAS FAN C",
    "SEA WATER INTAKE PUMP A",
    "SEA WATER INTAKE PUMP C",
  ],
  C380: [
    "EJECTOR PUMP A",
    "EJECTOR PUMP B",
    "PULVERIZED COAL FAN C",
    "MILL SEAL AIR FAN C",
    "CONDENSATE PUMP A",
    "CONDENSATE PUMP B",
    "IGNITER AIR FAN C",
    "BLOWER PFISTER C",
    "GAS AIR HEATER C",
  ],
  D6KV: ["BOILER FEED WATER PUMP D-A", "BOILER FEED WATER PUMP D-B"],
  D380: ["CONDENSATE PUMP D-A", "CONDENSATE PUMP D-B"],
  UTILITY: [
    "COMPRESSOR HOUSE",
    "CHLORINATION PLANT",
    "WATER TREATMENT PLANT",
    "WASTE WATER TREATMENT PLANT",
    "AUXILIARY BOILER",
    "EMERGENCY DIESEL GENERATOR",
  ],
};

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
    ordering: false, // Sorting Mati Total

    columnDefs: [
      { defaultContent: "-", targets: "_all" },
      // Kita tidak set width di JS, tapi mengandalkan padding CSS di dashboard.php
    ],

    lengthMenu: [
      [5, 10, 25, 50, -1],
      [5, 10, 25, 50, "Semua"],
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

    initComplete: function () {
      var filterContent = $("#my-filter-source").html();
      if (filterContent) {
        $("#my-filter-placeholder").html(filterContent);
        $("#my-filter-source").remove();
        bindFilterEvents();
      }
    },
  });

  $("#example1").on("length.dt", function (e, settings, len) {
    const currentUnit = localStorage.getItem("mon_selectedUnit");
    const currentMotor = localStorage.getItem("mon_selectedMotor");
    if (currentUnit && currentMotor) {
      loadDataFromSheet(currentUnit, currentMotor);
    }
  });

  function loadDataFromSheet(unit, sheetName) {
    if (!unit || !sheetName) return;
    const targetURL = SCRIPT_URLS[unit];
    if (!targetURL) {
      toastr.error("Link Database belum ada.");
      return;
    }

    var dt = $("#example1").DataTable();
    var limitData = dt.page.len();

    dt.clear().draw();

    const url = `${targetURL}?token=${API_TOKEN}&sheet=${encodeURIComponent(sheetName)}`;

    fetch(url)
      .then((response) => response.json())
      .then((data) => {
        if (!Array.isArray(data) || data.length === 0) {
          toastr.warning("Data tidak ditemukan.");
          return;
        }

        const headers = data[0];
        var rows = data.slice(1);

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

        const idxTime = getColIndex(headers, "Timestamp");
        const idxEmail = getColIndex(headers, "Email");
        const idxUnit = getColIndex(headers, "PILIH SALAH SATU");
        const idxSection1 = getColIndex(headers, "SECTION NO");
        const idxSection2 = getColIndex(headers, "SECTION NO 2");

        const idxVibrasi = getColIndex(headers, "VIBRASI");
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

        let formattedData = [];

        rows.forEach((row, index) => {
          let valSection = safeGet(row, idxSection1);
          if (!valSection || valSection === "-" || valSection === "") {
            valSection = safeGet(row, idxSection2);
          }

          formattedData.push([
            index + 1,
            safeGet(row, idxTime),
            safeGet(row, idxEmail),
            safeGet(row, idxUnit),
            valSection,
            safeGet(row, idxVibrasi),
            safeGet(row, idxTempDE),
            safeGet(row, idxTempNDE),
            safeGet(row, idxSuhu),
            safeGet(row, idxBeban),
            safeGet(row, idxDamper),
            safeGet(row, idxCurrent),
            safeGet(row, idxBunyi),
            safeGet(row, idxPanel),
            safeGet(row, idxKelengkapan),
            safeGet(row, idxKebersihan),
            safeGet(row, idxGrounding),
            safeGet(row, idxRegreasing),
            safeGet(row, idxAction)
              ? safeGet(row, idxAction)
              : '<span class="badge badge-success">Tercatat</span>',
          ]);
        });

        dt.rows.add(formattedData).draw();
      })
      .catch((error) => {
        console.error("Error:", error);
        toastr.error("Gagal koneksi server.");
      });
  }

  function getColIndex(headers, keywords) {
    if (!Array.isArray(keywords)) keywords = [keywords];
    for (let i = 0; i < headers.length; i++) {
      const h = String(headers[i]).toUpperCase();
      for (let k of keywords) {
        if (h.includes(k.toUpperCase())) return i;
      }
    }
    return -1;
  }

  function safeGet(row, index) {
    return index < 0 || !row[index] ? "-" : row[index];
  }

  function bindFilterEvents() {
    const unitSelect = $("#pilihUnit");
    const motorSelect = $("#pilihMotor");

    function updateLabelTitle() {
      var unitSelect = $("#pilihUnit");
      var unitText = $("#pilihUnit option:selected").text();
      var motorText = $("#pilihMotor").val();
      var titleElement = $("#label-title");

      titleElement.removeClass("title-animate");

      var labelBaru = "";
      if (unitSelect.val() === "" || unitSelect.val() === null) {
        labelBaru = "DATABASE MONITORING MOTOR";
      } else {
        labelBaru =
          !motorText || motorText === ""
            ? unitText
            : unitText + " - " + motorText;
      }

      titleElement.text(labelBaru);

      void titleElement[0].offsetWidth;

      titleElement.addClass("title-animate");
    }

    $(document).ready(function () {
      $("#pilihUnit, #pilihMotor").on("change", function () {
        updateLabelTitle();
      });
    });

    function populateMotor(unit, selectedMotor = null) {
      motorSelect.empty();
      if (unit && dataMotor[unit]) {
        motorSelect.prop("disabled", false);
        motorSelect.append('<option value="">-- Pilih Motor --</option>');
        dataMotor[unit].forEach(function (motorName) {
          const isSelected = selectedMotor === motorName ? "selected" : "";
          motorSelect.append(
            `<option value="${motorName}" ${isSelected}>${motorName}</option>`,
          );
        });
      } else {
        motorSelect.prop("disabled", true);
        motorSelect.append('<option value="">-- Pilih Motor --</option>');
      }
    }

    unitSelect.change(function () {
      const val = $(this).val();
      populateMotor(val);
      localStorage.setItem("mon_selectedUnit", val);
      localStorage.removeItem("mon_selectedMotor");
      $("#example1").DataTable().clear().draw();
      updateLabelTitle();
    });

    motorSelect.change(function () {
      const unit = unitSelect.val();
      const motorName = $(this).val();
      localStorage.setItem("mon_selectedMotor", motorName);
      if (unit && motorName) loadDataFromSheet(unit, motorName);
      updateLabelTitle();
    });

    // Gunakan delegasi event agar lebih aman meski elemen dipindah-pindah
    $(document).on("click", "#btnRefresh", function (e) {
      e.preventDefault();
      e.stopPropagation(); // Hentikan gangguan dari elemen induk

      const unitSelect = $("#pilihUnit");
      const motorSelect = $("#pilihMotor");
      const unit = unitSelect.val();
      const motor = motorSelect.val();

      if (unit && motor) {
        console.log("Refreshing data for:", unit, motor);
        loadDataFromSheet(unit, motor);
      } else {
        toastr.info(
          "Pilih Unit dan Motor terlebih dahulu untuk refresh data spesifik.",
        );
        // Opsi: window.location.reload(); jika ingin refresh total
      }
    });

    const savedUnit = localStorage.getItem("mon_selectedUnit");
    const savedMotor = localStorage.getItem("mon_selectedMotor");
    if (savedUnit) {
      unitSelect.val(savedUnit);
      populateMotor(savedUnit, savedMotor);
      if (savedMotor) loadDataFromSheet(savedUnit, savedMotor);
      setTimeout(updateLabelTitle, 500);
    }
  }
});
