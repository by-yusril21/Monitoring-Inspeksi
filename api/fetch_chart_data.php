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
// Cari index kolom secara dinamis (Case Insensitive)
$colWaktu = -1;
$colVibDE = -1;
$colVibNDE = -1;

foreach ($headers as $index => $header) {
    $h = strtoupper(trim($header));
    if (strpos($h, 'TIMESTAMP') !== false || strpos($h, 'WAKTU') !== false)
        $colWaktu = $index;
    if (strpos($h, 'VIBRASI BEARING DE') !== false)
        $colVibDE = $index;
    if (strpos($h, 'VIBRASI BEARING NDE') !== false)
        $colVibNDE = $index;
}

if ($colWaktu == -1 || $colVibDE == -1 || $colVibNDE == -1) {
    echo json_encode(["status" => "error", "message" => "Kolom Timestamp, Vibrasi DE, atau Vibrasi NDE tidak ditemukan di sheet ini."]);
    exit;
}

$labels = [];
$dataDE = [];
$dataNDE = [];

// Looping data mulai dari baris ke-2 (index 1)
for ($i = 1; $i < count($rawData); $i++) {
    $row = $rawData[$i];

    // Pastikan baris memiliki data yang cukup
    if (!isset($row[$colWaktu]) || !isset($row[$colVibDE]) || !isset($row[$colVibNDE]))
        continue;

    $valTime = trim($row[$colWaktu]);
    $valDE = trim($row[$colVibDE]);
    $valNDE = trim($row[$colVibNDE]);

    // SKIP data kosong, '-', atau '--'
    if (
        $valDE == "" || $valDE == "-" || $valDE == "--" ||
        $valNDE == "" || $valNDE == "-" || $valNDE == "--"
    ) {
        continue;
    }

    // Ambil format Tanggal/Bulan/Tahun dari Timestamp Google Sheets
    $formattedDate = $valTime;
    if (strpos($valTime, ' ') !== false) {
        $parts = explode(' ', $valTime); // Pisahkan antara tanggal dan jam
        $dateParts = explode('/', $parts[0]);
        if (count($dateParts) == 3) {
            // Gabungkan kembali menjadi format DD/MM/YYYY
            $formattedDate = $dateParts[0] . '/' . $dateParts[1] . '/' . $dateParts[2];
        } else {
            // Jika formatnya berbeda, cukup ambil teks tanggalnya saja (sebelum spasi)
            $formattedDate = $parts[0];
        }
    }

    $labels[] = $formattedDate;

    // Pastikan angka menggunakan titik untuk desimal, bukan koma
    $valDE = str_replace(',', '.', $valDE);
    $valNDE = str_replace(',', '.', $valNDE);

    $dataDE[] = (float) $valDE;
    $dataNDE[] = (float) $valNDE;
}

// Kembalikan data yang sudah bersih dan terstruktur untuk Chart.js
echo json_encode([
    "status" => "success",
    "labels" => $labels,
    "dataDE" => $dataDE,
    "dataNDE" => $dataNDE
]);
?>