<style>
  /* 1. Sidebar Container Utama */
  .main-sidebar-custom {
    background-color: #efefef !important;
    transition: all 0.3s ease;
    
    /* FITUR FIXED: Mengunci sidebar agar tidak bergerak */
    position: fixed !important;
    top: 0;
    bottom: 0;
    left: 0;
    height: 100vh !important;
    overflow-y: auto !important; /* Menu tetap bisa di-scroll jika kepanjangan */
    z-index: 1038;
  }

  /* Sembunyikan scrollbar sidebar agar tetap bersih */
  .main-sidebar-custom::-webkit-scrollbar {
    width: 0px;
    background: transparent;
  }

  /* 2. Brand Logo Area */
  .brand-link-custom {
    border-bottom: 2px solid rgb(220, 220, 220) !important;
    color: #000000 !important;
    display: flex;
    align-items: center;
    height: 45px; /* Sesuaikan dengan tinggi navbar tipis kita sebelumnya */
  }

  /* 3. Styling Menu Navigasi */
  .nav-sidebar .nav-link {
    color: #000000 !important;
    font-size: 14px;
    margin-bottom: 2px;
  }

  /* Efek Hover */
  .nav-sidebar .nav-link:hover {
    background-color: rgba(0, 0, 0, 0.05) !important;
    color: #156ada !important;
  }

  /* Warna Menu Aktif */
  .nav-sidebar .nav-link.active {
    background-color: #799dc4 !important;
    color: #ffffff !important; /* Putih agar lebih kontras di atas biru */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  /* 4. Global Body Scrollbar (Sembunyikan scrollbar utama) */
  html, body {
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
    <span class="brand-text font-weight-bold" style="font-size: 14px; padding-left: 10px;">
      MONITORING INSPEKSI MOTOR
    </span>
  </a>

  <div class="sidebar">
    <nav class="mt-3">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <li class="nav-item">
          <a href="?page=dashboard" class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="?page=termometer" class="nav-link <?php echo ($current_page == 'termometer') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-thermometer-half"></i>
            <p>Termometer</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="?page=datasensor" class="nav-link <?php echo ($current_page == 'datasensor') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-database"></i>
            <p>Data Sensor</p>
          </a>
        </li>

        <li class="nav-header" style="padding: 10px 1rem 5px; color: #888; font-size: 10px;">AKUN</li>

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