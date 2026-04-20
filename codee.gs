// ==========================================
// VERSI NGEBUT
// KONFIGURASI KEAMANAN & EMAIL
// ==========================================
var PASSWORD_RAHASIA = "SemenTonasa2026"; 

// ==========================================
// 1. FUNGSI MEMBACA DATA (GET)
// ==========================================

function doGet(e) {
  // --- VALIDASI TOKEN KODE ANDA ---
  if (!e.parameter.token || e.parameter.token !== PASSWORD_RAHASIA) {
    return ContentService.createTextOutput(JSON.stringify({
      "status": "error",
      "message": "AKSES DITOLAK: Password/Token Salah atau Tidak Ada!"
    })).setMimeType(ContentService.MimeType.JSON);
  }

  var doc = SpreadsheetApp.getActiveSpreadsheet();

  // ========================================================
  // ACTION 1: Mengambil Data MASTER_JADWAL untuk Website
  // ========================================================
  if (e.parameter.action === "get_master") {
    var sheetMaster = doc.getSheetByName("MASTER_JADWAL");
    if (!sheetMaster) {
      return responseJSON({"status": "error", "message": "Sheet MASTER_JADWAL tidak ditemukan"});
    }
    
    var dataMaster = sheetMaster.getDataRange().getDisplayValues();
    var resultMaster = [];
    for (var i = 1; i < dataMaster.length; i++) {
      if (dataMaster[i][1] !== "") { 
        resultMaster.push({
          "no": dataMaster[i][0],
          "namaMotor": dataMaster[i][1],
          "sectionNo": dataMaster[i][2],    // AMBIL DARI KOLOM C (INDEX 2)
          "tanggalAwal": dataMaster[i][3]   // TANGGAL GESER KE KOLOM D (INDEX 3)
        });
      }
    }
    return responseJSON({
      "status": "success", 
      "data": resultMaster
    });
  }

  // ========================================================
  // ACTION 2: Mengambil Data PENGATURAN_EMAIL (PASTE DI SINI)
  // ========================================================
  if (e.parameter.action === "get_emails") {
    // Memanggil fungsi otomatis buat sheet
    var sheetEmail = getOrCreateEmailSheet(); 
    
    var dataEmail = sheetEmail.getDataRange().getDisplayValues();
    var resultEmail = [];
    
    for (var i = 1; i < dataEmail.length; i++) {
      resultEmail.push({
        "no": dataEmail[i][0],
        "nama": dataEmail[i][1],
        "email": dataEmail[i][2],
        "status": dataEmail[i][3]
      });
    }
    return responseJSON({"status": "success", "data": resultEmail});
  }

  // ========================================================
  // KODE LAMA: Posisinya HARUS PALING BAWAH di dalam doGet
  // ========================================================
  var sheetName = e.parameter.sheet; 
  var sheet = sheetName ? doc.getSheetByName(sheetName) : doc.getSheets()[0];
  
  if (!sheet) {
    return ContentService.createTextOutput(JSON.stringify({"error": "Sheet tidak ditemukan"}));
  }

  var data = sheet.getDataRange().getDisplayValues(); 
  return ContentService.createTextOutput(JSON.stringify(data)).setMimeType(ContentService.MimeType.JSON);
}

