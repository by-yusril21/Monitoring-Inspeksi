/**
 * VibeTube.js - Industrial Vibration Monitor Plugin (Clean UI Version)
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
    this.maxLimit = 15; // Set permanen ke 15 (batas standar vibrasi)
    this.externalIds = externalIds;
    this.id = "vibe-" + Math.random().toString(36).substr(2, 9);

    // Ingatan untuk tanggal
    this.currentTimestamp = "--/--/----";

    this._injectStyles();
    this._init();
  }

  _injectStyles() {
    if (document.getElementById("vibetube-styles")) return;
    const style = document.createElement("style");
    style.id = "vibetube-styles";

    // CSS sudah dibersihkan dari .vibetube-btn dan .vibetube-ruler
    style.innerHTML = `
            .vibetube-wrapper { width: 100%; padding: 5px 0; user-select: none; border-bottom: 1px dashed #f2f2f2; display: flex; flex-direction: column; align-items: center; }
            .vibetube-header { 
                display: flex; 
                justify-content: space-between; 
                width: 3.2cm; /* Lebar sama persis dengan kaca tabung */
                margin: 0 auto 5px auto; /* Berada presisi di tengah */
                align-items: center; 
            }
            .vibetube-title { 
                font-size: 9px; /* Ukuran disesuaikan agar muat sebaris */
                font-weight: bold; 
                color: #555; 
                text-transform: uppercase; 
            }
            .vibetube-time { 
                font-size: 8.5px; /* Ukuran disesuaikan agar muat sebaris */
                color: #999; 
                font-family: monospace; 
            }
            .vibetube-glass { 
                width: 3.2cm; height: 22px; background: #f2f2f2; border-radius: 11px;
                border: 1px solid #ccc; position: relative; overflow: hidden; 
                display: flex; align-items: center; justify-content: center;
                box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
                margin: 0 auto;
            }
            .vibetube-liquid { 
                height: 100%; width: 0%; transition: width 0.6s cubic-bezier(0.1, 0.7, 1.0, 0.1), background-color 0.4s; 
                background-image: linear-gradient(to bottom, rgba(255,255,255,0.3) 0%, transparent 50%, rgba(0,0,0,0.1) 100%); 
                position: absolute; left: 0; z-index: 1; 
            }
            .vibetube-val { font-family: 'Courier New', monospace; font-size: 12px; font-weight: 800; z-index: 5; position: relative; color: #222; text-shadow: 0px 0px 2px rgba(255,255,255,0.8); pointer-events: none; }
        `;
    document.head.appendChild(style);
  }

  _init() {
    const wrapper = document.createElement("div");
    wrapper.className = "vibetube-wrapper";
    wrapper.id = this.id;

    // HTML dibersihkan: Tidak ada tag <button> dan <div class="vibetube-ruler">
    wrapper.innerHTML = `
            <div class="vibetube-header">
                <span class="vibetube-title">${this.label}</span>
                <span class="vibetube-time" id="${this.id}-time">--/--/----</span>
            </div>
            <div class="vibetube-glass">
                <div class="vibetube-liquid" id="${this.id}-fill"></div>
                <span class="vibetube-val" id="${this.id}-val">0.00 mm/s</span>
            </div>
        `;
    this.container.appendChild(wrapper);
    this.update(this.currentValue);
  }

  _getCurrentTimestamp() {
    const now = new Date();
    return `${String(now.getDate()).padStart(2, "0")}/${String(now.getMonth() + 1).padStart(2, "0")}/${now.getFullYear()}`;
  }

  update(newValue, customTimestamp = undefined) {
    this.currentValue = newValue;

    // Logika penyimpanan tanggal
    if (customTimestamp !== undefined) {
      if (customTimestamp && customTimestamp !== "-") {
        this.currentTimestamp = customTimestamp;
      } else {
        this.currentTimestamp = "-";
      }
    } else if (this.currentTimestamp === "--/--/----") {
      this.currentTimestamp = this._getCurrentTimestamp();
    }

    const fill = document.getElementById(`${this.id}-fill`);
    const text = document.getElementById(`${this.id}-val`);
    const timeDisplay = document.getElementById(`${this.id}-time`);

    const valueString = this.currentValue.toFixed(2) + " mm/s";

    let pct =
      (Math.min(this.currentValue, this.maxLimit) / this.maxLimit) * 100;
    fill.style.width = pct + "%";
    text.innerText = valueString;

    timeDisplay.innerText = this.currentTimestamp;

    if (this.externalIds.valId) {
      const el = document.getElementById(this.externalIds.valId);
      if (el) el.innerText = valueString;
    }
    if (this.externalIds.timeId) {
      const el = document.getElementById(this.externalIds.timeId);
      if (el) el.innerText = this.currentTimestamp;
    }

    // Pewarnaan Cairan
    if (this.currentValue < 4.5) fill.style.backgroundColor = "#3498db";
    else if (this.currentValue < 9.0) fill.style.backgroundColor = "#2ecc71";
    else fill.style.backgroundColor = "#e74c3c";
  }
}
