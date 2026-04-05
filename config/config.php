<?php
// File: config.php
// Pastikan file ini tidak bisa diakses langsung oleh publik

// 1. Sertakan koneksi database jika belum ada
require_once __DIR__ . '/database.php';

// 2. Set nilai default (fallback) jika database bermasalah/kosong
$API_TOKEN = "SemenTonasa2026";
$SCRIPT_URLS = [
    "C6KV" => "",
    "C380" => "",
    "D6KV" => "",
    "D380" => "",
    "UTILITY" => ""
];

// 3. Ambil konfigurasi API dari tabel settings
if (isset($conn)) {
    $query_api = "SELECT setting_key, setting_value FROM settings WHERE setting_key = 'api_token' OR setting_key LIKE 'script_url_%'";
    $result_api = mysqli_query($conn, $query_api);

    if ($result_api) {
        while ($row = mysqli_fetch_assoc($result_api)) {
            $key = $row['setting_key'];
            $val = $row['setting_value'];

            if ($key == 'api_token') {
                $API_TOKEN = $val;
            } else if ($key == 'script_url_c6kv') {
                $SCRIPT_URLS['C6KV'] = $val;
            } else if ($key == 'script_url_c380') {
                $SCRIPT_URLS['C380'] = $val;
            } else if ($key == 'script_url_d6kv') {
                $SCRIPT_URLS['D6KV'] = $val;
            } else if ($key == 'script_url_d380') {
                $SCRIPT_URLS['D380'] = $val;
            } else if ($key == 'script_url_utility') {
                $SCRIPT_URLS['UTILITY'] = $val;
            }
        }
    }
}
?>