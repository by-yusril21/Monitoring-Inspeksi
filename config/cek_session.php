<?php
// File: config/cek_session.php

// =========================================================
// PENGATURAN SESSION 30 HARI (2.592.000 detik)
// Harus dieksekusi sebelum session_start()
// =========================================================
ini_set('session.gc_maxlifetime', 2592000); // Tahan di server 30 hari
session_set_cookie_params(2592000);         // Tahan di browser 30 hari

// 1. Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. CEK KEAMANAN
if (!isset($_SESSION['username'])) {
    header('HTTP/1.0 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Login dulu bos."]);
    exit;
}

// 3. SOLUSI KECEPATAN: LEPAS KUNCI SESSION
session_write_close();
?>