// ==========================================
// 2. FUNGSI MENULIS DATA (POST)
// ==========================================
function doPost(e) {
  try {
    // Validasi tambahan: Pastikan payload tidak kosong
    if (!e.postData || !e.postData.contents) {
       return responseJSON({"status": "error", "message": "Payload JSON kosong!"});
    }

    var doc = SpreadsheetApp.getActiveSpreadsheet();
    var jsonString = e.postData.contents;
    var data = JSON.parse(jsonString);
    
    if (data.token !== PASSWORD_RAHASIA) {
       return responseJSON({"status": "error", "message": "Password/Token Salah!"});
    }

    // ========================================================
    // UPDATE MASTER JADWAL (REVISI KOLOM 4)
    // ========================================================
    if (data.action === "update_master") {
       var sheetMaster = doc.getSheetByName("MASTER_JADWAL");
       if (!sheetMaster) return responseJSON({"status": "error", "message": "Sheet MASTER_JADWAL tidak ditemukan"});
       
       var namaMotorDicari = data.namaMotor;
       var tanggalBaru = data.tanggalBaru; // Harus String format DD/MM/YYYY
       
       var masterData = sheetMaster.getDataRange().getValues();
       var barisDitemukan = -1;
       
       // Cari posisi baris motor tersebut
       for (var i = 1; i < masterData.length; i++) {
         if (String(masterData[i][1]).trim() === String(namaMotorDicari).trim()) {
           barisDitemukan = i + 1; // +1 karena index array mulai 0, baris sheet mulai 1
           break;
         }
       }
       
       if (barisDitemukan !== -1) {
         // REVISI: Kolom ke-4 adalah TANGGAL AWAL REGREASING (sebelumnya ke-3)
         sheetMaster.getRange(barisDitemukan, 4).setValue(tanggalBaru);
         
         // SANGAT PENTING: Jalankan ulang fungsi rekap
         buatRekapRegreasing(); 
         
         return responseJSON({
           "status": "success", 
           "message": "Tanggal regreasing untuk " + namaMotorDicari + " berhasil diperbarui menjadi " + tanggalBaru
         });
       } else {
         return responseJSON({"status": "error", "message": "Motor " + namaMotorDicari + " tidak ditemukan."});
       }
    }

    // ========================================================
    // TAMBAH EMAIL
    // ========================================================
    if (data.action === "add_email") {
      var sheetEmail = getOrCreateEmailSheet(); 
      var nextNo = sheetEmail.getLastRow(); 
      sheetEmail.appendRow([nextNo, data.nama, data.email, "AKTIF"]);
      return responseJSON({"status": "success", "message": "Email " + data.email + " berhasil didaftarkan"});
    }

    // ========================================================
    // ACTION: UPDATE EMAIL
    // ========================================================
    if (data.action === "update_email") {
      var sheetEmail = getOrCreateEmailSheet(); 
      var dataAll = sheetEmail.getDataRange().getValues();
      var barisDitemukan = -1;
      
      for (var i = 1; i < dataAll.length; i++) {
        if (String(dataAll[i][0]).trim() === String(data.no).trim()) {
          barisDitemukan = i + 1;
          break;
        }
      }
      
      if (barisDitemukan !== -1) {
        sheetEmail.getRange(barisDitemukan, 2).setValue(data.nama);
        sheetEmail.getRange(barisDitemukan, 3).setValue(data.email);
        sheetEmail.getRange(barisDitemukan, 4).setValue(data.status);
        
        return responseJSON({
          "status": "success", 
          "message": "Data email " + data.nama + " berhasil diupdate"
        });
      } else {
        return responseJSON({
          "status": "error", 
          "message": "Gagal Edit: Data dengan No " + data.no + " tidak ditemukan."
        });
      }
    }

    // ========================================================
    // ACTION: DELETE EMAIL
    // ========================================================
    if (data.action === "delete_email") {
      var sheetEmail = getOrCreateEmailSheet(); 
      var dataAll = sheetEmail.getDataRange().getValues();
      var barisDitemukan = -1;
      
      for (var i = 1; i < dataAll.length; i++) {
        if (String(dataAll[i][0]).trim() === String(data.no).trim()) {
          barisDitemukan = i + 1;
          break;
        }
      }
      
      if (barisDitemukan !== -1) {
        sheetEmail.deleteRow(barisDitemukan);
        return responseJSON({
          "status": "success", 
          "message": "Data email berhasil dihapus"
        });
      } else {
        return responseJSON({
          "status": "error", 
          "message": "Gagal Hapus: Data dengan No " + data.no + " tidak ditemukan."
        });
      }
    }

    // ========================================================
    // KODE LAMA: Input data harian maintenance
    // ========================================================
    var sheetName = data.targetSheet; 
    var sheet = doc.getSheetByName(sheetName);
    
    if (!sheet) {
      return responseJSON({"status": "error", "message": "Sheet '" + sheetName + "' tidak ditemukan."});
    }
    
    var lastCol = sheet.getLastColumn();
    if (lastCol == 0) return responseJSON({"status": "error", "message": "Sheet kosong"});
    
    var headers = sheet.getRange(1, 1, 1, lastCol).getValues()[0];
    var newRow = new Array(headers.length);
    
    setValueByName(headers, newRow, "Timestamp", new Date());
    setValueByName(headers, newRow, "Email Address", data.email);
    setValueByName(headers, newRow, "PILIH SALAH SATU", data.maintenanceType);
    setValueByName(headers, newRow, "SECTION NO", data.sectionNo);
    
    setValueByName(headers, newRow, "VIBRASI BEARING DE H", data.vibrasiDE_H);
    setValueByName(headers, newRow, "VIBRASI BEARING DE V", data.vibrasiDE_V);
    setValueByName(headers, newRow, "VIBRASI BEARING DE AX", data.vibrasiDE_Ax);
    setValueByName(headers, newRow, "VIBRASI BEARING DE GE", data.vibrasiDE_gE);

    setValueByName(headers, newRow, "VIBRASI BEARING NDE H", data.vibrasiNDE_H);
    setValueByName(headers, newRow, "VIBRASI BEARING NDE V", data.vibrasiNDE_V);
    setValueByName(headers, newRow, "VIBRASI BEARING NDE AX", data.vibrasiNDE_Ax);
    setValueByName(headers, newRow, "VIBRASI BEARING NDE GE", data.vibrasiNDE_gE);

    setValueByName(headers, newRow, "TEMPERATURE BEARING DE", data.tempDE);
    setValueByName(headers, newRow, "TEMPERATURE BEARING NDE", data.tempNDE);
    setValueByName(headers, newRow, "SUHU RUANGAN/VENTILASI DAN PENERANGAN", data.suhuRuang);
    setValueByName(headers, newRow, "BEBAN GENERATOR", data.beban);
    setValueByName(headers, newRow, "OPENING DAMPER", data.damper);
    
    setValueByName(headers, newRow, "LOAD CURRENT R", data.amperR);
    setValueByName(headers, newRow, "LOAD CURRENT S", data.amperS);
    setValueByName(headers, newRow, "LOAD CURRENT T", data.amperT);
    
    setValueByName(headers, newRow, "BUNYI MOTOR (BEARING)", data.bunyi);
    setValueByName(headers, newRow, "PANEL LOCAL/TOMBOL START-STOP", data.panel);
    setValueByName(headers, newRow, "KELENGKAPAN MOTOR", data.kelengkapan);
    setValueByName(headers, newRow, "KEBERSIHAN MOTOR", data.kebersihan);
    setValueByName(headers, newRow, "SISTEM PENTANAHAN ATAU GROUNDING", data.grounding);
    setValueByName(headers, newRow, "REGREASING BEARING SISI DE DAN NDE", data.regreasing);
    setValueByName(headers, newRow, "ACTIONS", data.actions);

    sheet.appendRow(newRow);
    
    kirimNotifikasiEmail(sheetName, "Website/Aplikasi", newRow, headers);
    
    urutkanSheet(sheet);
    buatRekapRegreasing(); 
    buatRekapDataTerbaru(); 

    return responseJSON({"status": "success", "row": sheet.getLastRow()});
    
  } catch (error) {
    return responseJSON({"status": "error", "message": "Error pada server Apps Script: " + error.toString()});
  }
}

