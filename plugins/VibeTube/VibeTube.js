/**
 * VibeTube.js - Industrial Vibration Monitor Plugin (Classic 5cm Tube, Individual Time & Empty Data Handler)
 */
class VibeTube {
  constructor(
    containerId,
    label,
    initialValue = 0,
    externalIds = { valId: null, timeId: null },
  ) {
    this.container = document.getElementById(containerId);
    this.label = label;
    this.currentValue = initialValue;
    this.maxLimit = 15;
    this.externalIds = externalIds;
    this.id = "vibe-" + Math.random().toString(36).substr(2, 9);
    this.currentTimestamp = "Data Kosong"; // Default awal jika belum ada data

    this._injectStyles();
    this._init();
  }

  _injectStyles() {
    if (document.getElementById("vibetube-styles")) return;
    const style = document.createElement("style");
    style.id = "vibetube-styles";

    // [DIPERBAIKI] Tabung diperpanjang menjadi 5cm
    style.innerHTML = `
            .vibetube-wrapper { width: 100%; padding: 0; user-select: none; display: flex; flex-direction: column; align-items: flex-start; }
            .vibetube-header { display: none; } 
            .vibetube-glass { 
                width: 5cm; /* PANJANG TABUNG DIUBAH MENJADI 5 CM DI SINI */
                height: 22px; background: #f2f2f2; border-radius: 11px;
                border: 1px solid #ccc; position: relative; overflow: hidden; 
                display: flex; align-items: center; justify-content: center;
                box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
                margin: 0;
            }
            .vibetube-liquid { 
                height: 100%; width: 0%; transition: width 0.6s cubic-bezier(0.1, 0.7, 1.0, 0.1), background-color 0.4s; 
                background-image: linear-gradient(to bottom, rgba(255,255,255,0.3) 0%, transparent 50%, rgba(0,0,0,0.1) 100%); 
                position: absolute; left: 0; z-index: 1; 
            }
            .vibetube-val { font-family: 'Courier New', monospace; font-size: 12px; font-weight: 800; z-index: 5; position: relative; color: #222; text-shadow: 0px 0px 2px rgba(255,255,255,0.8); pointer-events: none; display: block; }
        `;
    document.head.appendChild(style);
  }

  _init() {
    const wrapper = document.createElement("div");
    wrapper.className = "vibetube-wrapper";
    wrapper.id = this.id;
    wrapper.innerHTML = `
            <div class="vibetube-header">
                <span class="vibetube-title">${this.label}</span>
                <span class="vibetube-time" id="${this.id}-time">Data Kosong</span>
            </div>
            <div class="vibetube-glass">
                <div class="vibetube-liquid" id="${this.id}-fill"></div>
                <span class="vibetube-val" id="${this.id}-val">0.00 mm/s</span>
            </div>
        `;
    this.container.appendChild(wrapper);
    this.update(this.currentValue);
  }

  update(newValue, customTimestamp = undefined) {
    // 1. Pastikan nilai angka valid (jika NaN jadikan 0)
    this.currentValue = isNaN(newValue) ? 0 : newValue;

    // 2. Logika Waktu Update & Deteksi "Data Kosong"
    if (customTimestamp !== undefined) {
      if (
        customTimestamp === "-" ||
        customTimestamp === "" ||
        customTimestamp === null
      ) {
        this.currentTimestamp = "Data Kosong";
      } else {
        this.currentTimestamp = customTimestamp;
      }
    }

    // 3. Update Visual Tabung dan Angka
    const fill = document.getElementById(`${this.id}-fill`);
    const text = document.getElementById(`${this.id}-val`);

    const valueString = this.currentValue.toFixed(2) + " mm/s";
    let pct =
      (Math.min(this.currentValue, this.maxLimit) / this.maxLimit) * 100;

    fill.style.width = pct + "%";
    text.innerText = valueString;

    // 4. Pewarnaan Tabung (Biru -> Hijau -> Merah)
    if (this.currentValue < 4.5) {
      fill.style.backgroundColor = "#3498db";
    } else if (this.currentValue < 9.0) {
      fill.style.backgroundColor = "#2ecc71";
    } else {
      fill.style.backgroundColor = "#e74c3c";
    }

    // 5. Update Waktu Masing-Masing Berdasarkan ID Kontainernya
    const containerId = this.container.id;
    const extTime = document.getElementById(`ext-time-${containerId}`);

    if (extTime) {
      extTime.innerText = this.currentTimestamp;

      // Berikan efek warna merah jika tulisan "Data Kosong" agar lebih jelas
      if (this.currentTimestamp === "Data Kosong") {
        extTime.style.color = "#e74c3c"; // Merah
        extTime.style.fontWeight = "bold";
      } else {
        extTime.style.color = "#95a5a6"; // Abu-abu normal
        extTime.style.fontWeight = "normal";
      }
    }
  }
}
