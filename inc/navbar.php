<style>
  /* 1. Global: Sembunyikan Scrollbar & Smooth Scroll */
  html,
  body {
    -ms-overflow-style: none;
    scrollbar-width: none;
    scroll-behavior: smooth;
  }

  body::-webkit-scrollbar {
    display: none;
  }

  /* 2. Navbar Custom */
  .navbar-custom {
    background-color: #efefef !important;
  }

  /* 3. Button Styling (Background Transparan & Teks Putih) */
  .btn-nav-custom {
    background: transparent !important;
    border: none !important;
    color: #162330 !important;
    font-weight: 600;
    font-size: 13px;
    padding: 8px 12px;
    cursor: pointer;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    outline: none !important;
  }

  .btn-nav-custom:hover {
    background-color: rgb(219, 219, 219) !important;
    color: #4aabff !important;
    border-radius: 4px;
  }

  /* Animasi Teks Judul (Opsional) */
  @keyframes slideFadeIn {
    0% {
      opacity: 0;
      transform: translateY(-10px);
    }

    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .dynamic-title {
    font-family: 'Source Sans Pro', sans-serif;
    font-weight: 700;
    font-size: 1.25rem;
    color: #d0d0d0;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    text-transform: uppercase;
    animation: slideFadeIn 0.5s ease-out;
  }
</style>

<nav class="main-header navbar navbar-expand navbar-dark navbar-custom border-bottom-0">

  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="color : #000000">
        <i class="fas fa-bars"></i>
      </a>
    </li>

    <li class="nav-item">
      <button type="button" class="btn-nav-custom" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="fas fa-table mr-2"></i> TABEL DATA
      </button>
    </li>

    <li class="nav-item">
      <button type="button" class="btn-nav-custom"
        onclick="document.getElementById('section-gauge').scrollIntoView({behavior: 'smooth'});">
        <i class="fas fa-tachometer-alt mr-2"></i> GAUGE DATA
      </button>
    </li>

    <li class="nav-item">
      <button type="button" class="btn-nav-custom"
        onclick="document.getElementById('section-input').scrollIntoView({behavior: 'smooth'});">
        <i class="fas fa-edit mr-2"></i> INPUT DATA
      </button>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
      <a class="nav-link" data-widget="fullscreen" href="#" role="button">
        <i class="fas fa-expand-arrows-alt" style="color : #162330"></i>
      </a>
    </li>
  </ul>

</nav>