// ==========================================
// 3. FUNGSI KHUSUS GOOGLE FORM (TRIGGER)
// ==========================================
function urutkanOtomatis(e) {
  try {
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    
    var lastRow = sheet.getLastRow();
    var lastCol = sheet.getLastColumn();
    var lastRowData = [];
    var headers = [];
    
    if (lastRow > 1 && lastCol > 0) {
      headers = sheet.getRange(1, 1, 1, lastCol).getDisplayValues()[0];
      lastRowData = sheet.getRange(lastRow, 1, 1, lastCol).getDisplayValues()[0];
    }
    
    kirimNotifikasiEmail(sheet.getName(), "Google Form", lastRowData, headers);

    urutkanSheet(sheet);
    buatRekapRegreasing(); 
    buatRekapDataTerbaru(); 
  } catch (err) {
    Logger.log("Error Sort: " + err.toString());
  }
}

// ==========================================
// 3.5. FUNGSI KIRIM EMAIL
// ==========================================
function kirimNotifikasiEmail(sheetName, sumberData, rowData, headers) {
  try {
    var subject = "[SISTEM MAINTENANCE] Data Baru Masuk - " + sheetName;
    var body = "Halo,\n\nTelah masuk laporan/data maintenance baru di sistem.\n\n";
    
    body += "Nama Motor         : " + sheetName + "\n";
    body += "Sumber Input       : " + sumberData + "\n";
    body += "Waktu Input        : " + new Date().toLocaleString("id-ID") + "\n\n";
    
    if (rowData && rowData.length > 0 && headers && headers.length > 0) {
      body += "=== DETAIL DATA YANG DIINPUT ===\n\n";
      for (var i = 0; i < headers.length; i++) {
        var namaKolom = headers[i] ? headers[i].toString().trim() : "Kolom " + (i + 1);
        var nilaiData = (rowData[i] !== undefined && rowData[i] !== "") ? rowData[i] : "-";
        
        if (namaKolom !== "") {
          body += namaKolom + " : " + nilaiData + "\n";
        }
      }
      body += "\n================================\n\n";
    }
    body += "Silakan cek data di website Monitoring.\n\nTerima Kasih,\nSistem Automasi Electrical Maintenance";
    var penerimaAktif = getDynamicEmails(); 
    MailApp.sendEmail(penerimaAktif, subject, body);
  } catch (e) {
    Logger.log("Gagal mengirim email notifikasi: " + e.toString());
  }
}

