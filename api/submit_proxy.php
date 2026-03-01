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
// Kita ambil token dari config.php
$payloadForGoogle = [
    "token" => $API_TOKEN,
    "targetSheet" => $data['targetSheet'],
    "maintenanceType" => $data['maintenanceType'],
    "email" => $data['email'],
    "sectionNo" => $data['sectionNo'],
    "actions" => $data['actions'],
    "vibrasi" => $data['vibrasi'],
    "tempDE" => $data['tempDE'],
    "tempNDE" => $data['tempNDE'],
    "suhuRuang" => $data['suhuRuang'],
    "beban" => $data['beban'],
    "damper" => $data['damper'],
    "amper" => $data['amper'],
    "bunyi" => $data['bunyi'],
    "panel" => $data['panel'],
    "kelengkapan" => $data['kelengkapan'],
    "kebersihan" => $data['kebersihan'],
    "grounding" => $data['grounding'],
    "regreasing" => $data['regreasing']
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
    // Karena Google Apps Script menggunakan redirect, kadang response bodynya kosong
    // Kita anggap berhasil jika HTTP code 200 atau 302
    echo json_encode(["status" => "success", "message" => "Data berhasil dikirim."]);
} else {
    echo json_encode(["status" => "error", "message" => "Server merespon dengan kode: " . $httpCode]);
}
?>