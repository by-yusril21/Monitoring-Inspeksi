<?php
// =========================================================
// PENGATURAN SESSION
// =========================================================
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
session_start();

if (isset($_SESSION['username'])) {
  header("Location: index.php");
  exit;
}

include "config/database.php";
$message = "";

if (isset($_POST['username'])) {
  $username = trim($_POST['username']);
  $password = $_POST['password'];

  $stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE username = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    if (password_verify($password, $data['password'])) {
      $_SESSION['username'] = $username;
      $_SESSION['fullname'] = $data['fullname'];
      $_SESSION['role'] = $data['role'];
      header("Location: index.php");
      exit;
    } else {
      $message = "<div class='alert-msg text-danger mb-3' style='font-size: 0.9rem; font-weight: 500;'><i class='fas fa-exclamation-circle'></i> Kata sandi salah</div>";
    }
  } else {
    $message = "<div class='alert-msg text-danger mb-3' style='font-size: 0.9rem; font-weight: 500;'><i class='fas fa-exclamation-circle'></i> Nama akun tidak terdaftar</div>";
  }
  mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Electrical Elins Maintenance</title>

  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,500,600,700&display=fallback">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">

  <style>
    body {
      background-image: url('assets/img/bg-login.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
      position: relative;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      font-family: 'Source Sans Pro', sans-serif;
      z-index: 1;
      overflow: hidden;
    }

    body::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.55);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      z-index: -1;
    }

    /* KOTAK UTAMA DIPERKECIL */
    .split-container {
      display: flex;
      width: 100%;
      max-width: 900px;
      /* Diperkecil dari 1050px */
      height: 550px;
      /* Diperkecil dari 620px */
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
      overflow: hidden;
      margin: 20px;
      animation: scaleUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
      opacity: 0;
      transform: scale(0.95);
    }

    .img-side {
      flex: 1.2;
      background-image: url('assets/img/bg-login.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      position: relative;
    }

    .img-side::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.15);
    }

    /* PADDING DI FORM DIPERKECIL */
    .form-side {
      flex: 1;
      padding: 40px 50px;
      /* Diperkecil dari 50px 70px */
      display: flex;
      flex-direction: column;
      justify-content: center;
      background-color: #ffffff;
    }

    .brand-header-wrapper {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 35px;
      animation: fadeIn 0.8s ease-out 0.2s forwards;
      opacity: 0;
    }

    .brand-logo-img {
      height: 65px;
      /* Logo disesuaikan sedikit */
      width: auto;
      border-radius: 50%;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      flex-shrink: 0;
      transition: transform 0.3s ease;
    }

    .brand-logo-img:hover {
      transform: scale(1.05);
    }

    .brand-text-container {
      text-align: left;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .brand-subtitle {
      font-size: 0.8rem;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 3px;
      font-weight: 600;
    }

    .brand-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: #1a1a1a;
      margin: 0;
      line-height: 1.2;
      letter-spacing: -0.3px;
    }

    .login-form-area {
      animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.4s forwards;
      opacity: 0;
      transform: translateY(20px);
    }

    .custom-input {
      border: 1.5px solid #e2e8f0;
      border-radius: 8px;
      padding: 12px 16px;
      font-size: 0.95rem;
      width: 100%;
      color: #333;
      transition: all 0.3s ease;
    }

    .custom-input::placeholder {
      color: #a0aec0;
    }

    .custom-input:focus {
      border-color: #111111;
      box-shadow: 0 0 0 4px rgba(17, 17, 17, 0.08);
      outline: none;
      transform: translateY(-1px);
    }

    .custom-checkbox-wrapper {
      display: flex;
      align-items: center;
      font-size: 0.9rem;
      color: #555;
      margin-top: 10px;
      margin-bottom: 25px;
    }

    .custom-checkbox-wrapper input[type="checkbox"] {
      margin-right: 10px;
      cursor: pointer;
      width: 16px;
      height: 16px;
      accent-color: #111111;
      transition: all 0.2s;
    }

    .btn-black {
      background-color: #111111;
      color: #ffffff;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 1rem;
      width: 100%;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-black:hover {
      background-color: #2b2b2b;
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }

    .btn-black:active {
      transform: translateY(0);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .forgot-link {
      display: inline-block;
      margin-top: 15px;
      font-size: 0.85rem;
      color: #555;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s, transform 0.2s;
    }

    .forgot-link:hover {
      color: #111;
      transform: translateX(3px);
    }

    .login-footer {
      text-align: center;
      margin-top: auto;
      padding-top: 20px;
      font-size: 0.75rem;
      color: #a0aec0;
      font-weight: 500;
      animation: fadeIn 1s ease-out 0.6s forwards;
      opacity: 0;
    }

    @keyframes scaleUp {
      from {
        opacity: 0;
        transform: scale(0.95);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    @keyframes slideUpFade {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 768px) {
      .split-container {
        flex-direction: column;
        height: auto;
        min-height: 90vh;
        border-radius: 12px;
      }

      .img-side {
        min-height: 200px;
        flex: none;
      }

      .form-side {
        padding: 30px;
      }

      .brand-header-wrapper {
        justify-content: center;
      }

      .brand-text-container {
        text-align: center;
      }
    }
  </style>
</head>

<body>

  <div class="split-container">
    <div class="img-side"></div>

    <div class="form-side">
      <div class="brand-header-wrapper">
        <img src="assets/img/logo.jpg" alt="Logo" class="brand-logo-img">
        <div class="brand-text-container">
          <div class="brand-subtitle">PT Semen Tonasa</div>
          <h1 class="brand-title">Electrical of power plant<br>elins maintenance</h1>
        </div>
      </div>

      <div class="login-form-area">
        <?php if (!empty($message))
          echo $message; ?>

        <form action="" method="post">
          <div class="form-group mb-3">
            <input type="text" class="custom-input" name="username" placeholder="Nama akun..." required
              autocomplete="off">
          </div>

          <div class="form-group mb-1">
            <input type="password" class="custom-input" id="password-field" name="password" placeholder="Kata sandi..."
              required>
          </div>

          <div class="custom-checkbox-wrapper">
            <input type="checkbox" id="show-pass" onclick="togglePassword()">
            <label for="show-pass" style="margin: 0; font-weight: 500; cursor: pointer; user-select: none;">Tampilkan
              kata sandi</label>
          </div>

          <button type="submit" class="btn-black">Masuk</button>

          <div>
            <a href="#" class="forgot-link">Ubah kata sandi?</a>
          </div>
        </form>
      </div>

      <div class="login-footer">
        Copyright &copy; Elins Maintenance <?php echo date("Y"); ?>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      var x = document.getElementById("password-field");
      if (x.type === "password") {
        x.type = "text";
      } else {
        x.type = "password";
      }
    }
  </script>

</body>

</html>