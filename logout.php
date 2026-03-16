<?php
// 1. Panggil session-nya dulu agar PHP tahu kotak mana yang mau dihancurkan
session_start();

// 2. Kosongkan semua isi variabel session
session_unset();

// 3. Hancurkan file session-nya di server
session_destroy();

// 4. Pindahkan halaman ke login.php menggunakan PHP murni (Lebih cepat dari JavaScript)
header("Location: login.php");
exit; // Pastikan script berhenti berjalan setelah redirect
?>