<?php
// Pastikan file ini dibaca sebagai JavaScript oleh browser
header("Content-Type: application/javascript");

// Panggil koneksi database Anda
require 'database.php';

// Siapkan kerangka default jika database kosong
$dataMotor = [
  'C6KV' => [],
  'C380' => [],
  'D6KV' => [],
  'D380' => [],
  'UTILITY' => []
];

// Ambil data dari database
$query = "SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'motor_list_%'";
$result = mysqli_query($conn, $query);

if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    // Teks dari database masih tergabung dengan \n (Enter)
    // Kita pecah menjadi array menggunakan fungsi explode()
    // Fungsi array_filter dan array_map digunakan untuk membuang baris yang kosong/spasi berlebih
    $list_array = array_filter(array_map('trim', explode("\n", $row['setting_value'])));

    // Memasukkan array ke dalam kategori yang tepat
    if ($row['setting_key'] == 'motor_list_c6kv')
      $dataMotor['C6KV'] = array_values($list_array);
    if ($row['setting_key'] == 'motor_list_c380')
      $dataMotor['C380'] = array_values($list_array);
    if ($row['setting_key'] == 'motor_list_d6kv')
      $dataMotor['D6KV'] = array_values($list_array);
    if ($row['setting_key'] == 'motor_list_d380')
      $dataMotor['D380'] = array_values($list_array);
    if ($row['setting_key'] == 'motor_list_utility')
      $dataMotor['UTILITY'] = array_values($list_array);
  }
}

// Konversi Array PHP menjadi Format JSON JavaScript
$json_data = json_encode($dataMotor, JSON_PRETTY_PRINT);

// Cetak langsung sebagai variabel JavaScript (Persis seperti format config.js Anda sebelumnya)
echo "window.dataMotor = " . $json_data . ";";
?>