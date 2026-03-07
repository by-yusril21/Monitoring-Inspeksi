/**
 * File: termometer-motor.js
 * Deskripsi: Visualisasi D3.js Termometer Modern + Card Status Dinamis + Zona Memudar
 */

document.addEventListener("DOMContentLoaded", function () {
  if (typeof d3 === "undefined") {
    console.error("D3.js tidak ditemukan!");
    return;
  }

  function drawThermometer(containerId, currentTemp) {
    d3.select("#" + containerId)
      .selectAll("*")
      .remove();

    var width = 95,
      height = 150;
    var bottomY = height - 5,
      topY = 5,
      bulbRadius = 14,
      tubeWidth = 14,
      tubeBorderWidth = 1;
    var innerBulbColor = "rgb(230, 200, 200)",
      tubeBorderColor = "#999999";
    var mercuryColor;

    if (currentTemp < 40) mercuryColor = "rgb(72, 235, 39)";
    else if (currentTemp >= 40 && currentTemp < 80)
      mercuryColor = "rgb(240, 169, 15)";
    else mercuryColor = "rgb(231, 76, 60)";

    var bulb_cy = bottomY - bulbRadius,
      bulb_cx = width / 2,
      top_cy = topY + tubeWidth / 2;

    var svg = d3
      .select("#" + containerId)
      .append("svg")
      .attr("width", width)
      .attr("height", height);

    var defs = svg.append("defs");

    // --- GRADIEN UNTUK BOHLAM TERMOMETER ---
    var gradientId = "bulbGradient-" + containerId;
    var bulbGradient = defs
      .append("radialGradient")
      .attr("id", gradientId)
      .attr("cx", "50%")
      .attr("cy", "50%")
      .attr("r", "50%")
      .attr("fx", "50%")
      .attr("fy", "50%");
    bulbGradient
      .append("stop")
      .attr("offset", "0%")
      .style("stop-color", innerBulbColor);
    bulbGradient
      .append("stop")
      .attr("offset", "90%")
      .style("stop-color", mercuryColor);

    // --- [BARU] GRADIEN UNTUK ZONA (MEMUDAR KE KANAN) ---
    // Gradien High (Merah)
    var highGradId = "highGrad-" + containerId;
    var highGrad = defs
      .append("linearGradient")
      .attr("id", highGradId)
      .attr("x1", "0%")
      .attr("y1", "0%")
      .attr("x2", "100%")
      .attr("y2", "0%");
    highGrad
      .append("stop")
      .attr("offset", "0%")
      .style("stop-color", "rgba(239, 83, 83, 0.29)");
    highGrad
      .append("stop")
      .attr("offset", "100%")
      .style("stop-color", "rgba(231, 76, 60, 0)");

    // Gradien Medium (Orange)
    var medGradId = "medGrad-" + containerId;
    var medGrad = defs
      .append("linearGradient")
      .attr("id", medGradId)
      .attr("x1", "0%")
      .attr("y1", "0%")
      .attr("x2", "100%")
      .attr("y2", "0%");
    medGrad
      .append("stop")
      .attr("offset", "0%")
      .style("stop-color", "rgba(173, 171, 45, 0.37)");
    medGrad
      .append("stop")
      .attr("offset", "100%")
      .style("stop-color", "rgba(243, 156, 18, 0)");

    // Gradien Low (Biru)
    var lowGradId = "lowGrad-" + containerId;
    var lowGrad = defs
      .append("linearGradient")
      .attr("id", lowGradId)
      .attr("x1", "0%")
      .attr("y1", "0%")
      .attr("x2", "100%")
      .attr("y2", "0%");
    lowGrad
      .append("stop")
      .attr("offset", "0%")
      .style("stop-color", "rgba(62, 201, 62, 0.34)");
    lowGrad
      .append("stop")
      .attr("offset", "100%")
      .style("stop-color", "rgba(52, 152, 219, 0)");

    // Kaca Tabung
    svg
      .append("circle")
      .attr("r", tubeWidth / 2)
      .attr("cx", width / 2)
      .attr("cy", top_cy)
      .style("fill", "#FFFFFF")
      .style("stroke", tubeBorderColor)
      .style("stroke-width", tubeBorderWidth + "px");
    svg
      .append("rect")
      .attr("x", width / 2 - tubeWidth / 2)
      .attr("y", top_cy)
      .attr("height", bulb_cy - top_cy)
      .attr("width", tubeWidth)
      .style("shape-rendering", "crispEdges")
      .style("fill", "#FFFFFF")
      .style("stroke", tubeBorderColor)
      .style("stroke-width", tubeBorderWidth + "px");
    svg
      .append("circle")
      .attr("r", tubeWidth / 2 - tubeBorderWidth / 2)
      .attr("cx", width / 2)
      .attr("cy", top_cy)
      .style("fill", "#FFFFFF")
      .style("stroke", "none");
    svg
      .append("circle")
      .attr("r", bulbRadius)
      .attr("cx", bulb_cx)
      .attr("cy", bulb_cy)
      .style("fill", "#FFFFFF")
      .style("stroke", tubeBorderColor)
      .style("stroke-width", tubeBorderWidth + "px");
    svg
      .append("rect")
      .attr("x", width / 2 - (tubeWidth - tubeBorderWidth) / 2)
      .attr("y", top_cy)
      .attr("height", bulb_cy - top_cy)
      .attr("width", tubeWidth - tubeBorderWidth)
      .style("shape-rendering", "crispEdges")
      .style("fill", "#FFFFFF")
      .style("stroke", "none");

    var step = 10,
      domain = [0, 120];
    var scale = d3.scale
      .linear()
      .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
      .domain(domain);

    // ==========================================
    // ZONA (LOW, MEDIUM, HIGH) DENGAN EFEK MEMUDAR
    // ==========================================
    // 1. ZONA HIGH (80 - 120)
    svg
      .append("rect")
      .attr("x", width / 2 + tubeWidth / 2 + 5)
      .attr("y", scale(120))
      .attr("width", 38)
      .attr("height", scale(80) - scale(120))
      .style("fill", "url(#" + highGradId + ")") // Panggil Gradien
      .style("rx", 3);
    svg
      .append("text")
      .attr("x", width / 2 + tubeWidth / 2 + 20) // Geser x sedikit ke 20
      .attr("y", scale(100))
      .text("High")
      .style("fill", "#c0392b")
      .style("font-size", "8.5px")
      .style("font-weight", "bold")
      .style("text-anchor", "middle")
      .style("alignment-baseline", "middle");

    // 2. ZONA MEDIUM (40 - 80)
    svg
      .append("rect")
      .attr("x", width / 2 + tubeWidth / 2 + 5)
      .attr("y", scale(80))
      .attr("width", 38)
      .attr("height", scale(40) - scale(80))
      .style("fill", "url(#" + medGradId + ")") // Panggil Gradien
      .style("rx", 3);
    svg
      .append("text")
      .attr("x", width / 2 + tubeWidth / 2 + 20) // Geser x sedikit ke 20
      .attr("y", scale(60))
      .text("Medium")
      .style("fill", "#d35400")
      .style("font-size", "7.5px")
      .style("font-weight", "bold")
      .style("text-anchor", "middle")
      .style("alignment-baseline", "middle");

    // 3. ZONA LOW (0 - 40)
    svg
      .append("rect")
      .attr("x", width / 2 + tubeWidth / 2 + 5)
      .attr("y", scale(40))
      .attr("width", 38)
      .attr("height", scale(0) - scale(40))
      .style("fill", "url(#" + lowGradId + ")") // Panggil Gradien
      .style("rx", 3);
    svg
      .append("text")
      .attr("x", width / 2 + tubeWidth / 2 + 20) // Geser x sedikit ke 20
      .attr("y", scale(20))
      .text("Low")
      .style("fill", "#32739f")
      .style("font-size", "8.5px")
      .style("font-weight", "bold")
      .style("text-anchor", "middle")
      .style("alignment-baseline", "middle");

    // GARIS BATAS ZONA
    svg
      .append("line")
      .attr("x1", width / 2 - tubeWidth / 2 - 3)
      .attr("x2", width / 2 + tubeWidth / 2 + 4)
      .attr("y1", scale(40))
      .attr("y2", scale(40))
      .style("stroke", "#f39c12")
      .style("stroke-width", "1px")
      .style("stroke-dasharray", "2,2");
    svg
      .append("line")
      .attr("x1", width / 2 - tubeWidth / 2 - 3)
      .attr("x2", width / 2 + tubeWidth / 2 + 4)
      .attr("y1", scale(80))
      .attr("y2", scale(80))
      .style("stroke", "#e74c3c")
      .style("stroke-width", "1px")
      .style("stroke-dasharray", "2,2");

    // CAIRAN MERKURI
    var fillTopPosition = scale(currentTemp);
    if (fillTopPosition > bulb_cy) fillTopPosition = bulb_cy;
    if (fillTopPosition < top_cy) fillTopPosition = top_cy;
    svg
      .append("rect")
      .attr("x", width / 2 - (tubeWidth - 6) / 2)
      .attr("y", fillTopPosition)
      .attr("width", tubeWidth - 6)
      .attr("height", bulb_cy - fillTopPosition)
      .style("shape-rendering", "crispEdges")
      .style("fill", mercuryColor);
    svg
      .append("circle")
      .attr("r", bulbRadius - 3)
      .attr("cx", bulb_cx)
      .attr("cy", bulb_cy)
      .style("fill", "url(#" + gradientId + ")")
      .style("stroke", mercuryColor)
      .style("stroke-width", "1.5px");

    // ANGKA PENGGARIS KIRI
    var axis = d3.svg
      .axis()
      .scale(scale)
      .innerTickSize(4)
      .outerTickSize(0)
      .tickValues(
        d3
          .range((domain[1] - domain[0]) / step + 1)
          .map((v) => domain[0] + v * step),
      )
      .orient("left");
    var svgAxis = svg
      .append("g")
      .attr("transform", "translate(" + (width / 2 - tubeWidth / 2) + ",0)")
      .call(axis);
    svgAxis
      .selectAll(".tick text")
      .style("fill", "#777777")
      .style("font-size", "7.5px")
      .attr("x", -5);
    svgAxis.select("path").style("stroke", "none").style("fill", "none");
    svgAxis
      .selectAll(".tick line")
      .style("stroke", tubeBorderColor)
      .style("shape-rendering", "crispEdges")
      .style("stroke-width", "1px")
      .attr("x2", -3);
  }

  // UPDATE SELURUH ELEMEN DI DALAM CARD (Nilai, Warna, Status Box)
  function updateThermometerUI(prefix, dataObj) {
    let temp = isNaN(dataObj.value) ? 0 : dataObj.value;

    // 1. Gambar Termometer D3.js
    drawThermometer("thermo-" + prefix, temp);

    // 2. Tentukan Warna dan Status
    let color, bgColor, statusText, icon;
    if (temp < 40) {
      color = "#27ae60"; // Hijau (Normal)
      bgColor = "#eafaf1";
      statusText = "Normal";
      icon = "fa-check-circle";
    } else if (temp >= 40 && temp < 80) {
      color = "#f39c12"; // Orange (Warning)
      bgColor = "#fef5e7";
      statusText = "Warning";
      icon = "fa-exclamation-triangle";
    } else {
      color = "#e74c3c"; // Merah (Critical)
      bgColor = "#fdedec";
      statusText = "Critical";
      icon = "fa-times-circle";
    }

    // 3. Terapkan ke HTML
    let valElem = document.getElementById("val-" + prefix);
    let boxElem = document.getElementById("status-box-" + prefix);
    let txtElem = document.getElementById("status-text-" + prefix);
    let timeElem =
      document.getElementById("time-temp-" + prefix) ||
      document.getElementById("time-suhu-ruang");

    if (valElem) {
      valElem.innerText = temp.toFixed(1) + " °C";
      valElem.style.color = color;
    }
    if (boxElem && txtElem) {
      boxElem.style.backgroundColor = bgColor;
      txtElem.innerHTML = `<i class="fas ${icon}"></i> ${statusText}`;
      txtElem.style.color = color;
    }
    if (timeElem) {
      timeElem.innerText =
        dataObj.timestamp !== "-" ? dataObj.timestamp : "Data Kosong";
    }
  }

  // POTONG STRING WAKTU MENJADI TANGGAL SAJA
  function getLastValidData(allData, colIndex) {
    for (let i = allData.length - 1; i >= 0; i--) {
      let val = allData[i][colIndex];
      if (
        val !== null &&
        val !== undefined &&
        String(val).trim() !== "-" &&
        String(val).trim() !== ""
      ) {
        let match = String(val)
          .replace(",", ".")
          .match(/-?\d+(\.\d+)?/);
        if (match && !isNaN(parseFloat(match[0]))) {
          let rawTime = allData[i][1] ? String(allData[i][1]) : "-";
          return {
            value: parseFloat(match[0]),
            timestamp: rawTime.includes(" ") ? rawTime.split(" ")[0] : rawTime,
          };
        }
      }
    }
    return { value: 0, timestamp: "-" };
  }

  // SINKRONISASI DARI DATATABLE
  function syncThermometerFromTable() {
    if (typeof $ !== "undefined" && $.fn.DataTable.isDataTable("#example1")) {
      const table = $("#example1").DataTable();
      if (table.data().any()) {
        const allData = table.rows().data();
        updateThermometerUI("de", getLastValidData(allData, 7));
        updateThermometerUI("nde", getLastValidData(allData, 8));
        updateThermometerUI("winding", getLastValidData(allData, 9));
      } else {
        const emptyData = { value: 0, timestamp: "-" };
        updateThermometerUI("de", emptyData);
        updateThermometerUI("nde", emptyData);
        updateThermometerUI("winding", emptyData);
      }
    }
  }

  if (typeof $ !== "undefined") {
    $("#example1").on("draw.dt", syncThermometerFromTable);
  }
});
