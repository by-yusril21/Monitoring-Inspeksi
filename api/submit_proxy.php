<?php
// File: api/submit_proxy.php
header('Content-Type: application/json');

// 1. Panggil file konfigurasi rahasia
require_once '../config/config.php';

// 2. Baca data payload (JSON) yang dikirim oleh JavaScript
$inputData = file_get_contents("php://input");
$data = json_decode($inputData, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Format data tidak valid."]);
    exit;
}

// 3. Validasi Unit
$unit = isset($data['unit']) ? $data['unit'] : '';

if (!array_key_exists($unit, $SCRIPT_URLS) || empty($SCRIPT_URLS[$unit])) {
    echo json_encode(["status" => "error", "message" => "URL API untuk unit ini belum disetting."]);
    exit;
}

// 4. Susun Payload untuk Google Apps Script
// Menggunakan isset() agar jika ada data yang kosong, PHP tidak mengeluarkan pesan Error/Warning
$payloadForGoogle = [
    "token" => isset($API_TOKEN) ? $API_TOKEN : '',
    "targetSheet" => isset($data['targetSheet']) ? $data['targetSheet'] : '',
    "maintenanceType" => isset($data['maintenanceType']) ? $data['maintenanceType'] : '',
    "email" => isset($data['email']) ? $data['email'] : '',
    "sectionNo" => isset($data['sectionNo']) ? $data['sectionNo'] : '-',
    "actions" => isset($data['actions']) ? $data['actions'] : '-',

    // [DIPERBAIKI] Vibrasi sudah dipecah menjadi dua (DE & NDE)
    "vibrasiDE" => isset($data['vibrasiDE']) ? $data['vibrasiDE'] : '-',
    "vibrasiNDE" => isset($data['vibrasiNDE']) ? $data['vibrasiNDE'] : '-',

    "tempDE" => isset($data['tempDE']) ? $data['tempDE'] : '-',
    "tempNDE" => isset($data['tempNDE']) ? $data['tempNDE'] : '-',
    "suhuRuang" => isset($data['suhuRuang']) ? $data['suhuRuang'] : '-',
    "beban" => isset($data['beban']) ? $data['beban'] : '-',
    "damper" => isset($data['damper']) ? $data['damper'] : '-',
    "amper" => isset($data['amper']) ? $data['amper'] : '-',
    "bunyi" => isset($data['bunyi']) ? $data['bunyi'] : '-',
    "panel" => isset($data['panel']) ? $data['panel'] : '-',
    "kelengkapan" => isset($data['kelengkapan']) ? $data['kelengkapan'] : '-',
    "kebersihan" => isset($data['kebersihan']) ? $data['kebersihan'] : '-',
    "grounding" => isset($data['grounding']) ? $data['grounding'] : '-',
    "regreasing" => isset($data['regreasing']) ? $data['regreasing'] : '-'
];

$targetUrl = $SCRIPT_URLS[$unit];

// 5. Kirim data ke Google Apps Script via cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadForGoogle));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 6. Tangani hasil respon
if ($error) {
    echo json_encode(["status" => "error", "message" => "Gagal menghubungi server Google: " . $error]);
} else if ($httpCode == 200 || $httpCode == 302) {
    echo json_encode(["status" => "success", "message" => "Data berhasil dikirim."]);
} else {
    echo json_encode(["status" => "error", "message" => "Server merespon dengan kode: " . $httpCode]);
}
?>