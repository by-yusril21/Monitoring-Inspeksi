<?php
// 1. Keamanan & Database
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    echo "<script>window.location.href='index.php';</script>"; exit; 
}

require 'config/database.php';
require_once 'config/config.php';

// 2. Panggil Logika Penyimpanan Backend
// 2. Panggil Logika Penyimpanan Backend dari folder API
// __DIR__ . '/../' artinya: "Mundur satu langkah dari folder page, lalu masuk ke folder api"
require_once __DIR__ . '/../api/settings_process.php';

// 3. Tangkap Notifikasi
$pesan_notifikasi = "";
if (isset($_SESSION['flash_message'])) {
    $pesan_notifikasi = "
    <div class='alert alert-success alert-dismissible fade show shadow-sm' role='alert'>
        <i class='fas fa-check-circle mr-2'></i> <strong>Berhasil!</strong> " . $_SESSION['flash_message'] . "
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            <span aria-hidden='true'>&times;</span>
        </button>
    </div>";
    unset($_SESSION['flash_message']); 
}

// 4. Ambil Data Settings (Akan otomatis terbaca oleh semua file tab yang di-include)
$query_get = "SELECT * FROM settings";
$result = mysqli_query($conn, $query_get);
$konfigurasi = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $konfigurasi[$row['setting_key']] = $row;
    }
}
?>

<style>
    input[type="color"].form-control { height: 38px; padding: 3px; cursor: pointer; }
    .custom-control-label { cursor: pointer; }
    .accordion .card-header button:hover { text-decoration: none; background-color: #f8f9fa; }
    .motor-link:hover { text-decoration: underline !important; }
</style>

<div class="content-wrapper">
    <section class="content pt-4">
        <div class="container-fluid">
            <div class="row"><div class="col-md-12"><?php echo $pesan_notifikasi; ?></div></div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card shadow-sm sticky-top" style="border-radius: 10px; top: 70px; z-index: 100;">
                        <div class="card-header border-0 pb-0">
                            <h3 class="card-title font-weight-bold">Kategori Pengaturan</h3>
                        </div>
                        <div class="card-body p-2">
                            <ul class="nav nav-pills flex-column" id="settings-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-chart" data-toggle="pill" href="#content-chart"><i class="fas fa-chart-pie mr-2"></i> Parameter Grafik</a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a class="nav-link" id="tab-api" data-toggle="pill" href="#content-api"><i class="fas fa-link mr-2"></i> Koneksi Spreadsheet</a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a class="nav-link" id="tab-motor" data-toggle="pill" href="#content-motor"><i class="fas fa-cogs mr-2"></i> Master Data Motor</a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a class="nav-link" id="tab-filter" data-toggle="pill" href="#content-filter"><i class="fas fa-filter mr-2"></i> Filter Jadwal Regreasing</a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a class="nav-link" id="tab-inspeksi" data-toggle="pill" href="#content-inspeksi"><i class="fas fa-clipboard-check mr-2"></i> Form Inspeksi</a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a class="nav-link" id="tab-pdf" data-toggle="pill" href="#content-pdf"><i class="fas fa-file-pdf mr-2"></i> Pengaturan PDF</a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a class="nav-link" href="index.php?page=pengaturan_email"><i class="fas fa-envelope-open-text mr-2"></i> Pengaturan Notifikasi Email</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="tab-content" id="settings-tabContent">
                        <?php include 'settings_tabs/tab_chart.php'; ?>
                        <?php include 'settings_tabs/tab_api.php'; ?>
                        <?php include 'settings_tabs/tab_motor.php'; ?>
                        <?php include 'settings_tabs/daftar_motor.php'; ?>
                        <?php include 'settings_tabs/tab_filter.php'; ?>
                        <?php include 'settings_tabs/tab_inspeksi.php'; ?>
                        <?php include 'settings_tabs/tab_pdf.php'; ?>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // Inisialisasi JS untuk File Input (Khusus PDF)
    document.addEventListener('DOMContentLoaded', () => {
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>