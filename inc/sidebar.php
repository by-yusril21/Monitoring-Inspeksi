<style>
  .main-sidebar-custom {
    background-color: #efefef !important;
    /* background-color: #ededed !important; */
    transition: all 0.3s ease;
    /* box-shadow: none !important; */
    overflow-y: hidden !important;
    height: 100vh;
    /* Memastikan tinggi sidebar setinggi layar */
  }

  /* Brand Logo area */
  .brand-link-custom {
    border-bottom: 2px solid rgb(220, 220, 220) !important;
    color: #000000 !important;
  }

  /* Teks menu di sidebar */
  .nav-sidebar .nav-link {
    color: #000000 !important;
    /* Warna abu-abu soft agar tidak terlalu kontras */
  }

  /* Efek Hover Menu (sedikit lebih terang dari Navy) */
  .nav-sidebar .nav-link:hover {
    background-color: rgb(187, 186, 186) !important;
    color: #156ada !important;
  }

  /* Warna menu saat aktif */
  .nav-sidebar .nav-link.active {
    background-color: #799dc4 !important;
    /* Biru cerah sebagai aksen */
    color: #141517 !important;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  }

  /* Sembunyikan Scrollbar */
  html,
  body {
    scrollbar-width: none;
    -ms-overflow-style: none;
    scroll-behavior: smooth;
  }

  body::-webkit-scrollbar {
    display: none;
  }
</style>

<?php
// Mengambil nama halaman dari URL, jika kosong default ke dashboard
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<aside class="main-sidebar main-sidebar-custom elevation-4">
  <a class="brand-link brand-link-custom">
    <span class="brand-text font-weight-light" style="font-size: 17px">
      <b>MONITORING INSPEKSI MOTOR</b>
    </span>
  </a>

  <div class="sidebar">

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <li class="nav-item">
          <a href="?page=dashboard" class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="" class="nav-link <?php echo ($current_page == 'halaman2') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-database"></i>
            <p>Halaman 2</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="?page=datasensor" class="nav-link <?php echo ($current_page == 'datasensor') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-database"></i>
            <p>Data</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="logout.php" class="nav-link">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>