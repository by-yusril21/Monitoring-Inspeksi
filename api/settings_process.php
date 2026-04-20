<?php
// --- PROSES SIMPAN DATA JIKA TOMBOL DITEKAN (POST) ---
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

            if ($error === 0 && $size > 0 && strpos($type, 'image/') === 0) {
                $data_gambar = file_get_contents($tmp_name);
                $base64 = base64_encode($data_gambar);
                $logo_base64 = 'data:' . $type . ';base64,' . $base64;
                mysqli_query($conn, "UPDATE settings SET setting_value = '$logo_base64' WHERE setting_key = 'pdf_logo_base64'");
            }
        }
        unset($_POST['form_update_pdf'], $_POST['pdf_judul_1'], $_POST['pdf_judul_2']);
    }

    // --- JIKA FORM INSPEKSI (LINK GFORM) DISUBMIT ---
    if (isset($_POST['form_update_inspeksi'])) {
        $form_links = [];

        // Pastikan array key dikirim dari form
        if (isset($_POST['gform_keys'])) {
            foreach ($_POST['gform_keys'] as $unit => $motorArray) {
                foreach ($motorArray as $index => $motorName) {
                    $motorNameClean = trim($motorName);

                    if (!empty($motorNameClean)) {
                        // Ambil nilai edit dan user, gunakan null coalescing (??) untuk menghindari error undefined index
                        $link_edit = isset($_POST['gform_edit_vals'][$unit][$index]) ? trim($_POST['gform_edit_vals'][$unit][$index]) : '';
                        $link_user = isset($_POST['gform_user_vals'][$unit][$index]) ? trim($_POST['gform_user_vals'][$unit][$index]) : '';

                        // Simpan ke array multi-dimensi
                        $form_links[$unit][$motorNameClean] = [
                            'edit' => $link_edit,
                            'user' => $link_user
                        ];
                    }
                }
            }
        }

        // Ubah array ke format JSON untuk disimpan di database
        $json_links = mysqli_real_escape_string($conn, json_encode($form_links));

        $cek = mysqli_query($conn, "SELECT 1 FROM settings WHERE setting_key = 'form_links_json'");
        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($conn, "UPDATE settings SET setting_value = '$json_links' WHERE setting_key = 'form_links_json'");
        } else {
            mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value, setting_name, description) VALUES ('form_links_json', '$json_links', 'Link Google Form', 'Data link Google Form untuk masing-masing motor')");
        }

        // Unset variabel agar tidak diproses oleh update general di bawah
        unset($_POST['form_update_inspeksi'], $_POST['gform_keys'], $_POST['gform_edit_vals'], $_POST['gform_user_vals']);
    }

    // --- JIKA FORM CHART DISUBMIT ---
    if (isset($_POST['form_update_chart'])) {
        if (!isset($_POST['chart_hidden_parameters']))
            $_POST['chart_hidden_parameters'] = '';
        unset($_POST['form_update_chart']);
    }

    // --- JIKA FORM FILTER REGREASING DISUBMIT ---
    if (isset($_POST['form_update_regreasing'])) {
        if (!isset($_POST['regreasing_filter_c6kv']))
            $_POST['regreasing_filter_c6kv'] = '';
        if (!isset($_POST['regreasing_filter_c380']))
            $_POST['regreasing_filter_c380'] = '';
        if (!isset($_POST['regreasing_filter_d6kv']))
            $_POST['regreasing_filter_d6kv'] = '';
        if (!isset($_POST['regreasing_filter_d380']))
            $_POST['regreasing_filter_d380'] = '';
        if (!isset($_POST['regreasing_filter_utility6kv']))
            $_POST['regreasing_filter_utility6kv'] = '';
        if (!isset($_POST['regreasing_filter_utility380']))
            $_POST['regreasing_filter_utility380'] = '';
        unset($_POST['form_update_regreasing']);
    }

    // --- UPDATE GENERAL UNTUK SISA FORM LAINNYA ---
    foreach ($_POST as $key => $value) {
        if (is_array($value))
            $value = implode(',', $value);
        $key_clean = mysqli_real_escape_string($conn, $key);
        $val_clean = mysqli_real_escape_string($conn, $value);
        mysqli_query($conn, "UPDATE settings SET setting_value = '$val_clean' WHERE setting_key = '$key_clean'");
    }

    if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == 1) {
        echo "success";
        exit; // <- Ini mencegah browser mengulang muat (reload) layar
    }

    // --- Redirect dengan Pesan Sukses ---
    $_SESSION['flash_message'] = "Pengaturan telah diperbarui dan disimpan ke database.";
    echo "<script>window.location.href='index.php?page=settings';</script>";
    exit;
}
?>