<?php
// 1. Mulai session jika belum ada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Cek Keamanan: Apakah dia BELUM LOGIN atau BUKAN ADMIN?
if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    // Karena dipanggil di tengah halaman, kita gunakan JS Redirect
    echo "<script>window.location.href='index.php';</script>";
    exit; 
}

// Sesuaikan path ke file koneksi database Anda
require 'config/database.php';

// --- 1. PROSES SIMPAN DATA JIKA TOMBOL DITEKAN (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- JIKA FORM PENGATURAN PDF DISUBMIT ---
    if (isset($_POST['form_update_pdf'])) {
        $judul_1 = mysqli_real_escape_string($conn, $_POST['pdf_judul_1']);
        $judul_2 = mysqli_real_escape_string($conn, $_POST['pdf_judul_2']);
        
        mysqli_query($conn, "UPDATE settings SET setting_value = '$judul_1' WHERE setting_key = 'pdf_judul_1'");
        mysqli_query($conn, "UPDATE settings SET setting_value = '$judul_2' WHERE setting_key = 'pdf_judul_2'");
        
        // Proses Upload Logo Gambar -> Base64
        if (isset($_FILES['logo_baru']['name']) && $_FILES['logo_baru']['name'] != '') {
            $tmp_name = $_FILES['logo_baru']['tmp_name'];
            $type = $_FILES['logo_baru']['type'];
            $size = $_FILES['logo_baru']['size'];
            $error = $_FILES['logo_baru']['error'];

            if ($error === 0 && $size > 0) {
                if (strpos($type, 'image/') === 0) {
                    $data_gambar = file_get_contents($tmp_name);
                    $base64 = base64_encode($data_gambar);
                    $logo_base64 = 'data:' . $type . ';base64,' . $base64;
                    
                    mysqli_query($conn, "UPDATE settings SET setting_value = '$logo_base64' WHERE setting_key = 'pdf_logo_base64'");
                }
            }
        }

        unset($_POST['form_update_pdf']); 
        unset($_POST['pdf_judul_1']);
        unset($_POST['pdf_judul_2']);
    }

    // Jika Form Chart yang disubmit
    if (isset($_POST['form_update_chart'])) {
        if (!isset($_POST['chart_hidden_parameters'])) {
            $_POST['chart_hidden_parameters'] = '';
        }
        unset($_POST['form_update_chart']); 
    }

    // Jika Form Filter Regreasing yang disubmit
    if (isset($_POST['form_update_regreasing'])) {
        if (!isset($_POST['regreasing_filter_c6kv'])) $_POST['regreasing_filter_c6kv'] = '';
        if (!isset($_POST['regreasing_filter_c380'])) $_POST['regreasing_filter_c380'] = '';
        if (!isset($_POST['regreasing_filter_d6kv'])) $_POST['regreasing_filter_d6kv'] = '';
        if (!isset($_POST['regreasing_filter_d380'])) $_POST['regreasing_filter_d380'] = '';
        unset($_POST['form_update_regreasing']); 
    }

    // Looping semua input form yang dikirim (General Update)
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        $key_clean = mysqli_real_escape_string($conn, $key);
        $val_clean = mysqli_real_escape_string($conn, $value);

        // Update nilai ke tabel settings
        $query_update = "UPDATE settings SET setting_value = '$val_clean' WHERE setting_key = '$key_clean'";
        mysqli_query($conn, $query_update);
    }

    // --- SOLUSI PRG dengan JS: Simpan pesan sukses di Session, lalu Redirect ---
    $_SESSION['flash_message'] = "Pengaturan telah diperbarui dan disimpan ke database.";
    
    // Gunakan JS Redirect agar tidak bentrok dengan sidebar.php
    echo "<script>window.location.href='index.php?page=settings';</script>";
    exit; 
}

// --- 2. CEK PESAN NOTIFIKASI (GET) ---
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