// ==========================================
// 4. LOGIKA UTAMA: SORTIR & RATA KIRI
// ==========================================
function urutkanSheet(sheet) {
  var lastRow = sheet.getLastRow();
  var lastCol = sheet.getLastColumn();
  
  if (lastRow > 1) {
    var rangeData = sheet.getRange(2, 1, lastRow - 1, lastCol);
    rangeData.sort({column: 1, ascending: true});
    rangeData.setHorizontalAlignment("left");
    rangeData.setVerticalAlignment("middle");
  }
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================
function setValueByName(headers, rowArray, colName, value) {
  var index = headers.indexOf(colName);
  if (index === -1) {
    index = headers.findIndex(function(h) {
      return h.trim().toUpperCase() === colName.trim().toUpperCase();
    });
  }
  if (index !== -1) {
    rowArray[index] = value;
  }
}

function responseJSON(content) {
  return ContentService.createTextOutput(JSON.stringify(content)).setMimeType(ContentService.MimeType.JSON);
}

function getColumnIndexByName(headersArray, keyword) {
  for (var k = 0; k < headersArray.length; k++) {
    if (String(headersArray[k]).toUpperCase().indexOf(keyword) !== -1) return k;
  }
  return -1;
}

function parseGoogleDate(dateInput) {
  if (!dateInput) return null;
  if (Object.prototype.toString.call(dateInput) === '[object Date]') {
    return new Date(dateInput.getTime());
  }
  var dateStr = String(dateInput).split(" ")[0];
  if (dateStr.indexOf("/") !== -1) {
    var parts = dateStr.split("/");
    if (parts.length === 3) {
      if (parts[2].length === 4) {
        return new Date(parts[2], parts[1] - 1, parts[0]); // Format DD/MM/YYYY
      } else if (parts[0].length === 4) {
        return new Date(parts[0], parts[1] - 1, parts[2]); // Format YYYY/MM/DD
      }
    }
  }
  var d = new Date(dateStr);
  if (!isNaN(d.getTime())) return d;
  return null;
}

// ==================================================================================
// MEMBUAT & MEMBACA MASTER JADWAL REGREASING
// ==================================================================================
// ==================================================================================
// MEMBUAT, MEMBACA, & SINKRONISASI MASTER JADWAL (UPDATE OTOMATIS SECTION NO)
// ==================================================================================
// ==================================================================================
// MEMBUAT, MEMBACA, & SINKRONISASI MASTER JADWAL (VERSI OPTIMASI / LEBIH CEPAT)
// ==================================================================================
function persiapkanMasterJadwal() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheetMaster = ss.getSheetByName("MASTER_JADWAL");
  
  if (!sheetMaster) {
    sheetMaster = ss.insertSheet("MASTER_JADWAL", 0);
    sheetMaster.getRange("A1:E1").setValues([["NO", "NAMA MOTOR", "SECTION NO", "TANGGAL AWAL REGREASING", "KETERANGAN"]]);
    sheetMaster.getRange("A1:E1").setFontWeight("bold").setBackground("#FFC000").setHorizontalAlignment("center");
  }

  var masterRange = sheetMaster.getDataRange();
  var masterValues = masterRange.getValues();
  var mapMaster = {};

  for (var i = 1; i < masterValues.length; i++) {
    var motorName = String(masterValues[i][1]).trim();
    if (motorName !== "") {
      mapMaster[motorName] = i; 
    }
  }

  var sheets = ss.getSheets();
  var isModified = false;
  var newRows = [];
  var lastRow = sheetMaster.getLastRow();

  sheets.forEach(function(sh) {
    var name = sh.getName().trim();
    var nameLower = name.toLowerCase();
    
    if (name === "MASTER_JADWAL" || name === "REKAP_REGREASING" || name === "DATA_TERBARU" || name === "PENGATURAN_EMAIL" || nameLower.indexOf("dashboard") !== -1 || nameLower.indexOf("master") !== -1 || nameLower.indexOf("email") !== -1) {
      return; 
    }
    
    var sectionVal = "-";
    var shLastRow = sh.getLastRow();
    var shLastCol = sh.getLastColumn();

    // OPTIMASI KECEPATAN: 
    // Hanya baca Baris ke-1 (Header) dan Baris Terakhir (Data Terbaru) saja!
    // Tidak lagi membaca ribuan baris di tengah-tengah.
    if (shLastRow > 1 && shLastCol > 0) {
      var headers = sh.getRange(1, 1, 1, shLastCol).getValues()[0];
      var lastRowData = sh.getRange(shLastRow, 1, 1, shLastCol).getValues()[0];
      
      var idxSection = getColumnIndexByName(headers, "SECTION NO");
      if (idxSection !== -1) {
        sectionVal = String(lastRowData[idxSection]).trim();
        if (sectionVal === "") sectionVal = "-";
      }
    }

    if (mapMaster[name] === undefined) {
      lastRow++;
      newRows.push([lastRow - 1, name, sectionVal, "", ""]); 
      mapMaster[name] = true; 
      isModified = true;
    } else {
      var rowIndex = mapMaster[name];
      var currentSection = String(masterValues[rowIndex][2]).trim();

      if (currentSection !== sectionVal) {
        masterValues[rowIndex][2] = sectionVal; 
        isModified = true;
      }
    }
  });

  if (isModified) {
    if (masterValues.length > 1) {
      masterRange.setValues(masterValues); 
    }
    if (newRows.length > 0) {
      sheetMaster.getRange(masterValues.length + 1, 1, newRows.length, 5).setValues(newRows); 
    }
    sheetMaster.autoResizeColumns(1, 4);
    sheetMaster.getRange(2, 2, sheetMaster.getLastRow(), 1).setHorizontalAlignment("left");
    sheetMaster.getRange(2, 3, sheetMaster.getLastRow(), 2).setHorizontalAlignment("center");
  }

  var freshData = sheetMaster.getDataRange().getValues();
  var finalMap = {};
  for (var k = 1; k < freshData.length; k++) {
    finalMap[String(freshData[k][1]).trim()] = freshData[k][3]; 
  }
  return finalMap;
}

