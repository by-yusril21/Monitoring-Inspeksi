<?php
// Mengambil nama halaman dari URL, jika kosong default ke home
$current_page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">

  <a href="?page=home" class="brand-link">
    <img src="assets/img/logo.jpg" alt="Logo" class="brand-image img-circle elevation-3"
      style="opacity: .8; background-color: white;">

    <span class="brand-text font-weight-bold" style="letter-spacing: 0.5px; font-size: 16px;">Electrical
      Powerplant</span>
  </a>

  <div class="sidebar">
    <nav class="mt-3">
      <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent nav-flat" data-widget="treeview" role="menu"
        data-accordion="false">

        <li class="nav-item">
          <a href="?page=home" class="nav-link <?php echo ($current_page == 'home') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Home</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="?page=chart" class="nav-link <?php echo ($current_page == 'chart') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>Chart</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="?page=jadwal-regreasing"
            class="nav-link <?php echo ($current_page == 'jadwal-regreasing') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Jadwal Regreasing</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="index.php?page=data_terbaru"
            class="nav-link <?= ($current_page == 'data_terbaru') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-table"></i>
            <p>Rekap Data Terbaru</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="index.php?page=buat_qrcode" class="nav-link <?= ($current_page == 'buat_qrcode') ? 'active' : '' ?>">
            <i class="nav-icon fas fa-qrcode"></i>
            <p>Generator QR Code</p>
          </a>
        </li>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <li class="nav-item">
            <a href="?page=user" class="nav-link <?php echo ($current_page == 'user') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-users"></i>
              <p>Tambah Pengguna</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="?page=settings" class="nav-link <?php echo ($current_page == 'settings') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-cogs"></i>
              <p>Settings</p>
            </a>
          </li>
        <?php endif; ?>

        <li class="nav-header" style="font-size: 11px; font-weight: bold; color: #adb5bd; margin-top: 10px;">AKUN
          PENGGUNA</li>

        <li class="nav-item">
          <a href="logout.php" class="nav-link text-danger">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>

<script>
  // Menambahkan class 'sidebar-collapse' ke tag <body> saat halaman dimuat
  document.addEventListener("DOMContentLoaded", function () {
    document.body.classList.add('sidebar-collapse');
  });
</script>