<?php
// 1. CEK KEAMANAN: Pastikan user sudah login
// Karena dipanggil di index.php, kita langsung cek sesinya saja
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    echo "
        <div class='container mt-5'>
            <div class='alert alert-danger text-center shadow-sm'>
                <h4><i class='fas fa-shield-alt'></i> Akses Ditolak!</h4>
                <p>Anda tidak memiliki izin (belum login) untuk membuka form ini.</p>
                <a href='index.php' class='btn btn-primary mt-2'>Kembali ke Dashboard</a>
            </div>
        </div>
    ";
    // Hentikan eksekusi kode di bawahnya
    exit;
}

// 2. Tangkap link samaran dari URL
$url_samaran = isset($_GET['target']) ? $_GET['target'] : '';

if (empty($url_samaran)) {
    echo "<div class='alert alert-warning text-center mt-5'>Data form tidak ditemukan.</div>";
    exit;
}

// 3. Buka penyamaran Base64
$link_asli = base64_decode($url_samaran);

// 4. Validasi apakah benar-benar link
if (strpos($link_asli, 'http') === 0) {

    // 5. REDIRECT MENGGUNAKAN JAVASCRIPT (Anti-Error "Headers Already Sent")
    // Layar akan putih sekian milidetik, lalu langsung melompat ke Google Form
    echo "
        <div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
            <p>Membuka Form Aman... Mohon Tunggu.</p>
        </div>
        <script>
            window.location.replace('$link_asli');
        </script>
    ";
    exit;

} else {
    echo "<div class='alert alert-danger text-center mt-5'>Format link form rusak.</div>";
    exit;
}
?>