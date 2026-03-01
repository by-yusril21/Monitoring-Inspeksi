<?php /* navbar.php — AdminLTE 3 + Bootstrap 4 native */ ?>



<nav class="main-header navbar navbar-expand navbar-white navbar-light sticky-top px-0 py-0"
  style="box-shadow:0 2px 8px rgba(0,0,0,0.1); flex-wrap:wrap;">

  <!-- ── Baris 1: Hamburger | Menu | Ikon Filter ── -->
  <div class="d-flex align-items-center w-100">

    <!-- Hamburger -->
    <a class="nav-link px-3 flex-shrink-0" data-widget="pushmenu" href="#" role="button">
      <i class="fas fa-bars"></i>
    </a>

    <!-- Filter Unit & Motor — desktop (md ke atas) -->
    <div class="d-none d-md-flex align-items-center">
      <select id="pilihUnit" class="form-control form-control-sm mr-1" style="width:155px;">
        <option value="">-- Pilih Unit --</option>
        <option value="C6KV">PLTU UNIT C 6KV</option>
        <option value="C380">PLTU UNIT C 380</option>
        <option value="D6KV">PLTU UNIT D 6KV</option>
        <option value="D380">PLTU UNIT D 380</option>
        <option value="UTILITY">PLTU UNIT UTILITY</option>
      </select>
      <select id="pilihMotor" class="form-control form-control-sm mr-1" style="width: 270px;" disabled>
        <option value="">-- Pilih Motor --</option>
      </select>
      <button type="button" id="btnRefresh" class="btn btn-primary btn-sm text-nowrap">
        <i class="fas fa-sync-alt mr-1"></i>Update
      </button>
    </div>

    <!-- Menu navigasi — selalu jejer ke samping -->
    <ul class="navbar-nav d-flex flex-row align-items-center ml-auto mb-0">

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

      <!-- Fullscreen — desktop saja -->
      <li class="nav-item d-none d-md-block">
        <a class="nav-link px-2" data-widget="fullscreen" href="#" role="button" title="Fullscreen">
          <i class="fas fa-expand-arrows-alt text-secondary"></i>
        </a>
      </li>

      <!-- Tombol buka/tutup filter — mobile saja -->
      <li class="nav-item d-md-none">
        <a class="nav-link px-2" data-toggle="collapse" href="#filterCollapse" role="button" aria-expanded="false"
          aria-controls="filterCollapse">
          <i class="fas fa-filter text-primary"></i>
        </a>
      </li>

    </ul>
  </div>
  <!-- /Baris 1 -->

  <!-- ── Baris 2: Filter collapse full width — mobile saja ── -->
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
        <i class="fas fa-sync-alt mr-1"></i>Update
      </button>
    </div>
  </div>
  <!-- /Baris 2 -->

</nav>