// --- 3. AMBIL DATA SAAT INI DARI DATABASE ---
$query_get = "SELECT * FROM settings";
$result = mysqli_query($conn, $query_get);
$konfigurasi = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $konfigurasi[$row['setting_key']] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Setting Konfigurasi SCADA</title>
    <style>
        input[type="color"].form-control {
            height: 38px;
            padding: 3px;
            cursor: pointer;
        }
        /* Memperbaiki jarak antar checkbox */
        .custom-control-label { cursor: pointer; }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0 text-dark"><i class="fas fa-cogs mr-2 text-secondary"></i> Pengaturan Sistem</h1>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $pesan_notifikasi; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="card shadow-sm sticky-top" style="border-radius: 10px; top: 70px; z-index: 100;">
                                <div class="card-header border-0 pb-0">
                                    <h3 class="card-title font-weight-bold">Kategori Pengaturan</h3>
                                </div>
                                <div class="card-body p-2">
                                    <ul class="nav nav-pills flex-column" id="settings-tab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="tab-chart" data-toggle="pill" href="#content-chart" role="tab" aria-selected="true">
                                                <i class="fas fa-chart-pie mr-2"></i> Parameter Grafik
                                            </a>
                                        </li>
                                         <li class="nav-item mt-1">
                                            <a class="nav-link" id="tab-api" data-toggle="pill" href="#content-api" role="tab" aria-selected="false">
                                                <i class="fas fa-link mr-2"></i> Koneksi Spreadsheet
                                            </a>
                                        </li>
                                        <li class="nav-item mt-1">
                                            <a class="nav-link" id="tab-motor" data-toggle="pill" href="#content-motor" role="tab" aria-selected="false">
                                                <i class="fas fa-cogs mr-2"></i> Master Data Motor
                                            </a>
                                        </li>
                                        <li class="nav-item mt-1">
                                            <a class="nav-link" id="tab-filter-regreasing" data-toggle="pill" href="#content-filter-regreasing" role="tab">
                                                <i class="fas fa-filter mr-2"></i> Filter Jadwal Regreasing
                                            </a>
                                        </li>
                                        <li class="nav-item mt-1">
                                            <a class="nav-link" id="tab-inspeksi" data-toggle="pill" href="#content-inspeksi" role="tab" aria-selected="false">
                                                <i class="fas fa-clipboard-check mr-2"></i> Form Inspeksi
                                            </a>
                                        </li>
                                        <li class="nav-item mt-1">
                                            <a class="nav-link" id="tab-pdf" data-toggle="pill" href="#content-pdf" role="tab" aria-selected="false">
                                                <i class="fas fa-file-pdf mr-2"></i> Pengaturan PDF
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="tab-content" id="settings-tabContent">
                                
                                <div class="tab-pane fade show active" id="content-chart" role="tabpanel">
                                    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
                                        <div class="card-header border-0">
                                            <h3 class="card-title font-weight-bold">Konfigurasi Visual & Sistem Grafik</h3>
                                        </div>

                                        <form action="" method="POST">
                                            <input type="hidden" name="form_update_chart" value="1">
                                            
                                            <div class="card-body pt-0">
                                                <p class="text-muted text-sm mb-4 pb-2 border-bottom text-left">
                                                    Ubah parameter di bawah ini untuk menyesuaikan fungsionalitas Grafik. Centang data yang ingin Anda <b>sembunyikan</b> saat grafik pertama kali dimuat.
                                                </p>

                                                <?php 
                                                foreach ($konfigurasi as $key => $data) { 
                                                    if(strpos($key, 'chart_') !== false) {
                                                ?>
                                                    <div class="form-group mb-4 text-left">
                                                        <label for="<?php echo $key; ?>" class="font-weight-bold text-dark">
                                                            <?php echo $data['setting_name']; ?>
                                                        </label>
                                                        
                                                        <?php 
                                                        if ($key === 'chart_hidden_parameters') { 
                                                            // ===============================================
                                                            // ARRAY PARAMETER 16 DATA BARU
                                                            // ===============================================
                                                            $daftar_parameter = [
                                                                'DE_H'  => 'Vib. DE (H)',
                                                                'DE_V'  => 'Vib. DE (V)',
                                                                'DE_Ax' => 'Vib. DE (Ax)',
                                                                'DE_gE' => 'Vib. DE (gE)',
                                                                
                                                                'NDE_H'  => 'Vib. NDE (H)',
                                                                'NDE_V'  => 'Vib. NDE (V)',
                                                                'NDE_Ax' => 'Vib. NDE (Ax)',
                                                                'NDE_gE' => 'Vib. NDE (gE)',
                                                                
                                                                'TempDE'  => 'Temp DE',
                                                                'TempNDE' => 'Temp NDE',
                                                                'Suhu'    => 'Suhu Ruangan',
                                                                'Beban'   => 'Beban Gen.',
                                                                'Damper'  => 'Open Damper',
                                                                
                                                                'CurrR' => 'Arus (R)',
                                                                'CurrS' => 'Arus (S)',
                                                                'CurrT' => 'Arus (T)'
                                                            ];
                                                            
                                                            $hidden_array = array_map('trim', explode(',', $data['setting_value']));
                                                        ?>
                                                            <div class="row mt-2">
                                                                <?php foreach($daftar_parameter as $val => $label) { 
                                                                    $isChecked = in_array($val, $hidden_array) ? 'checked' : '';
                                                                ?>
                                                                <div class="col-md-3 col-sm-4 col-6 mb-2">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="chk_<?php echo $val; ?>" name="<?php echo $key; ?>[]" value="<?php echo $val; ?>" <?php echo $isChecked; ?>>
                                                                        <label class="custom-control-label font-weight-normal text-muted" for="chk_<?php echo $val; ?>">
                                                                            <?php echo $label; ?>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <?php } ?>
                                                            </div>
                                                        
                                                        <?php 
                                                        } else if (strpos($key, 'default_') !== false) { 
                                                        ?>
                                                            <select class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" required>
                                                                <option value="1" <?php echo ($data['setting_value'] == '1') ? 'selected' : ''; ?>>Aktif (Menyala / Ditampilkan)</option>
                                                                <option value="0" <?php echo ($data['setting_value'] == '0') ? 'selected' : ''; ?>>Mati (Nonaktif / Disembunyikan)</option>
                                                            </select>
                                                        <?php 
                                                        } else { 
                                                            $inputType = "text"; 
                                                            if (strpos($key, 'width') !== false || strpos($key, 'radius') !== false || strpos($key, 'step') !== false || strpos($key, 'visible') !== false || strpos($key, 'interval') !== false) {
                                                                $inputType = "number";
                                                            } else if (strpos($key, 'bg') !== false || strpos($key, 'color') !== false) {
                                                                $inputType = "color";
                                                            }
                                                        ?>
                                                            <input type="<?php echo $inputType; ?>" class="form-control" id="<?php echo $key; ?>"
                                                                name="<?php echo $key; ?>"
                                                                value="<?php echo htmlspecialchars($data['setting_value']); ?>"
                                                                required <?php echo ($inputType == 'number') ? 'step="0.1"' : ''; ?>>
                                                        <?php } ?>
                                                            
                                                        <small class="form-text text-muted mt-1">
                                                            <i class="fas fa-info-circle mr-1 text-info"></i>
                                                            <?php echo $data['description']; ?>
                                                        </small>
                                                    </div>
                                                <?php 
                                                    } 
                                                }  
                                                ?>

                                            </div>
                                            <div class="card-footer bg-light text-right" style="border-radius: 0 0 10px 10px;">
                                                <button type="reset" class="btn btn-default mr-2">
                                                    <i class="fas fa-undo mr-1"></i> Batal
                                                </button>
                                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                                    <i class="fas fa-save mr-1"></i> Simpan Konfigurasi Grafik
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="content-motor" role="tabpanel">
                                    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
                                        <div class="card-header border-0">
                                            <h3 class="card-title font-weight-bold">Master Data Peralatan Motor</h3>
                                        </div>
                                        <form action="" method="POST">
                                            <div class="card-body pt-0 text-left">
                                                <p class="text-muted text-sm mb-4 pb-2 border-bottom">
                                                    Kelola daftar motor yang akan muncul di dropdown halaman SCADA. <b>Pastikan setiap nama motor berada di baris baru (tekan Enter).</b>
                                                </p>
                                                
                                                <div class="row">
                                                <?php 
                                                foreach ($konfigurasi as $key => $data) { 
                                                    if(strpos($key, 'motor_list_') !== false) {
                                                ?>
                                                    <div class="col-md-6 mb-4 text-left">
                                                        <label for="<?php echo $key; ?>" class="font-weight-bold text-dark">
                                                            <i class="fas fa-list-ul text-primary mr-1"></i> <?php echo $data['setting_name']; ?>
                                                        </label>
                                                        
                                                        <textarea class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" rows="8" placeholder="Ketik nama motor di sini..."><?php echo htmlspecialchars($data['setting_value']); ?></textarea>
                                                            
                                                        <small class="form-text text-muted mt-1">
                                                            <?php echo $data['description']; ?>
                                                        </small>
                                                    </div>
                                                <?php 
                                                    } 
                                                } 
                                                ?>
                                                </div>

                                            </div>
                                            <div class="card-footer bg-light text-right" style="border-radius: 0 0 10px 10px;">
                                                <button type="reset" class="btn btn-default mr-2">
                                                    <i class="fas fa-undo mr-1"></i> Batal
                                                </button>
                                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                                    <i class="fas fa-save mr-1"></i> Simpan Daftar Motor
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="content-filter-regreasing" role="tabpanel">
                                    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
                                        <div class="card-header border-0">
                                            <h3 class="card-title font-weight-bold">Pilih Motor untuk Jadwal Regreasing</h3>
                                        </div>
                                        <form action="" method="POST">
                                            <input type="hidden" name="form_update_regreasing" value="1">

                                            <div class="card-body pt-0 text-left">
                                                <p class="text-muted text-sm mb-4 pb-2 border-bottom">
                                                    Berikut adalah daftar motor dari <b>Master List</b>. Centang motor yang ingin Anda munculkan di halaman Jadwal Regreasing.
                                                </p>

                                                <?php 
                                                $mapping_filter = [
                                                    'regreasing_filter_c6kv' => [
                                                        'master' => 'motor_list_c6kv', 
                                                        'judul'  => 'Filter Regreasing Motor 6kV PLTU Unit C'
                                                    ],
                                                    'regreasing_filter_c380' => [
                                                        'master' => 'motor_list_c380', 
                                                        'judul'  => 'Filter Regreasing Motor 380V PLTU Unit C'
                                                    ],
                                                    'regreasing_filter_d6kv' => [
                                                        'master' => 'motor_list_d6kv', 
                                                        'judul'  => 'Filter Regreasing Motor 6kV PLTU Unit D'
                                                    ],
                                                    'regreasing_filter_d380' => [
                                                        'master' => 'motor_list_d380', 
                                                        'judul'  => 'Filter Regreasing Motor 380V PLTU Unit D'
                                                    ]
                                                ];

                                                foreach ($mapping_filter as $filter_key => $data_map) {
                                                    $master_key = $data_map['master'];
                                                    $nama_display = $data_map['judul'];
                                                    
                                                    $master_raw = isset($konfigurasi[$master_key]) ? $konfigurasi[$master_key]['setting_value'] : '';
                                                    $master_array = array_filter(array_map('trim', explode("\n", $master_raw)));

                                                    $checked_raw = isset($konfigurasi[$filter_key]) ? $konfigurasi[$filter_key]['setting_value'] : '';
                                                    $checked_array = array_map('trim', explode(',', $checked_raw));
                                                ?>
                                                    <div class="mb-4">
                                                        <label class="font-weight-bold text-primary"><i class="fas fa-layer-group mr-1"></i> <?php echo $nama_display; ?></label>
                                                        
                                                        <div class="row bg-light p-3 border" style="border-radius: 8px; max-height: 200px; overflow-y: auto;">
                                                            <?php foreach($master_array as $m) { 
                                                                $isSel = in_array($m, $checked_array) ? 'checked' : '';
                                                                $unique_id = md5($filter_key . '_' . $m); 
                                                            ?>
                                                            <div class="col-md-4 mb-2">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="reg_<?php echo $unique_id; ?>" name="<?php echo $filter_key; ?>[]" value="<?php echo $m; ?>" <?php echo $isSel; ?>>
                                                                    <label class="custom-control-label font-weight-normal" for="reg_<?php echo $unique_id; ?>"><?php echo $m; ?></label>
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                <?php } ?>

                                            </div>
                                            <div class="card-footer bg-light text-right">
                                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                                    <i class="fas fa-save mr-1"></i> Update Filter Regreasing
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="content-inspeksi" role="tabpanel">
                                    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
                                        <div class="card-header border-0">
                                            <h3 class="card-title font-weight-bold">Konfigurasi Form Inspeksi</h3>
                                        </div>
                                        <div class="card-body text-left">
                                            <div class="alert alert-info bg-light border-info text-info"><i class="fas fa-info-circle mr-2"></i> Modul pengaturan form inspeksi masih dalam tahap pengembangan.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="content-api" role="tabpanel">
                                    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
                                        <div class="card-header border-0">
                                            <h3 class="card-title font-weight-bold">Integrasi Web App Google Apps Script</h3>
                                        </div>

                                        <form action="" method="POST">
                                            <div class="card-body pt-0">
                                                <p class="text-muted text-sm mb-4 pb-2 border-bottom text-left">
                                                    Atur token keamanan dan URL untuk masing-masing unit. Pastikan URL yang dimasukkan adalah hasil <b>Deploy as Web App</b> terbaru dari Google Apps Script.
                                                </p>

                                                <?php 
                                                foreach ($konfigurasi as $key => $data) { 
                                                    if($key === 'api_token' || strpos($key, 'script_url_') !== false) {
                                                        $inputType = (strpos($key, 'script_url_') !== false) ? 'url' : 'text';
                                                ?>
                                                    <div class="form-group mb-4 text-left">
                                                        <label for="<?php echo $key; ?>" class="font-weight-bold text-dark">
                                                            <?php echo $data['setting_name']; ?>
                                                        </label>
                                                        
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text bg-light">
                                                                    <i class="fas <?php echo ($key === 'api_token') ? 'fa-key text-warning' : 'fa-globe text-primary'; ?>"></i>
                                                                </span>
                                                            </div>
                                                            <input type="<?php echo $inputType; ?>" class="form-control" id="<?php echo $key; ?>"
                                                                name="<?php echo $key; ?>"
                                                                value="<?php echo htmlspecialchars($data['setting_value']); ?>"
                                                                placeholder="Masukkan <?php echo $data['setting_name']; ?>">
                                                        </div>
                                                            
                                                        <small class="form-text text-muted mt-1">
                                                            <i class="fas fa-info-circle mr-1 text-info"></i>
                                                            <?php echo $data['description']; ?>
                                                        </small>
                                                    </div>
                                                <?php 
                                                    } 
                                                } 
                                                ?>

                                            </div>
                                            <div class="card-footer bg-light text-right" style="border-radius: 0 0 10px 10px;">
                                                <button type="reset" class="btn btn-default mr-2">
                                                    <i class="fas fa-undo mr-1"></i> Batal
                                                </button>
                                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                                    <i class="fas fa-save mr-1"></i> Simpan Koneksi
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade" id="content-pdf" role="tabpanel">
                                    <div class="card card-outline card-danger shadow-sm" style="border-radius: 10px;">
                                        <div class="card-header border-0">
                                            <h3 class="card-title font-weight-bold">Pengaturan Kop Surat PDF</h3>
                                        </div>

                                        <form action="" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="form_update_pdf" value="1">
                                            
                                            <div class="card-body pt-0 text-left">
                                                <p class="text-muted text-sm mb-4 pb-2 border-bottom">
                                                    Sesuaikan teks judul dan logo yang akan ditampilkan saat mengunduh data tabel sebagai dokumen PDF.
                                                </p>

                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-dark">Judul Baris 1 (Utama)</label>
                                                    <input type="text" name="pdf_judul_1" class="form-control" value="<?php echo htmlspecialchars($konfigurasi['pdf_judul_1']['setting_value'] ?? ''); ?>" required>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-dark">Judul Baris 2 (Sub Judul / Nama Perusahaan)</label>
                                                    <input type="text" name="pdf_judul_2" class="form-control" value="<?php echo htmlspecialchars($konfigurasi['pdf_judul_2']['setting_value'] ?? ''); ?>" required>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-dark">Logo Watermark PDF (Format: PNG/JPG transparan)</label>
                                                    
                                                    <div class="mb-3 p-3 text-center" style="background-color: #f8f9fa; border: 1px dashed #ccc; border-radius: 5px;">
                                                        <?php if(!empty($konfigurasi['pdf_logo_base64']['setting_value'])): ?>
                                                            <img src="<?php echo $konfigurasi['pdf_logo_base64']['setting_value']; ?>" alt="Logo Preview" style="max-height: 100px;">
                                                            <div class="mt-2 text-success" style="font-size: 12px;"><i class="fas fa-check-circle"></i> Logo Aktif Terpasang</div>
                                                        <?php else: ?>
                                                            <div class="text-muted"><i class="fas fa-image fa-2x mb-2 text-black-50"></i><br>Belum ada logo terpasang.</div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="logoUpload" name="logo_baru" accept="image/png, image/jpeg">
                                                        <label class="custom-file-label" for="logoUpload">Pilih file logo baru...</label>
                                                    </div>
                                                    <small class="text-muted mt-2 d-block">Biarkan kosong jika tidak ingin mengubah logo saat ini.</small>
                                                </div>

                                            </div>
                                            <div class="card-footer bg-light text-right" style="border-radius: 0 0 10px 10px;">
                                                <button type="submit" class="btn btn-danger px-4 shadow-sm">
                                                    <i class="fas fa-save mr-1"></i> Simpan Pengaturan PDF
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>

    </div>
    
    <script>
        $(document).ready(function () {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
</body>
</html>