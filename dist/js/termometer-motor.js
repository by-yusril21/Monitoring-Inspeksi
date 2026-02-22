/**
 * File: termometer-motor.js
 * Deskripsi: Menangani visualisasi D3.js (Termometer) dan Sinkronisasi Tabel
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

    var width = 80,
      height = 150,
      minTemp = 0,
      maxTemp = 120;
    var bottomY = height - 5,
      topY = 5,
      bulbRadius = 14,
      tubeWidth = 14,
      tubeBorderWidth = 1;
    var innerBulbColor = "rgb(230, 200, 200)",
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
    if (valElement) {
      valElement.style.color = displayColor;
      valElement.innerText = currentTemp.toFixed(1) + " °C";
    }

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

    var step = 10,
      domain = [0, 120];
    var scale = d3.scale
      .linear()
      .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
      .domain(domain);

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
      .style("font-size", "7px")
      .style("font-weight", "bold");

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
      .style("font-size", "7px")
      .style("font-weight", "bold");

    var fillTopPosition = scale(currentTemp);
    if (fillTopPosition > bulb_cy) fillTopPosition = bulb_cy;
    if (fillTopPosition < top_cy) fillTopPosition = top_cy;
    var tubeFill_bottom = bulb_cy,
      tubeFill_top = fillTopPosition;

    svg
      .append("rect")
      .attr("x", width / 2 - (tubeWidth - 6) / 2)
      .attr("y", tubeFill_top)
      .attr("width", tubeWidth - 6)
      .attr("height", tubeFill_bottom - tubeFill_top)
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

    var tickValues = d3
      .range((domain[1] - domain[0]) / step + 1)
      .map(function (v) {
        return domain[0] + v * step;
      });
    var axis = d3.svg
      .axis()
      .scale(scale)
      .innerTickSize(4)
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
      .style("font-size", "7px")
      .attr("x", -6);
    svgAxis.select("path").style("stroke", "none").style("fill", "none");
    svgAxis
      .selectAll(".tick line")
      .style("stroke", tubeBorderColor)
      .style("shape-rendering", "crispEdges")
      .style("stroke-width", "1px")
      .attr("x2", -4);
  }

  function updateThermometerValue(domId, timeDomId, dataObj) {
    let numValue = isNaN(dataObj.value) ? 0 : dataObj.value;
    drawThermometer(domId, numValue);

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

  function syncThermometerFromTable() {
    if (typeof $ !== "undefined" && $.fn.DataTable.isDataTable("#example1")) {
      const table = $("#example1").DataTable();

      if (table.data().any()) {
        const allData = table.rows().data();
        updateThermometerValue(
          "thermo-de",
          "time-temp-de",
          getLastValidData(allData, 6),
        );
        updateThermometerValue(
          "thermo-nde",
          "time-temp-nde",
          getLastValidData(allData, 7),
        );
        updateThermometerValue(
          "thermo-winding",
          "time-suhu-ruang",
          getLastValidData(allData, 8),
        );
      } else {
        const emptyData = { value: 0, timestamp: "-" };
        updateThermometerValue("thermo-de", "time-temp-de", emptyData);
        updateThermometerValue("thermo-nde", "time-temp-nde", emptyData);
        updateThermometerValue("thermo-winding", "time-suhu-ruang", emptyData);
      }
    }
  }

  if (typeof $ !== "undefined") {
    $("#example1").on("draw.dt", syncThermometerFromTable);
  }
});
