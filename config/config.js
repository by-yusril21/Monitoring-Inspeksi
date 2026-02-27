/* =======================================================
   config.js
   Konfigurasi global — URL API, Token, dan Data Motor
   Dimuat PERTAMA sebelum tabel.js dan navbar.js
   ======================================================= */

window.SCRIPT_URLS = {
  C6KV: "https://script.google.com/macros/s/AKfycbwc72bVBL0w12SYeD_oYeyA9GFI2e4nA2PgiIcald9gb7KyuekfzkOD_EhvystikAc/exec",
  C380: "https://script.google.com/macros/s/AKfycbw3Jw1GMtoIHeePHQv_hy6oeY7TkIPjdI4n9VI2m6T91WeztL5WDpA8VBbbQCr_OKVO/exec",
  D6KV: "",
  D380: "",
  UTILITY: "",
};

window.API_TOKEN = "SemenTonasa2026";

window.dataMotor = {
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