// ==================================================================================
// FUNGSI UPDATE REKAP REGREASING
// ==================================================================================
// ==================================================================================
// FUNGSI UPDATE REKAP REGREASING (VERSI OPTIMASI MEMORI / SUPER CEPAT)
// ==================================================================================
function buatRekapRegreasing() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var mapMaster = persiapkanMasterJadwal(); // Ini sudah pakai versi cepat
  var namaSheetRekap = "REKAP_REGREASING";
  var sheetRekap = ss.getSheetByName(namaSheetRekap);
  
  if (!sheetRekap) {
    sheetRekap = ss.insertSheet(namaSheetRekap, 1); 
  } else {
    sheetRekap.clear(); 
  }
  
  var INTERVAL_BULAN = 3; 
  var hariIni = new Date();
  hariIni.setHours(0,0,0,0);
  
  var sheets = ss.getSheets();
  var outputData = [];
  
  outputData.push([
    "NO", "NAMA MOTOR", "STATUS TERBARU", "TGL SELESAI AKTUAL", "JADWAL NEXT (FIXED)", "SISA HARI", "KETERANGAN ALARM", "DIUPDATE OLEH (EMAIL)"
  ]);
  
  var noUrut = 1;

  for (var i = 0; i < sheets.length; i++) {
    var sheet = sheets[i];
    var namaMotor = sheet.getName().trim();
    var nameLower = namaMotor.toLowerCase();

    if (namaMotor === "MASTER_JADWAL" || namaMotor === "REKAP_REGREASING" || namaMotor === "DATA_TERBARU" || namaMotor === "PENGATURAN_EMAIL" || nameLower.indexOf("master") !== -1 || nameLower.indexOf("dashboard") !== -1 || nameLower.indexOf("email") !== -1) {
      continue;
    }

    var lastRow = sheet.getLastRow();
    if (lastRow <= 1) {
        outputData.push([noUrut, namaMotor, "BELUM ADA DATA", "-", "-", "-", "Belum ada riwayat form", "-"]);
        noUrut++;
        continue;
    }

    var lastCol = sheet.getLastColumn();
    // Tarik headernya saja untuk mencari posisi kolom
    var headers = sheet.getRange(1, 1, 1, lastCol).getValues()[0];

    var colRegreasing = getColumnIndexByName(headers, "REGREASING");
    var colWaktu = getColumnIndexByName(headers, "TIMESTAMP"); 
    if (colWaktu === -1) colWaktu = getColumnIndexByName(headers, "WAKTU");
    var colEmail = getColumnIndexByName(headers, "EMAIL"); 

    if (colRegreasing === -1 || colWaktu === -1) {
       outputData.push([noUrut, namaMotor, "ERROR KOLOM", "-", "-", "-", "Kolom Regreasing/Waktu tidak ditemukan", "-"]);
       noUrut++;
       continue; 
    }

    // 🚀 OPTIMASI KECEPATAN: 
    // Jangan sedot semua data! Tarik SATU KOLOM yang dibutuhkan saja dari baris ke-2 hingga terakhir.
    // Index di Apps Script getRange dimulai dari 1, sedangkan array headers dimulai dari 0 (makanya +1)
    var numRowsData = lastRow - 1;
    var statusData = sheet.getRange(2, colRegreasing + 1, numRowsData, 1).getValues();
    var waktuData = sheet.getRange(2, colWaktu + 1, numRowsData, 1).getValues();
    var emailData = colEmail !== -1 ? sheet.getRange(2, colEmail + 1, numRowsData, 1).getValues() : null;

    var statusTerbaru = "N/A";
    var lastSelesaiTime = null;
    var emailTerbaru = "-"; 

    // Loop mundur dari data terbawah untuk mencari status SELESAI terakhir
    for (var r = statusData.length - 1; r >= 0; r--) {
      // Data yang diambil per kolom bentuknya array 2D, jadi akses index [r][0]
      var val = String(statusData[r][0]).replace(/(<([^>]+)>)/gi, "").trim().toUpperCase();
      
      if (statusTerbaru === "N/A" && (val === "SELESAI" || val === "BELUM")) {
         statusTerbaru = val; 
         if (emailData && emailData[r][0]) {
             emailTerbaru = String(emailData[r][0]).trim(); 
         }
      }
      
      if (val === "SELESAI" && lastSelesaiTime === null) {
         lastSelesaiTime = waktuData[r][0];
      }

      if (statusTerbaru !== "N/A" && lastSelesaiTime !== null) {
         break; // Sudah ketemu, stop loop mundur!
      }
    }

    var tglTerakhirStr = "-";
    var jadwalNextStr = "-";
    var sisaHariStr = "-";
    var keterangan = "BELUM DISETTING";

    var lastSelesaiObj = null;
    if (lastSelesaiTime && lastSelesaiTime !== "") {
        lastSelesaiObj = parseGoogleDate(lastSelesaiTime);
        if (lastSelesaiObj) {
            tglTerakhirStr = Utilities.formatDate(lastSelesaiObj, Session.getScriptTimeZone(), "dd/MM/yyyy");
        }
    }

    var baseDateRaw = mapMaster[namaMotor];
    if (baseDateRaw && baseDateRaw !== "") {
        var baseDateObj = parseGoogleDate(baseDateRaw);
        
        if (baseDateObj) {
            var targetDate = new Date(baseDateObj.getTime());
            var compareDate = lastSelesaiObj ? lastSelesaiObj : new Date(targetDate.getTime() - 86400000);

            while (targetDate.getTime() <= compareDate.getTime()) {
                targetDate.setMonth(targetDate.getMonth() + INTERVAL_BULAN);
            }

            jadwalNextStr = Utilities.formatDate(targetDate, Session.getScriptTimeZone(), "dd/MM/yyyy");

            var selisihWaktu = targetDate.getTime() - hariIni.getTime();
            var sisaHari = Math.ceil(selisihWaktu / (1000 * 3600 * 24));
            sisaHariStr = sisaHari;

            if (sisaHari > 14) {
              keterangan = "✅ AMAN";
            } else if (sisaHari > 0 && sisaHari <= 14) {
              keterangan = "⚠️ WARNING (Mendekati Jadwal)";
            } else if (sisaHari === 0) {
              keterangan = "⚠️ HARI INI JADWALNYA";
            } else {
              keterangan = "❌ OVERDUE (Lewat " + Math.abs(sisaHari) + " Hari)";
            }
        } else {
            keterangan = "FORMAT TGL MASTER SALAH";
        }
    } else {
        keterangan = "TANGGAL AWAL DI MASTER KOSONG";
    }

    outputData.push([noUrut, namaMotor, statusTerbaru, tglTerakhirStr, jadwalNextStr, sisaHariStr, keterangan, emailTerbaru]);
    noUrut++;
  }

  // Tulis Output ke Sheet Rekap (Sekaligus)
  if (outputData.length > 0) {
    var fullRange = sheetRekap.getRange(1, 1, outputData.length, outputData[0].length);
    fullRange.setValues(outputData);
    fullRange.setBorder(true, true, true, true, true, true);
    fullRange.setVerticalAlignment("middle");
    
    var headerRange = sheetRekap.getRange(1, 1, 1, outputData[0].length);
    headerRange.setFontWeight("bold").setBackground("#4F81BD").setFontColor("white").setHorizontalAlignment("center");
    
    if (outputData.length > 1) {
      var bodyRange = sheetRekap.getRange(2, 1, outputData.length - 1, outputData[0].length);
      bodyRange.setHorizontalAlignment("center"); 
      sheetRekap.getRange(2, 2, outputData.length - 1, 1).setHorizontalAlignment("left");
      sheetRekap.getRange(2, 7, outputData.length - 1, 1).setHorizontalAlignment("left");
      sheetRekap.getRange(2, 8, outputData.length - 1, 1).setHorizontalAlignment("left");
    }
    sheetRekap.autoResizeColumns(1, outputData[0].length);
  }
}

