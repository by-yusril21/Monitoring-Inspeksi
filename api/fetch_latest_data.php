<?php


// File: api/fetch_latest_data.php
header('Content-Type: application/json');
require_once '../config/cek_session.php';
require_once '../config/config.php';

// Tangkap parameter unit (contoh: C6KV)
$unit = isset($_GET['unit']) ? strtoupper($_GET['unit']) : '';

if (empty($unit)) {
    echo json_encode(["status" => "error", "message" => "Parameter unit wajib diisi."]);
    exit;
}

if (!array_key_exists($unit, $SCRIPT_URLS) || empty($SCRIPT_URLS[$unit])) {
    echo json_encode(["status" => "error", "message" => "URL API belum disetting."]);
    exit;
}

// KITA LANGSUNG TEMBAK KE SHEET "DATA_TERBARU"
$targetUrl = $SCRIPT_URLS[$unit] . "?token=" . urlencode($API_TOKEN) . "&sheet=DATA_TERBARU";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(["status" => "error", "message" => "cURL Error: " . $error]);
    exit;
}

$rawData = json_decode($response, true);

if (!$rawData || isset($rawData['error']) || (isset($rawData['status']) && $rawData['status'] == 'error')) {
    echo json_encode(["status" => "error", "message" => "Gagal mengambil data dari server Google."]);
    exit;
}

// Jika data kosong (hanya ada header)
if (count($rawData) <= 1) {
    echo json_encode(["status" => "empty", "data" => []]);
    exit;
}

$headers = $rawData[0];
$formattedData = [];

// Format data baris demi baris agar mudah dibaca oleh JavaScript
for ($i = 1; $i < count($rawData); $i++) {
    $row = $rawData[$i];
    $rowData = [];
    foreach ($headers as $index => $headerName) {
        $key = strtoupper(trim($headerName)); // Nama header jadi kunci array
        $rowData[$key] = isset($row[$index]) ? trim($row[$index]) : '-';
    }
    $formattedData[] = $rowData;
}

echo json_encode([
    "status" => "success",
    "unit" => $unit,
    "data" => $formattedData
]);
?>