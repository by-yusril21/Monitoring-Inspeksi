<style>
  /* =========================================
     CSS KHUSUS NAVBAR & JUDUL
     ========================================= */

  /* Menghilangkan garis bawah dan shadow bawaan navbar AdminLTE */
  .main-header {
    border-bottom: none !important;
    box-shadow: none !important;
  }

  /* Styling Judul PILIH UNIT */
  #label-title {
    font-size: 0.9rem !important;
    font-weight: 900 !important;
    color: #000000 !important;
    text-transform: uppercase;
    letter-spacing: 1px;
    line-height: 1.2;
    display: inline-block;
    text-shadow:
      2px 2px 4px rgba(0, 0, 0, 0.2),
      0px 0px 1px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
  }

  /* Animasi saat judul berubah (Highlight) */
  .dynamic-title {
    animation: highlightFade 0.5s ease-out;
  }

  @keyframes highlightFade {
    0% {
      color: #007bff;
      transform: scale(1.05);
    }

    100% {
      color: #333;
      transform: scale(1);
    }
  }

  /* Animasi meluncur dari bawah saat ganti Unit/Motor */
  .title-animate {
    animation: slideFadeShadow 0.6s cubic-bezier(0.23, 1, 0.32, 1) both;
  }

  @keyframes slideFadeShadow {
    0% {
      opacity: 0;
      transform: translateY(15px);
      filter: blur(3px);
      text-shadow: none;
    }

    100% {
      opacity: 1;
      transform: translateY(0);
      filter: blur(0);
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }
  }
</style>

<nav class="main-header navbar navbar-expand-md navbar-light navbar-white text-sm py-1 sticky-top">

  <ul class="navbar-nav align-items-center">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars text-dark"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <span id="label-title" class="nav-link" style="cursor: default;">PILIH UNIT</span>
    </li>
  </ul>

  <span id="label-title" class="navbar-brand d-sm-none ml-2">PILIH UNIT</span>

  <button class="navbar-toggler ml-auto border-0" type="button" data-toggle="collapse" data-target="#navbarCollapse"
    aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarCollapse">
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a href="javascript:void(0)" class="nav-link font-weight-bold text-primary nav-menu-link"
          data-target-id="section-tabel" onclick="scrollToSection('section-tabel')">
          <i class="fas fa-table mr-1"></i> TABEL DATA
        </a>
      </li>
      <li class="nav-item">
        <a href="javascript:void(0)" class="nav-link font-weight-bold text-dark nav-menu-link"
          data-target-id="section-gauge"
          onclick="document.getElementById('section-gauge').scrollIntoView({behavior: 'smooth'});">
          <i class="fas fa-tachometer-alt mr-1"></i> GAUGE DATA
        </a>
      </li>
      <li class="nav-item">
        <a href="javascript:void(0)" class="nav-link font-weight-bold text-dark nav-menu-link"
          data-target-id="section-input"
          onclick="document.getElementById('section-input').scrollIntoView({behavior: 'smooth'});">
          <i class="fas fa-edit mr-1"></i> INPUT DATA
        </a>
      </li>
      <li class="nav-item d-none d-md-block">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt text-dark"></i>
        </a>
      </li>
    </ul>
  </div>
</nav>

<script>
  // Fungsi khusus untuk scroll dengan jarak (Hanya dipakai TABEL DATA)
  function scrollToSection(id) {
    const element = document.getElementById(id);
    if (element) {
      const offset = 60; // Ganti angka ini jika jarak atas tabel masih kurang pas
      const elementPosition = element.getBoundingClientRect().top + window.scrollY;
      const offsetPosition = elementPosition - offset;

      window.scrollTo({
        top: offsetPosition,
        behavior: "smooth"
      });
    }
  }

  // Script deteksi warna saat scroll (ScrollSpy)
  document.addEventListener("DOMContentLoaded", function () {
    const navMenuLinks = document.querySelectorAll('.nav-menu-link');

    window.addEventListener('scroll', () => {
      let currentSection = '';
      const sections = ['section-tabel', 'section-gauge', 'section-input'];

      sections.forEach(id => {
        const sectionElement = document.getElementById(id);
        if (sectionElement) {
          const sectionTop = sectionElement.offsetTop - 70;
          if (window.scrollY >= sectionTop) {
            currentSection = id;
          }
        }
      });

      if (window.scrollY < 50) {
        currentSection = 'section-tabel';
      }

      navMenuLinks.forEach(link => {
        link.classList.remove('text-primary');
        link.classList.add('text-dark');

        if (link.getAttribute('data-target-id') === currentSection) {
          link.classList.remove('text-dark');
          link.classList.add('text-primary');
        }
      });
    });
  });
</script>