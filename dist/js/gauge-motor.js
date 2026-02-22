/**
 * File: gauge-motor.js
 * Deskripsi: Menangani visualisasi ECharts (Jarum) dan Sinkronisasi Tabel
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

  // Inisialisasi khusus 4 Gauge ECharts
  initMotorGauge("gauge-vibrasi", "Vibrasi", "mm/s", 0, 15);
  initMotorGauge("gauge-beban-gen", "Beban", "MW", 0, 100);
  initMotorGauge("gauge-damper", "Damper", "%", 0, 100);
  initMotorGauge("gauge-load-current", "Arus", "A", 0, 300);

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
              let timeVal = allData[i][1]
                ? allData[i][1]
                : "Waktu Tidak Diketahui";
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
        updateGaugeValue(
          "gauge-vibrasi",
          "time-vibrasi",
          getLastValidData(allData, 5),
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
        const emptyData = { value: 0, timestamp: "-" };
        updateGaugeValue("gauge-vibrasi", "time-vibrasi", emptyData);
        updateGaugeValue("gauge-beban-gen", "time-beban-gen", emptyData);
        updateGaugeValue("gauge-damper", "time-damper", emptyData);
        updateGaugeValue("gauge-load-current", "time-load-current", emptyData);
      }
    }
  }

  if (typeof $ !== "undefined") {
    $("#example1").on("draw.dt", syncGaugeFromTable);
    $("#pilihMotor").on("change", updateLabelMotorGauge);
  }
});
