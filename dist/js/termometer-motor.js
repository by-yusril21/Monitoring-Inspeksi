function drawThermometer(containerId, currentTemp) {
  d3.select("#" + containerId)
    .selectAll("*")
    .remove();

  // Ukuran kanvas SVG sangat diperkecil
  var width = 80, // Diperkecil dari 110
    height = 150, // Diperkecil dari 220
    maxTemp = 120,
    minTemp = 0;

  var bottomY = height - 5,
    topY = 5,
    bulbRadius = 14, // Bola diperkecil dari 18
    tubeWidth = 14, // Tabung diperkecil dari 18
    tubeBorderWidth = 1,
    innerBulbColor = "rgb(230, 200, 200)",
    tubeBorderColor = "#999999";

  var mercuryColor, displayColor;

  if (currentTemp < 40) {
    mercuryColor = "rgb(52, 152, 219)";
    displayColor = "#2980b9";
  } else if (currentTemp >= 40 && currentTemp <= 85) {
    mercuryColor = "rgb(46, 204, 113)";
    displayColor = "#27ae60";
  } else {
    mercuryColor = "rgb(231, 76, 60)";
    displayColor = "#c0392b";
  }

  var valElement = document.getElementById(
    containerId.replace("thermo", "val"),
  );

  valElement.style.color = displayColor;
  valElement.innerText = currentTemp.toFixed(1) + " °C";

  var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width / 2,
    top_cy = topY + tubeWidth / 2;

  var svg = d3
    .select("#" + containerId)
    .append("svg")
    .attr("width", width)
    .attr("height", height);

  var defs = svg.append("defs");

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

  var step = 10;
  var domain = [0, 120];

  var scale = d3.scale
    .linear()
    .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
    .domain(domain);

  // --- GARIS BATAS KONDISI & TEKS LABEL (Ukurannya Disesuaikan) ---

  // Batas 40°C
  svg
    .append("line")
    .attr("x1", width / 2 - tubeWidth / 2 - 4)
    .attr("x2", width / 2 + tubeWidth / 2 + 4)
    .attr("y1", scale(40))
    .attr("y2", scale(40))
    .style("stroke", "#2980b9")
    .style("stroke-width", "1px")
    .style("stroke-dasharray", "3,2");

  svg
    .append("text")
    .attr("x", width / 2 + tubeWidth / 2 + 6)
    .attr("y", scale(40) + 2)
    .text("Medium")
    .style("fill", "#2980b9")
    .style("font-size", "7px") // Teks diperkecil
    .style("font-weight", "bold");

  // Batas 85°C
  svg
    .append("line")
    .attr("x1", width / 2 - tubeWidth / 2 - 4)
    .attr("x2", width / 2 + tubeWidth / 2 + 4)
    .attr("y1", scale(85))
    .attr("y2", scale(85))
    .style("stroke", "#c0392b")
    .style("stroke-width", "1px")
    .style("stroke-dasharray", "3,2");

  svg
    .append("text")
    .attr("x", width / 2 + tubeWidth / 2 + 6)
    .attr("y", scale(85) + 2)
    .text("High")
    .style("fill", "#c0392b")
    .style("font-size", "7px") // Teks diperkecil
    .style("font-weight", "bold");

  // --- CAIRAN TERMOMETER ---
  var fillTopPosition = scale(currentTemp);
  if (fillTopPosition > bulb_cy) fillTopPosition = bulb_cy;
  if (fillTopPosition < top_cy) fillTopPosition = top_cy;

  var tubeFill_bottom = bulb_cy,
    tubeFill_top = fillTopPosition;

  svg
    .append("rect")
    .attr("x", width / 2 - (tubeWidth - 6) / 2) // Dikurangi margin internal tabung
    .attr("y", tubeFill_top)
    .attr("width", tubeWidth - 6)
    .attr("height", tubeFill_bottom - tubeFill_top)
    .style("shape-rendering", "crispEdges")
    .style("fill", mercuryColor);

  svg
    .append("circle")
    .attr("r", bulbRadius - 3) // Lingkaran cairan disesuaikan proporsinya
    .attr("cx", bulb_cx)
    .attr("cy", bulb_cy)
    .style("fill", "url(#" + gradientId + ")")
    .style("stroke", mercuryColor)
    .style("stroke-width", "1.5px");

  // --- SKALA ANGKA DI SEBELAH KIRI ---
  var tickValues = d3
    .range((domain[1] - domain[0]) / step + 1)
    .map(function (v) {
      return domain[0] + v * step;
    });

  var axis = d3.svg
    .axis()
    .scale(scale)
    .innerTickSize(4) // Garis sumbu diperpendek
    .outerTickSize(0)
    .tickValues(tickValues)
    .orient("left");

  var svgAxis = svg
    .append("g")
    .attr("id", "tempScale-" + containerId)
    .attr("transform", "translate(" + (width / 2 - tubeWidth / 2) + ",0)")
    .call(axis);

  svgAxis
    .selectAll(".tick text")
    .style("fill", "#777777")
    .style("font-size", "7px") // Angka skala diperkecil
    .attr("x", -6); // Dirapatkan ke garis

  svgAxis.select("path").style("stroke", "none").style("fill", "none");

  svgAxis
    .selectAll(".tick line")
    .style("stroke", tubeBorderColor)
    .style("shape-rendering", "crispEdges")
    .style("stroke-width", "1px")
    .attr("x2", -4);
}

// -- SCRIPT SIMULASI DATA TETAP SAMA --
function updateDummyData() {
  let tempDE = 25 + Math.random() * 80;
  let tempNDE = 25 + Math.random() * 80;
  let tempWinding = 25 + Math.random() * 80;

  drawThermometer("thermo-de", tempDE);
  drawThermometer("thermo-nde", tempNDE);
  drawThermometer("thermo-winding", tempWinding);
}

updateDummyData();
setInterval(updateDummyData, 3000);
