<?php
// File: api/fetch_chart_data.php
header('Content-Type: application/json');

// 1. Panggil file konfigurasi rahasia
require_once '../config/cek_session.php';
require_once '../config/config.php';

// 2. Baca parameter yang dikirim via GET
$unit = isset($_GET['unit']) ? strtoupper($_GET['unit']) : '';
$sheetName = isset($_GET['sheet']) ? $_GET['sheet'] : '';

if (empty($unit) || empty($sheetName)) {
    echo json_encode(["status" => "error", "message" => "Parameter unit dan sheet wajib diisi."]);
    exit;
}

if (!array_key_exists($unit, $SCRIPT_URLS) || empty($SCRIPT_URLS[$unit])) {
    echo json_encode(["status" => "error", "message" => "URL API untuk unit ini belum disetting."]);
    exit;
}

// 3. Susun URL untuk request GET ke Google Apps Script
$targetUrl = $SCRIPT_URLS[$unit] . "?token=" . urlencode($API_TOKEN) . "&sheet=" . urlencode($sheetName);

// 4. Lakukan cURL ke Google Apps Script
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Tambahkan timeout agar tidak menggantung terlalu lama

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error) {
    echo json_encode(["status" => "error", "message" => "cURL Error: " . $error]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode(["status" => "error", "message" => "HTTP Code: " . $httpCode]);
    exit;
}

// 5. Decode Response dari Google
$rawData = json_decode($response, true);

if (!$rawData || isset($rawData['error']) || isset($rawData['status']) && $rawData['status'] == 'error') {
    $errorMsg = isset($rawData['message']) ? $rawData['message'] : (isset($rawData['error']) ? $rawData['error'] : 'Format response tidak valid');
    echo json_encode(["status" => "error", "message" => "Gagal mengambil data dari Google: " . $errorMsg]);
    exit;
}

// 6. FILTER & PARSING DATA UNTUK CHART
if (count($rawData) <= 1) {
    echo json_encode(["status" => "success", "labels" => [], "message" => "Belum ada data"]);
    exit;
}

$headers = $rawData[0];

// --- INISIALISASI INDEX SEMUA KOLOM (-1 = Tidak Ditemukan) ---
$colWaktu = -1;

// Vibrasi DE (4 Parameter)
$colVibDE_H = -1;
$colVibDE_V = -1;
$colVibDE_Ax = -1;
$colVibDE_gE = -1;
// Vibrasi NDE (4 Parameter)
$colVibNDE_H = -1;
$colVibNDE_V = -1;
$colVibNDE_Ax = -1;
$colVibNDE_gE = -1;

// Suhu & Beban (5 Parameter)
$colTempDE = -1;
$colTempNDE = -1;
$colSuhu = -1;
$colBeban = -1;
$colDamper = -1;

// Arus (3 Parameter)
$colCurrR = -1;
$colCurrS = -1;
$colCurrT = -1;


// Cari index kolom secara dinamis (Case Insensitive & Multi-keyword)
foreach ($headers as $index => $header) {
    $h = strtoupper(trim($header));

    // Waktu
    if (strpos($h, 'TIMESTAMP') !== false || strpos($h, 'WAKTU') !== false)
        $colWaktu = $index;

    // Vibrasi DE
    if (strpos($h, 'VIBRASI BEARING DE H') !== false || strpos($h, 'VIB DE H') !== false)
        $colVibDE_H = $index;
    if (strpos($h, 'VIBRASI BEARING DE V') !== false || strpos($h, 'VIB DE V') !== false)
        $colVibDE_V = $index;
    if (strpos($h, 'VIBRASI BEARING DE AX') !== false || strpos($h, 'VIB DE AX') !== false)
        $colVibDE_Ax = $index;
    if (strpos($h, 'VIBRASI BEARING DE GE') !== false || strpos($h, 'VIB DE GE') !== false)
        $colVibDE_gE = $index;

    // Vibrasi NDE
    if (strpos($h, 'VIBRASI BEARING NDE H') !== false || strpos($h, 'VIB NDE H') !== false)
        $colVibNDE_H = $index;
    if (strpos($h, 'VIBRASI BEARING NDE V') !== false || strpos($h, 'VIB NDE V') !== false)
        $colVibNDE_V = $index;
    if (strpos($h, 'VIBRASI BEARING NDE AX') !== false || strpos($h, 'VIB NDE AX') !== false)
        $colVibNDE_Ax = $index;
    if (strpos($h, 'VIBRASI BEARING NDE GE') !== false || strpos($h, 'VIB NDE GE') !== false)
        $colVibNDE_gE = $index;

    // Suhu & Operasional
    if (strpos($h, 'TEMPERATURE BEARING DE') !== false || strpos($h, 'TEMP. BEARING DE') !== false)
        $colTempDE = $index;
    if (strpos($h, 'TEMPERATURE BEARING NDE') !== false || strpos($h, 'TEMP. BEARING NDE') !== false)
        $colTempNDE = $index;
    if (strpos($h, 'SUHU RUANGAN') !== false)
        $colSuhu = $index;
    if (strpos($h, 'BEBAN GENERATOR') !== false)
        $colBeban = $index;
    if (strpos($h, 'OPENING DAMPER') !== false)
        $colDamper = $index;

    // Arus (Current)
    if (strpos($h, 'LOAD CURRENT R') !== false || strpos($h, 'CURRENT R') !== false)
        $colCurrR = $index;
    if (strpos($h, 'LOAD CURRENT S') !== false || strpos($h, 'CURRENT S') !== false)
        $colCurrS = $index;
    if (strpos($h, 'LOAD CURRENT T') !== false || strpos($h, 'CURRENT T') !== false)
        $colCurrT = $index;
}

if ($colWaktu == -1) {
    echo json_encode(["status" => "error", "message" => "Kolom Timestamp/Waktu tidak ditemukan di sheet ini."]);
    exit;
}

// --- SIAPKAN ARRAY UNTUK MENAMPUNG HASIL ---
$labels = [];

$dataDE_H = [];
$dataDE_V = [];
$dataDE_Ax = [];
$dataDE_gE = [];
$dataNDE_H = [];
$dataNDE_V = [];
$dataNDE_Ax = [];
$dataNDE_gE = [];

$dataTempDE = [];
$dataTempNDE = [];
$dataSuhu = [];
$dataBeban = [];
$dataDamper = [];

$dataCurrR = [];
$dataCurrS = [];
$dataCurrT = [];

// Fungsi pembantu untuk memproses value string menjadi float atau null
function parseValue($row, $colIndex)
{
    if ($colIndex == -1 || !isset($row[$colIndex]))
        return null;
    $val = trim($row[$colIndex]);
    if ($val === "" || $val === "-" || $val === "--")
        return null;
    return (float) str_replace(',', '.', $val); // Tangani koma desimal ala Indonesia
}

// Looping data mulai dari baris ke-2 (index 1)
for ($i = 1; $i < count($rawData); $i++) {
    $row = $rawData[$i];

    if (!isset($row[$colWaktu]))
        continue;
    $valTime = trim($row[$colWaktu]);
    if ($valTime == "")
        continue;

    // Ambil format Tanggal/Bulan/Tahun dari Timestamp
    $formattedDate = $valTime;
    if (strpos($valTime, ' ') !== false) {
        $parts = explode(' ', $valTime);
        $dateParts = explode('/', $parts[0]);
        if (count($dateParts) == 3) {
            $formattedDate = $dateParts[0] . '/' . $dateParts[1] . '/' . $dateParts[2];
        } else {
            $formattedDate = $parts[0];
        }
    }

    // Masukkan ke array Labels (Sumbu X)
    $labels[] = $formattedDate;

    // Tarik semua data Y-Axis
    $dataDE_H[] = parseValue($row, $colVibDE_H);
    $dataDE_V[] = parseValue($row, $colVibDE_V);
    $dataDE_Ax[] = parseValue($row, $colVibDE_Ax);
    $dataDE_gE[] = parseValue($row, $colVibDE_gE);

    $dataNDE_H[] = parseValue($row, $colVibNDE_H);
    $dataNDE_V[] = parseValue($row, $colVibNDE_V);
    $dataNDE_Ax[] = parseValue($row, $colVibNDE_Ax);
    $dataNDE_gE[] = parseValue($row, $colVibNDE_gE);

    $dataTempDE[] = parseValue($row, $colTempDE);
    $dataTempNDE[] = parseValue($row, $colTempNDE);
    $dataSuhu[] = parseValue($row, $colSuhu);
    $dataBeban[] = parseValue($row, $colBeban);
    $dataDamper[] = parseValue($row, $colDamper);

    $dataCurrR[] = parseValue($row, $colCurrR);
    $dataCurrS[] = parseValue($row, $colCurrS);
    $dataCurrT[] = parseValue($row, $colCurrT);
}

// Kembalikan JSON dengan key yang sesuai dengan file JS Frontend
echo json_encode([
    "status" => "success",
    "labels" => $labels,

    "dataDE_H" => $dataDE_H,
    "dataDE_V" => $dataDE_V,
    "dataDE_Ax" => $dataDE_Ax,
    "dataDE_gE" => $dataDE_gE,

    "dataNDE_H" => $dataNDE_H,
    "dataNDE_V" => $dataNDE_V,
    "dataNDE_Ax" => $dataNDE_Ax,
    "dataNDE_gE" => $dataNDE_gE,

    "dataTempDE" => $dataTempDE,
    "dataTempNDE" => $dataTempNDE,
    "dataSuhu" => $dataSuhu,
    "dataBeban" => $dataBeban,
    "dataDamper" => $dataDamper,

    "dataCurrR" => $dataCurrR,
    "dataCurrS" => $dataCurrS,
    "dataCurrT" => $dataCurrT
]);
?>