// ==================================================================================
// TRIGGER HARIAN & REKAP DATA TERBARU LENGKAP
// ==================================================================================
function updateRekapHarian() {
  try {
    buatRekapRegreasing();
    buatRekapDataTerbaru(); 
    Logger.log("Update rekap harian berhasil dijalankan pada " + new Date());
  } catch (e) {
    Logger.log("Error Update Harian: " + e.toString());
  }
}

// ==================================================================================
// REKAP DATA TERBARU DENGAN KOLOM-KOLOM BARU
// ==================================================================================
function buatRekapDataTerbaru() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var namaSheetTerbaru = "DATA_TERBARU";
  var sheetTerbaru = ss.getSheetByName(namaSheetTerbaru);
  
  if (!sheetTerbaru) {
    sheetTerbaru = ss.insertSheet(namaSheetTerbaru, 2); 
  } else {
    sheetTerbaru.clear(); 
  }
  
  var sheets = ss.getSheets();
  var outputData = [];
  
  outputData.push([
    "NAMA MOTOR", 
    "TIMESTAMP", 
    "EMAIL ADDRESS", 
    "STATUS", 
    "SECTION NO", 
    "VIB DE H", "VIB DE V", "VIB DE Ax", "VIB DE gE",
    "VIB NDE H", "VIB NDE V", "VIB NDE Ax", "VIB NDE gE",
    "TEMP DE (°C)", 
    "TEMP NDE (°C)", 
    "BEBAN GEN", 
    "DAMPER (%)", 
    "ARUS R", "ARUS S", "ARUS T",
    "SUHU RUANG/VENTILASI", 
    "BUNYI BEARING", 
    "PANEL LOKAL", 
    "KELENGKAPAN", 
    "KEBERSIHAN", 
    "GROUNDING", 
    "REGREASING", 
    "ACTIONS"
  ]);

  for (var i = 0; i < sheets.length; i++) {
    var sheet = sheets[i];
    var namaMotor = sheet.getName();
    var nameLower = namaMotor.toLowerCase();

    if (namaMotor === "MASTER_JADWAL" || namaMotor === "REKAP_REGREASING" || namaMotor === "DATA_TERBARU" || namaMotor === "PENGATURAN_EMAIL" || nameLower.indexOf("master") !== -1 || nameLower.indexOf("dashboard") !== -1 || nameLower.indexOf("email") !== -1) {
      continue;
    }

    var lastRow = sheet.getLastRow();
    if (lastRow <= 1) continue; 

    var sheetHeaders = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getDisplayValues()[0];
    var lastRowData = sheet.getRange(lastRow, 1, 1, sheet.getLastColumn()).getDisplayValues()[0];

    var idxWaktu = getColumnIndexByName(sheetHeaders, "TIMESTAMP"); if(idxWaktu === -1) idxWaktu = getColumnIndexByName(sheetHeaders, "WAKTU");
    var idxEmail = getColumnIndexByName(sheetHeaders, "EMAIL ADDRESS");
    var idxStatus = getColumnIndexByName(sheetHeaders, "PILIH SALAH SATU");
    var idxSection = getColumnIndexByName(sheetHeaders, "SECTION NO");

    var idxVibDE_H = getColumnIndexByName(sheetHeaders, "VIBRASI BEARING DE H");
    var idxVibDE_V = getColumnIndexByName(sheetHeaders, "VIBRASI BEARING DE V");
    var idxVibDE_Ax = getColumnIndexByName(sheetHeaders, "VIBRASI BEARING DE AX");
    var idxVibDE_gE = getColumnIndexByName(sheetHeaders, "VIBRASI BEARING DE GE");

    var idxVibNDE_H = getColumnIndexByName(sheetHeaders, "VIBRASI BEARING NDE H");
    var idxVibNDE_V = getColumnIndexByName(sheetHeaders, "VIBRASI BEARING NDE V");
    var idxVibNDE_Ax = getColumnIndexByName(sheetHeaders, "VIBRASI BEARING NDE AX");
    var idxVibNDE_gE = getColumnIndexByName(sheetHeaders, "VIBRASI BEARING NDE GE");

    var idxTempDE = getColumnIndexByName(sheetHeaders, "TEMPERATURE BEARING DE");
    var idxTempNDE = getColumnIndexByName(sheetHeaders, "TEMPERATURE BEARING NDE");
    var idxBeban = getColumnIndexByName(sheetHeaders, "BEBAN GENERATOR");
    var idxDamper = getColumnIndexByName(sheetHeaders, "OPENING DAMPER");

    var idxArus_R = getColumnIndexByName(sheetHeaders, "LOAD CURRENT R");
    var idxArus_S = getColumnIndexByName(sheetHeaders, "LOAD CURRENT S");
    var idxArus_T = getColumnIndexByName(sheetHeaders, "LOAD CURRENT T");

    var idxSuhuRuang = getColumnIndexByName(sheetHeaders, "SUHU RUANGAN");
    var idxBunyi = getColumnIndexByName(sheetHeaders, "BUNYI MOTOR");
    var idxPanel = getColumnIndexByName(sheetHeaders, "PANEL LOCAL");
    var idxKelengkapan = getColumnIndexByName(sheetHeaders, "KELENGKAPAN MOTOR");
    var idxKebersihan = getColumnIndexByName(sheetHeaders, "KEBERSIHAN MOTOR");
    var idxGrounding = getColumnIndexByName(sheetHeaders, "PENTANAHAN"); if (idxGrounding === -1) idxGrounding = getColumnIndexByName(sheetHeaders, "GROUNDING");
    var idxRegreasing = getColumnIndexByName(sheetHeaders, "REGREASING");
    var idxActions = getColumnIndexByName(sheetHeaders, "ACTIONS");

    outputData.push([
      namaMotor,
      idxWaktu !== -1 ? lastRowData[idxWaktu] : "-",
      idxEmail !== -1 ? lastRowData[idxEmail] : "-",
      idxStatus !== -1 ? lastRowData[idxStatus] : "-",
      idxSection !== -1 ? lastRowData[idxSection] : "-",
      
      idxVibDE_H !== -1 ? lastRowData[idxVibDE_H] : "-",
      idxVibDE_V !== -1 ? lastRowData[idxVibDE_V] : "-",
      idxVibDE_Ax !== -1 ? lastRowData[idxVibDE_Ax] : "-",
      idxVibDE_gE !== -1 ? lastRowData[idxVibDE_gE] : "-",
      idxVibNDE_H !== -1 ? lastRowData[idxVibNDE_H] : "-",
      idxVibNDE_V !== -1 ? lastRowData[idxVibNDE_V] : "-",
      idxVibNDE_Ax !== -1 ? lastRowData[idxVibNDE_Ax] : "-",
      idxVibNDE_gE !== -1 ? lastRowData[idxVibNDE_gE] : "-",
      
      idxTempDE !== -1 ? lastRowData[idxTempDE] : "-",
      idxTempNDE !== -1 ? lastRowData[idxTempNDE] : "-",
      idxBeban !== -1 ? lastRowData[idxBeban] : "-",
      idxDamper !== -1 ? lastRowData[idxDamper] : "-",
      
      idxArus_R !== -1 ? lastRowData[idxArus_R] : "-",
      idxArus_S !== -1 ? lastRowData[idxArus_S] : "-",
      idxArus_T !== -1 ? lastRowData[idxArus_T] : "-",
      
      idxSuhuRuang !== -1 ? lastRowData[idxSuhuRuang] : "-",
      idxBunyi !== -1 ? lastRowData[idxBunyi] : "-",
      idxPanel !== -1 ? lastRowData[idxPanel] : "-",
      idxKelengkapan !== -1 ? lastRowData[idxKelengkapan] : "-",
      idxKebersihan !== -1 ? lastRowData[idxKebersihan] : "-",
      idxGrounding !== -1 ? lastRowData[idxGrounding] : "-",
      idxRegreasing !== -1 ? lastRowData[idxRegreasing] : "-",
      idxActions !== -1 ? lastRowData[idxActions] : "-"
    ]);
  }

  if (outputData.length > 0) {
    var fullRange = sheetTerbaru.getRange(1, 1, outputData.length, outputData[0].length);
    fullRange.setValues(outputData);
    fullRange.setBorder(true, true, true, true, true, true);
    fullRange.setVerticalAlignment("middle");
    
    var headerRange = sheetTerbaru.getRange(1, 1, 1, outputData[0].length);
    headerRange.setFontWeight("bold").setBackground("#17a2b8").setFontColor("white").setHorizontalAlignment("center").setWrap(true);
    
    if (outputData.length > 1) {
      var bodyRange = sheetTerbaru.getRange(2, 1, outputData.length - 1, outputData[0].length);
      bodyRange.setHorizontalAlignment("center");
      sheetTerbaru.getRange(2, 1, outputData.length - 1, 1).setHorizontalAlignment("left"); 
      sheetTerbaru.getRange(2, outputData[0].length, outputData.length - 1, 1).setHorizontalAlignment("left"); 
    }
    sheetTerbaru.autoResizeColumns(1, outputData[0].length);
  }
}

