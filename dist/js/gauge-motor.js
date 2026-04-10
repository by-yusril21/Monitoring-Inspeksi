/**
 * File: gauge-motor.js
 * Deskripsi: Menangani visualisasi ECharts (Jarum 5 Parameter), VibeTube 8 Tabung, dan Sinkronisasi Tabel 28 Kolom
 */

document.addEventListener("DOMContentLoaded", function () {
  if (typeof echarts === "undefined") {
    console.error("ECharts tidak ditemukan!");
    return;
  }

  const standardColorStops = [
    [0.6, "#28a745"],
    [0.8, "#ffc107"],
    [1.0, "#dc3545"],
  ];

  const gaugeInstances = {};

  // --- INISIALISASI 8 VIBETUBE ---
  const vibeDE_H = new VibeTube("vibe-de-h-container", "H");
  const vibeDE_V = new VibeTube("vibe-de-v-container", "V");
  const vibeDE_Ax = new VibeTube("vibe-de-ax-container", "Ax");
  const vibeDE_gE = new VibeTube("vibe-de-ge-container", "gE");

  const vibeNDE_H = new VibeTube("vibe-nde-h-container", "H");
  const vibeNDE_V = new VibeTube("vibe-nde-v-container", "V");
  const vibeNDE_Ax = new VibeTube("vibe-nde-ax-container", "Ax");
  const vibeNDE_gE = new VibeTube("vibe-nde-ge-container", "gE");

  // --- FUNGSI INISIALISASI ECHARTS ---
  function initMotorGauge(domId, title, unit, min, max) {
    const chartDom = document.getElementById(domId);
    if (!chartDom) return;

    const myChart = echarts.init(chartDom, null, { renderer: "svg" });
    const width = window.innerWidth;
    let radius = width < 768 ? "65%" : "75%";
    let detailFontSize = width < 768 ? 10 : 12;

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
          axisLine: { lineStyle: { width: 10, color: standardColorStops } },
          pointer: {
            length: "80%",
            width: 3.5,
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
          axisLabel: { distance: -17, color: "#6c757d", fontSize: 8 },
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

  // --- [UPDATE] Inisialisasi 5 Gauge Utama ---
  initMotorGauge("gauge-beban-gen", "Beban", "MW", 0, 100);
  initMotorGauge("gauge-damper", "Damper", "%", 0, 100);
  initMotorGauge("gauge-load-current-r", "Arus", "A", 0, 300);
  initMotorGauge("gauge-load-current-s", "Arus", "A", 0, 300);
  initMotorGauge("gauge-load-current-t", "Arus", "A", 0, 300);

  window.addEventListener("resize", function () {
    Object.values(gaugeInstances).forEach((chart) => chart.resize());
  });

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

  function updateGaugeValue(domId, timeDomId, dataObj) {
    if (!gaugeInstances[domId]) return;
    let numValue = isNaN(dataObj.value) ? 0 : dataObj.value;
    gaugeInstances[domId].setOption({
      series: [{ data: [{ value: numValue }] }],
    });

    const timeElem = document.getElementById(timeDomId);
    if (timeElem) {
      if (dataObj.timestamp !== "-") {
        timeElem.innerHTML = `<i class="far fa-clock"></i> ${dataObj.timestamp}`;
        timeElem.classList.remove("text-danger");
      } else {
        timeElem.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Data Kosong`;
        timeElem.classList.add("text-danger");
      }
    }
  }

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
              let rawTime = allData[i][1]
                ? String(allData[i][1])
                : "Waktu Tidak Diketahui";
              let timeVal = rawTime.includes(" ")
                ? rawTime.split(" ")[0]
                : rawTime;

              return { value: parsed, timestamp: timeVal };
            }
          }
        }
      }
    }
    return { value: 0, timestamp: "-" };
  }

  function syncGaugeFromTable() {
    if (typeof $ !== "undefined" && $.fn.DataTable.isDataTable("#example1")) {
      const table = $("#example1").DataTable();
      updateLabelMotorGauge();

      if (table.data().any()) {
        const allData = table.rows().data();

        // 1. UPDATE VIBETUBE (Kolom 5 s/d 12)
        const data_DE_H = getLastValidData(allData, 5);
        const data_DE_V = getLastValidData(allData, 6);
        const data_DE_Ax = getLastValidData(allData, 7);
        const data_DE_gE = getLastValidData(allData, 8);

        const data_NDE_H = getLastValidData(allData, 9);
        const data_NDE_V = getLastValidData(allData, 10);
        const data_NDE_Ax = getLastValidData(allData, 11);
        const data_NDE_gE = getLastValidData(allData, 12);

        vibeDE_H.update(data_DE_H.value, data_DE_H.timestamp);
        vibeDE_V.update(data_DE_V.value, "-");
        vibeDE_Ax.update(data_DE_Ax.value, "-");
        vibeDE_gE.update(data_DE_gE.value, "-");

        vibeNDE_H.update(data_NDE_H.value, data_NDE_H.timestamp);
        vibeNDE_V.update(data_NDE_V.value, "-");
        vibeNDE_Ax.update(data_NDE_Ax.value, "-");
        vibeNDE_gE.update(data_NDE_gE.value, "-");

        // --- [UPDATE] MENGISI 5 GAUGE ECHARTS ---

        // Beban Generator ada di Kolom 19
        updateGaugeValue(
          "gauge-beban-gen",
          "time-beban-gen",
          getLastValidData(allData, 19),
        );

        // Opening Damper ada di Kolom 20
        updateGaugeValue(
          "gauge-damper",
          "time-damper",
          getLastValidData(allData, 20),
        );

        // Arus (R, S, T) ada di Kolom 16, 17, 18
        updateGaugeValue(
          "gauge-load-current-r",
          "time-load-current-r",
          getLastValidData(allData, 16),
        );
        updateGaugeValue(
          "gauge-load-current-s",
          "time-load-current-s",
          getLastValidData(allData, 17),
        );
        updateGaugeValue(
          "gauge-load-current-t",
          "time-load-current-t",
          getLastValidData(allData, 18),
        );
      } else {
        const emptyData = { value: 0, timestamp: "-" };

        vibeDE_H.update(0, "-");
        vibeDE_V.update(0, "-");
        vibeDE_Ax.update(0, "-");
        vibeDE_gE.update(0, "-");
        vibeNDE_H.update(0, "-");
        vibeNDE_V.update(0, "-");
        vibeNDE_Ax.update(0, "-");
        vibeNDE_gE.update(0, "-");

        updateGaugeValue("gauge-beban-gen", "time-beban-gen", emptyData);
        updateGaugeValue("gauge-damper", "time-damper", emptyData);
        updateGaugeValue(
          "gauge-load-current-r",
          "time-load-current-r",
          emptyData,
        );
        updateGaugeValue(
          "gauge-load-current-s",
          "time-load-current-s",
          emptyData,
        );
        updateGaugeValue(
          "gauge-load-current-t",
          "time-load-current-t",
          emptyData,
        );
      }
    }
  }

  if (typeof $ !== "undefined") {
    $("#example1").on("draw.dt", syncGaugeFromTable);
    $("#pilihMotor").on("change", updateLabelMotorGauge);
  }
});
