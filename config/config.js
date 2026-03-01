/* =======================================================
   config.js
   Konfigurasi global — URL API, Token, dan Data Motor
   Dimuat PERTAMA sebelum tabel.js dan navbar.js
   ======================================================= */

window.SCRIPT_URLS = {
  C6KV: "https://script.google.com/macros/s/AKfycbzkvL27aT9n2tFcgSZlXlDN5yMaPHxUHpWIHCCg0kfNPWG0UXbXaJTaDhMsfjaUOUpJ/exec",
  C380: "https://script.google.com/macros/s/AKfycbzS2uoWxzS3k6sCUBFl-nzgmnaOzXoSf_jIEcjJ_0EZDkult0ECnijJTSiT0eUm54xl/exec",
  D6KV: "https://script.google.com/macros/s/AKfycbzcaUlMVwdvefqH68AsoDeKvtQAe0uJ7TItNqv7LBA3iPBhk1b-QjKa5aH9fYi-b1glGg/exec",
  D380: "https://script.google.com/macros/s/AKfycbyO7pg3l3fCyn-biovFENyK8O-DxIUAoUt6fco1_klLfZT0BTSUOYaph4l-30ykGXtg/exec",
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
  D6KV: [
    "SEA WATER INTAKE PUMP B",
    "VENT GAS FAN D",
    "INDUCED DRAFT FAN D",
    "PULVERIZED FAN D",
    "FORCED DRAFT FAN D",
    "COAL MILL D",
    "BOILER FEED WATER PUMP B",
    "BOILER FEED WATER PUMP A",
  ],

  D380: [
    "GAS AIR HEATER D",
    "BLOWER PFISTER D",
    "IGNITER AIR FAN D",
    "MILL SEAL AIR FAN D",
    "PULVERIZED COAL FAN D",
    "EJECTOR PUMP B",
    "EJECTOR PUMP A",
    "CONDENSATE PUMP B",
    "CONDENSATE PUMP A",
  ],

  UTILITY: [
    "COMPRESSOR HOUSE",
    "CHLORINATION PLANT",
    "WATER TREATMENT PLANT",
    "WASTE WATER TREATMENT PLANT",
    "AUXILIARY BOILER",
    "EMERGENCY DIESEL GENERATOR",
  ],
};