function getDynamicEmails() {
  var sheet = getOrCreateEmailSheet();
  var data = sheet.getDataRange().getValues();
  var listEmail = [];

  for (var i = 1; i < data.length; i++) {
    var email = data[i][2]; 
    var status = data[i][3]; 
    if (email && status.toString().toUpperCase() === "AKTIF") {
      listEmail.push(email.toString().trim());
    }
  }

  return listEmail.length > 0 ? listEmail.join(",") : "Muhsabri.mb@sig.id";
}

function getOrCreateEmailSheet() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheetName = "PENGATURAN_EMAIL";
  var sheet = ss.getSheetByName(sheetName);
  
  if (!sheet) {
    sheet = ss.insertSheet(sheetName);
    var header = [["NO", "NAMA PERSONIL", "ALAMAT EMAIL", "STATUS"]];
    sheet.getRange(1, 1, 1, 4).setValues(header);
    
    var headerRange = sheet.getRange("A1:D1");
    headerRange.setBackground("#007bff")
               .setFontColor("white")
               .setFontWeight("bold")
               .setHorizontalAlignment("center");
    
    sheet.getRange("A:A").setHorizontalAlignment("center"); 
    sheet.getRange("D:D").setHorizontalAlignment("center"); 
    
    sheet.appendRow([1, "Admin Utama", "Muhsabri.mb@sig.id", "AKTIF"]);
    sheet.autoResizeColumns(1, 4);
  }
  return sheet;
}