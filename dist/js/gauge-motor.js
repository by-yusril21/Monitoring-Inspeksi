/**
 * File: gauge-motor.js
 * FITUR BARU: Auto-Fallback + Sinkronisasi Timestamp per Parameter
 */

document.addEventListener("DOMContentLoaded", function () {
  if (typeof echarts === "undefined") {
    console.error("ECharts tidak ditemukan!");
    return;
  }

  const standardColorStops = [
    [0.6, "#28a745"], // Normal (Hijau)
    [0.8, "#ffc107"], // Warning (Kuning)
    [1.0, "#dc3545"], // Danger (Merah)
  ];

  const gaugeInstances = {};

  // 1. Fungsi Inisialisasi ECharts
  function initMotorGauge(domId, title, unit, min, max) {
    const chartDom = document.getElementById(domId);
    if (!chartDom) return;

    const myChart = echarts.init(chartDom, null, { renderer: "svg" });

    const width = window.innerWidth;
    let radius = width < 768 ? "75%" : "80%";
    let detailFontSize = width < 768 ? 14 : 16;

    const option = {
      backgroundColor: "transparent",
      series: [
        {
          type: "gauge",
          startAngle: 210,
          endAngle: -30,
          min: min,
          max: max,
          radius: radius,
          center: ["50%", "55%"],
          axisLine: {
            lineStyle: { width: 10, color: standardColorStops },
          },
          pointer: {
            length: "60%",
            width: 4,
            itemStyle: { color: "#495057" },
          },
          axisTick: {
            distance: -10,
            length: 5,
            lineStyle: { color: "#adb5bd", width: 1 },
          },
          splitLine: {
            distance: -10,
            length: 8,
            lineStyle: { color: "#adb5bd", width: 2 },
          },
          axisLabel: { distance: -20, color: "#6c757d", fontSize: 9 },
          detail: {
            valueAnimation: true,
            formatter: "{value} " + unit,
            color: "#343a40",
            fontSize: detailFontSize,
            fontWeight: "bold",
            offsetCenter: [0, "65%"],
          },
          title: { show: false },
          data: [{ value: 0 }],
        },
      ],
    };

    myChart.setOption(option);
    gaugeInstances[domId] = myChart;
  }

  initMotorGauge("gauge-vibrasi", "Vibrasi", "mm/s", 0, 20);
  initMotorGauge("gauge-temp-de", "Temp DE", "°C", 0, 100);
  initMotorGauge("gauge-temp-nde", "Temp NDE", "°C", 0, 100);
  initMotorGauge("gauge-suhu-ruang", "Suhu Ruang", "°C", 0, 60);
  initMotorGauge("gauge-beban-gen", "Beban", "MW", 0, 100);
  initMotorGauge("gauge-damper", "Damper", "%", 0, 100);
  initMotorGauge("gauge-load-current", "Arus", "A", 0, 500);

  window.addEventListener("resize", function () {
    Object.values(gaugeInstances).forEach((chart) => {
      chart.resize();
    });
  });

  // =========================================================================
  // FUNGSI UTAMA
  // =========================================================================

  function updateLabelMotorGauge() {
    const labelElem = document.getElementById("label-motor-gauge");
    const motorText = document.getElementById("pilihMotor")
      ? document.getElementById("pilihMotor").value
      : "";
    if (labelElem) {
      if (motorText && motorText !== "") {
        labelElem.innerHTML = motorText;
        labelElem.className = "text-primary";
      } else {
        labelElem.innerHTML = "Menunggu Pilihan Motor...";
        labelElem.className = "text-danger";
      }
    }
  }

  // FUNGSI UPDATE GAUGE & TEKS TIMESTAMP
  function updateGaugeValue(domId, timeDomId, dataObj) {
    if (!gaugeInstances[domId]) return;

    let numValue = dataObj.value;
    let timeString = dataObj.timestamp;

    if (isNaN(numValue)) numValue = 0;

    // 1. Update Jarum
    gaugeInstances[domId].setOption({
      series: [{ data: [{ value: numValue }] }],
    });

    // 2. Update Teks Waktu di HTML
    const timeElem = document.getElementById(timeDomId);
    if (timeElem) {
      if (timeString !== "-") {
        timeElem.innerHTML = `<i class="far fa-clock"></i> ${timeString}`;
        timeElem.classList.remove("text-danger"); // Kembalikan warna normal
      } else {
        timeElem.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Data Kosong`;
        timeElem.classList.add("text-danger"); // Warnai merah jika kosong
      }
    }
  }

  /**
   * MENCARI DATA + MENGAMBIL TIMESTAMP DI BARIS YANG SAMA
   */
  function getLastValidData(allData, colIndex) {
    for (let i = allData.length - 1; i >= 0; i--) {
      let val = allData[i][colIndex];

      if (val !== null && val !== undefined) {
        let strVal = String(val).trim();

        if (strVal !== "-" && strVal !== "--" && strVal !== "") {
          strVal = strVal.replace(",", ".");
          let match = strVal.match(/-?\d+(\.\d+)?/);

          if (match) {
            let parsed = parseFloat(match[0]);
            if (!isNaN(parsed)) {
              // KUNCI: Ambil waktu dari Kolom Index 1 di BARIS YANG SAMA (i)
              let timeVal = allData[i][1]
                ? allData[i][1]
                : "Waktu Tidak Diketahui";

              // Return sebagai Objek (Nilai dan Waktu)
              return { value: parsed, timestamp: timeVal };
            }
          }
        }
      }
    }
    // Jika tidak ketemu sama sekali sampai atas
    return { value: 0, timestamp: "-" };
  }

  function syncGaugeFromTable() {
    if (typeof $ !== "undefined" && $.fn.DataTable.isDataTable("#example1")) {
      const table = $("#example1").DataTable();

      updateLabelMotorGauge();

      if (table.data().any()) {
        const allData = table.rows().data();

        // Masukkan (ID Chart, ID Teks Waktu, Data Objek Hasil Pencarian)
        updateGaugeValue(
          "gauge-vibrasi",
          "time-vibrasi",
          getLastValidData(allData, 5),
        );
        updateGaugeValue(
          "gauge-temp-de",
          "time-temp-de",
          getLastValidData(allData, 6),
        );
        updateGaugeValue(
          "gauge-temp-nde",
          "time-temp-nde",
          getLastValidData(allData, 7),
        );
        updateGaugeValue(
          "gauge-suhu-ruang",
          "time-suhu-ruang",
          getLastValidData(allData, 8),
        );
        updateGaugeValue(
          "gauge-beban-gen",
          "time-beban-gen",
          getLastValidData(allData, 9),
        );
        updateGaugeValue(
          "gauge-damper",
          "time-damper",
          getLastValidData(allData, 10),
        );
        updateGaugeValue(
          "gauge-load-current",
          "time-load-current",
          getLastValidData(allData, 11),
        );
      } else {
        // Jika tabel kosong
        const emptyData = { value: 0, timestamp: "-" };
        updateGaugeValue("gauge-vibrasi", "time-vibrasi", emptyData);
        updateGaugeValue("gauge-temp-de", "time-temp-de", emptyData);
        updateGaugeValue("gauge-temp-nde", "time-temp-nde", emptyData);
        updateGaugeValue("gauge-suhu-ruang", "time-suhu-ruang", emptyData);
        updateGaugeValue("gauge-beban-gen", "time-beban-gen", emptyData);
        updateGaugeValue("gauge-damper", "time-damper", emptyData);
        updateGaugeValue("gauge-load-current", "time-load-current", emptyData);
      }
    }
  }

  // MATA-MATA
  if (typeof $ !== "undefined") {
    $("#example1").on("draw.dt", function () {
      syncGaugeFromTable();
    });

    $("#pilihMotor").on("change", function () {
      updateLabelMotorGauge();
    });
  }
});
