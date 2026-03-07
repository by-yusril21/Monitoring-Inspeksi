<?php
/* navbar.php — AdminLTE 3 + Bootstrap 4 native */

// 1. Tangkap nilai ?page= dari URL
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// 2. Buat variabel penanda untuk Dashboard & Chart
// Karena menggunakan index.php, kita cukup cek dari parameter $currentPage
$isDashboard = ($currentPage === 'dashboard');
$isChart = ($currentPage === 'chart');

// 3. Menentukan unit chart mana yang sedang aktif (Default: C6KV)
$activeUnit = isset($_GET['unit']) ? strtoupper($_GET['unit']) : 'C6KV';

// 4. Tangkap nama user yang sedang login dari Session
$username_login = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
?>

<nav class="main-header navbar navbar-expand navbar-white navbar-light sticky-top px-0 py-0"
  style="box-shadow:0 2px 8px rgba(0,0,0,0.1); flex-wrap:wrap;">

  <div class="d-flex align-items-center w-100">

    <a class="nav-link px-3 flex-shrink-0" data-widget="pushmenu" href="#" role="button">
      <i class="fas fa-bars"></i>
    </a>

    <?php if ($isDashboard): ?>
      <div class="d-none d-md-flex align-items-center">
        <select id="pilihUnit" class="form-control form-control-sm mr-1" style="width:155px;">
          <option value="">-- Pilih Unit --</option>
          <option value="C6KV">PLTU UNIT C 6KV</option>
          <option value="C380">PLTU UNIT C 380</option>
          <option value="D6KV">PLTU UNIT D 6KV</option>
          <option value="D380">PLTU UNIT D 380</option>
          <option value="UTILITY">PLTU UNIT UTILITY</option>
        </select>
        <select id="pilihMotor" class="form-control form-control-sm mr-1" style="width: 230px;" disabled>
          <option value="">-- Pilih Motor --</option>
        </select>
        <button type="button" id="btnRefresh" class="btn btn-primary btn-sm text-nowrap">
          <i class="fas fa-sync-alt mr-1"></i>Update
        </button>
      </div>

    <?php elseif ($isChart): ?>
      <div class="d-flex align-items-center"
        style="overflow-x: auto; white-space: nowrap; -ms-overflow-style: none; scrollbar-width: none;">
        <style>
          /* Menyembunyikan scrollbar untuk deretan button chart di Chrome/Safari */
          .chart-nav-buttons::-webkit-scrollbar {
            display: none;
          }
        </style>
        <div class="chart-nav-buttons d-flex align-items-center py-1">
          <a href="?page=chart&unit=C6KV"
            class="btn <?= ($activeUnit == 'C6KV') ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm mr-2 font-weight-bold shadow-sm">
            MOTOR 6kV UNIT C
          </a>
          <a href="?page=chart&unit=D6KV"
            class="btn <?= ($activeUnit == 'D6KV') ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm mr-2 font-weight-bold shadow-sm">
            MOTOR 6kV UNIT D
          </a>
          <a href="?page=chart&unit=C380"
            class="btn <?= ($activeUnit == 'C380') ? 'btn-info text-white' : 'btn-outline-info' ?> btn-sm mr-2 font-weight-bold shadow-sm">
            MOTOR 380V UNIT C
          </a>
          <a href="?page=chart&unit=D380"
            class="btn <?= ($activeUnit == 'D380') ? 'btn-info text-white' : 'btn-outline-info' ?> btn-sm mr-2 font-weight-bold shadow-sm">
            MOTOR 380V UNIT D
          </a>
          <a href="?page=chart&unit=UTILITY"
            class="btn <?= ($activeUnit == 'UTILITY') ? 'btn-secondary text-white' : 'btn-outline-secondary' ?> btn-sm mr-2 font-weight-bold shadow-sm">
            MOTOR 380V UTILITY
          </a>
        </div>
      </div>
    <?php endif; ?>

    <ul class="navbar-nav d-flex flex-row align-items-center ml-auto mb-0">

      <?php if ($isDashboard): ?>
        <li class="nav-item">
          <a href="javascript:void(0)" class="nav-link nav-menu-link text-primary px-2" data-target-id="section-tabel"
            onclick="scrollToSection('section-tabel')">
            <i class="fas fa-table"></i>
            <span class="d-none d-md-inline ml-1">Tabel Data</span>
            <span class="d-inline d-md-none ml-1">Tabel</span>
          </a>
        </li>

        <li class="nav-item">
          <a href="javascript:void(0)" class="nav-link nav-menu-link text-dark px-2" data-target-id="section-gauge"
            onclick="scrollToSection('section-gauge')">
            <i class="fas fa-tachometer-alt"></i>
            <span class="d-none d-md-inline ml-1">Gauge Data</span>
            <span class="d-inline d-md-none ml-1">Gauge</span>
          </a>
        </li>

        <li class="nav-item">
          <a href="javascript:void(0)" class="nav-link nav-menu-link text-dark px-2" data-target-id="section-input"
            onclick="scrollToSection('section-input')">
            <i class="fas fa-edit"></i>
            <span class="d-none d-md-inline ml-1">Input Data</span>
            <span class="d-inline d-md-none ml-1">Input</span>
          </a>
        </li>

        <li class="nav-item d-md-none border-right pr-2 mr-2">
          <a class="nav-link px-2" data-toggle="collapse" href="#filterCollapse" role="button">
            <i class="fas fa-filter text-primary"></i>
          </a>
        </li>
      <?php endif; ?>

      <li class="nav-item dropdown pr-3">
        <a class="nav-link px-2 d-flex align-items-center" data-toggle="dropdown" href="#" style="cursor: pointer;">
          <span class="d-none d-md-inline font-weight-bold text-dark mr-2">
            <?= htmlspecialchars($username_login) ?>
          </span>
          <i class="fas fa-user-circle fa-lg text-primary"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow animated border-0 mt-2">
          <span class="dropdown-item dropdown-header border-bottom">
            <strong>
              <?= htmlspecialchars($username_login) ?>
            </strong>
          </span>
          <a href="logout.php" class="dropdown-item text-danger py-2">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </li>

    </ul>
  </div>

  <?php if ($isDashboard): ?>
    <div class="collapse w-100 d-md-none" id="filterCollapse">
      <div class="px-3 py-2 border-top border-primary bg-light w-100">
        <select id="pilihUnitMobile" class="form-control form-control-sm mb-2 w-100">
          <option value="">-- Pilih Unit --</option>
          <option value="C6KV">PLTU UNIT C 6KV</option>
          <option value="C380">PLTU UNIT C 380</option>
          <option value="D6KV">PLTU UNIT D 6KV</option>
          <option value="D380">PLTU UNIT D 380</option>
          <option value="UTILITY">PLTU UNIT UTILITY</option>
        </select>
        <select id="pilihMotorMobile" class="form-control form-control-sm mb-2 w-100" disabled>
          <option value="">-- Pilih Motor --</option>
        </select>
        <button type="button" id="btnRefreshMobile" class="btn btn-primary btn-sm btn-block">
          <i class="fas fa-sync-alt mr-1"></i>
        </button>
      </div>
    </div>
  <?php endif; ?>

</nav>