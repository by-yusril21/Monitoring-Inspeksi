<?php
// =========================================================
// PENGATURAN SESSION 30 HARI (2.592.000 detik)
// Harus dieksekusi sebelum session_start()
// =========================================================
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);

session_start();

// Jika pengguna sudah login sebelumnya, langsung lempar ke index (tidak perlu login lagi)
if (isset($_SESSION['username'])) {
  header("Location: index.php");
  exit;
}

include "config/database.php";

$message = "Masukkan Username Dan Password";

if (isset($_POST['username'])) {
  // Bersihkan input untuk mencegah error karakter aneh
  $username = trim($_POST['username']);
  $password = $_POST['password'];

  // =========================================================
  // PERBAIKAN KEAMANAN: Menggunakan Prepared Statement (Anti SQL Injection)
  // =========================================================
  $stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE username = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  // Cek apakah data user ditemukan
  if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);

    // Verifikasi Password
    if (password_verify($password, $data['password'])) {
      // Jika sukses, buat session
      $_SESSION['username'] = $username;
      $_SESSION['fullname'] = $data['fullname'];
      $_SESSION['role'] = $data['role'];

      // Pindahkan ke halaman index menggunakan PHP murni (Lebih cepat dan aman dari JS)
      header("Location: index.php");
      exit;
    } else {
      $message = "<b style='color:red'>Password Salah</b>";
    }
  } else {
    $message = "<b style='color:red'>Username Tidak Terdaftar</b>";
  }

  // Tutup statement
  mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">

</html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Electrical_Maintenance</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <b>Monitoring</b> Inspeksi Motor
    </div>
    <!-- /.login-logo -->
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg"> <?php echo $message ?></p>

        <form action="" method="post">
          <div class="input-group mb-3">
            <input type="text" class="form-control" name="username" placeholder="Username" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-users"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <!-- /.col -->
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </div>
            <!-- /.col -->
          </div>
        </form>
      </div>
      <!-- /.login-card-body -->
    </div>
  </div>
  <!-- /.login-box -->

  <!-- jQuery -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.min.js"></script>
</body>

</html>