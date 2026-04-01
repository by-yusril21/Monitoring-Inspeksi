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
// Contoh: .../exec?token=SemenTonasa2026&sheet=BFP%206A
$targetUrl = $SCRIPT_URLS[$unit] . "?token=" . urlencode($API_TOKEN) . "&sheet=" . urlencode($sheetName);

// 4. Lakukan cURL ke Google Apps Script
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Tambahkan timeout agar tidak menggantung terlalu lama
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

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
    echo json_encode(["status" => "success", "labels" => [], "dataDE" => [], "dataNDE" => []]);
    exit;
}

$headers = $rawData[0];

// Inisialisasi index kolom ke -1
$colWaktu = -1;
$colVibDE = -1;
$colVibNDE = -1;
$colTempDE = -1;
$colTempNDE = -1;
$colSuhu = -1;
$colBeban = -1;
$colDamper = -1;
$colCurrent = -1;

// Cari index kolom secara dinamis (Case Insensitive)
foreach ($headers as $index => $header) {
    $h = strtoupper(trim($header));
    if (strpos($h, 'TIMESTAMP') !== false || strpos($h, 'WAKTU') !== false)
        $colWaktu = $index;
    if (strpos($h, 'VIBRASI BEARING DE') !== false)
        $colVibDE = $index;
    if (strpos($h, 'VIBRASI BEARING NDE') !== false)
        $colVibNDE = $index;
    if (strpos($h, 'TEMPERATURE BEARING DE') !== false)
        $colTempDE = $index;
    if (strpos($h, 'TEMPERATURE BEARING NDE') !== false)
        $colTempNDE = $index;
    if (strpos($h, 'SUHU RUANGAN') !== false)
        $colSuhu = $index;
    if (strpos($h, 'BEBAN GENERATOR') !== false)
        $colBeban = $index;
    if (strpos($h, 'OPENING DAMPER') !== false)
        $colDamper = $index;
    if (strpos($h, 'LOAD CURRENT') !== false)
        $colCurrent = $index;
}

if ($colWaktu == -1) {
    echo json_encode(["status" => "error", "message" => "Kolom Timestamp/Waktu tidak ditemukan di sheet ini."]);
    exit;
}

$labels = [];
$dataDE = [];
$dataNDE = [];
$dataTempDE = [];
$dataTempNDE = [];
$dataSuhu = [];
$dataBeban = [];
$dataDamper = [];
$dataCurrent = [];

// Fungsi pembantu untuk memproses value string menjadi float atau null
function parseValue($row, $colIndex)
{
    if ($colIndex == -1 || !isset($row[$colIndex]))
        return null;
    $val = trim($row[$colIndex]);
    if ($val === "" || $val === "-" || $val === "--")
        return null;
    return (float) str_replace(',', '.', $val);
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

    $labels[] = $formattedDate;

    // Tarik semua data (jika kolom tidak ada, akan otomatis bernilai null)
    $dataDE[] = parseValue($row, $colVibDE);
    $dataNDE[] = parseValue($row, $colVibNDE);
    $dataTempDE[] = parseValue($row, $colTempDE);
    $dataTempNDE[] = parseValue($row, $colTempNDE);
    $dataSuhu[] = parseValue($row, $colSuhu);
    $dataBeban[] = parseValue($row, $colBeban);
    $dataDamper[] = parseValue($row, $colDamper);
    $dataCurrent[] = parseValue($row, $colCurrent);
}

// Kembalikan data yang sudah lengkap ke Frontend
echo json_encode([
    "status" => "success",
    "labels" => $labels,
    "dataDE" => $dataDE,
    "dataNDE" => $dataNDE,
    "dataTempDE" => $dataTempDE,
    "dataTempNDE" => $dataTempNDE,
    "dataSuhu" => $dataSuhu,
    "dataBeban" => $dataBeban,
    "dataDamper" => $dataDamper,
    "dataCurrent" => $dataCurrent
]);
?>