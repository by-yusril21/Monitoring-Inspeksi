<?php
// File: api_proxy.php
header('Content-Type: application/json');

// 1. Panggil file konfigurasi rahasia
require_once '../config/config.php';

// 2. Tangkap permintaan unit dari JavaScript (contoh: api_proxy.php?unit=C6KV)
$unit = isset($_GET['unit']) ? $_GET['unit'] : '';

// 3. Validasi apakah unit terdaftar di config.php
if (!array_key_exists($unit, $SCRIPT_URLS) || empty($SCRIPT_URLS[$unit])) {
    echo json_encode(['status' => 'error', 'message' => 'URL API untuk unit ini belum disetting.']);
    exit;
}

$targetUrl = $SCRIPT_URLS[$unit];

// Menangkap nama sheet dari JavaScript (tabel-motor.js). 
// Jika kosong (seperti dari jadwal-regreasing.js), gunakan default "REKAP_REGREASING"
$sheetName = isset($_GET['sheet']) ? $_GET['sheet'] : "REKAP_REGREASING";

// 4. Susun URL lengkap ke Google (URL + Token + Sheet)
$fullUrl = $targetUrl . "?token=" . $API_TOKEN . "&sheet=" . urlencode($sheetName);

// 5. Gunakan cURL untuk mengambil data dari Google
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fullUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Wajib diaktifkan karena Google Apps Script menggunakan redirect
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Mencegah error SSL di localhost/XAMPP

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// 6. Cetak hasil (kirim ke Frontend)
if ($error) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghubungi server Google: ' . $error]);
} else {
    echo $response;
}
?>