/* =======================================================
   navbar.js — AdminLTE 3 Responsive Navbar
   ======================================================= */

/* ── scrollToSection ── */
function scrollToSection(id) {
  const el = document.getElementById(id);
  if (!el) return;
  window.scrollTo({
    top     : el.getBoundingClientRect().top + window.scrollY - 56,
    behavior: "smooth"
  });
}

/* ── populateMotor: isi kedua pasang select Motor ── */
function populateMotor(unit, selectedMotor) {
  const opts = (unit && window.dataMotor && window.dataMotor[unit])
    ? window.dataMotor[unit] : [];

  ["#pilihMotor", "#pilihMotorMobile"].forEach(function (sel) {
    const $el = $(sel);
    $el.empty().append('<option value="">-- Pilih Motor --</option>');
    if (opts.length) {
      $el.prop("disabled", false);
      opts.forEach(function (name) {
        $el.append(
          `<option value="${name}" ${selectedMotor === name ? "selected" : ""}>${name}</option>`
        );
      });
    } else {
      $el.prop("disabled", true);
    }
  });
}

/* ── loadData: ambil data dari unit+motor aktif ── */
function loadData() {
  const unit  = $("#pilihUnit").val() || $("#pilihUnitMobile").val();
  const motor = $("#pilihMotor").val() || $("#pilihMotorMobile").val();
  if (unit && motor && typeof window.loadDataFromSheet === "function") {
    window.loadDataFromSheet(unit, motor);
  }
}

/* ── clearTable ── */
function clearTable() {
  if ($.fn.DataTable && $.fn.DataTable.isDataTable("#example1")) {
    $("#example1").DataTable().clear().draw();
  }
}

/* ── bindFilterEvents ── */
function bindFilterEvents() {

  $("#pilihUnit,#pilihUnitMobile,#pilihMotor,#pilihMotorMobile")
    .off("change.navbar");
  $(document).off("click.navbar", "#btnRefresh,#btnRefreshMobile");

  /* Pilih Unit Desktop */
  $("#pilihUnit").on("change.navbar", function () {
    const val = $(this).val();
    $("#pilihUnitMobile").val(val);
    populateMotor(val);
    localStorage.setItem("mon_selectedUnit", val);
    localStorage.removeItem("mon_selectedMotor");
    clearTable();
  });

  /* Pilih Unit Mobile */
  $("#pilihUnitMobile").on("change.navbar", function () {
    const val = $(this).val();
    $("#pilihUnit").val(val);
    populateMotor(val);
    localStorage.setItem("mon_selectedUnit", val);
    localStorage.removeItem("mon_selectedMotor");
    clearTable();
  });

  /* Pilih Motor Desktop */
  $("#pilihMotor").on("change.navbar", function () {
    const motor = $(this).val();
    $("#pilihMotorMobile").val(motor);
    localStorage.setItem("mon_selectedMotor", motor);
    loadData();
  });

  /* Pilih Motor Mobile */
  $("#pilihMotorMobile").on("change.navbar", function () {
    const motor = $(this).val();
    $("#pilihMotor").val(motor);
    localStorage.setItem("mon_selectedMotor", motor);
    loadData();
  });

  /* Tombol Update (desktop & mobile) */
  $(document).on("click.navbar", "#btnRefresh,#btnRefreshMobile", function (e) {
    e.preventDefault();
    e.stopPropagation();
    const unit  = $("#pilihUnit").val() || $("#pilihUnitMobile").val();
    const motor = $("#pilihMotor").val() || $("#pilihMotorMobile").val();
    if (unit && motor) {
      if (typeof window.loadDataFromSheet === "function") {
        window.loadDataFromSheet(unit, motor);
      }
    } else {
      toastr.info("Pilih Unit dan Motor terlebih dahulu.");
    }
    /* Tutup collapse filter setelah klik Update di mobile */
    $("#filterCollapse").collapse("hide");
  });

  /* Restore localStorage */
  const savedUnit  = localStorage.getItem("mon_selectedUnit");
  const savedMotor = localStorage.getItem("mon_selectedMotor");
  if (savedUnit) {
    $("#pilihUnit,#pilihUnitMobile").val(savedUnit);
    populateMotor(savedUnit, savedMotor);
    if (savedMotor) loadData();
  }
}

/* =======================================================
   DOM READY
   ======================================================= */
$(document).ready(function () {

  /* ScrollSpy */
  const navLinks = document.querySelectorAll(".nav-menu-link");

  function runScrollSpy() {
    const ids = ["section-tabel", "section-gauge", "section-input"];
    let current = "section-tabel";
    ids.forEach(function (id) {
      const el = document.getElementById(id);
      if (el && window.scrollY >= el.offsetTop - 70) current = id;
    });
    navLinks.forEach(function (link) {
      const isActive = link.getAttribute("data-target-id") === current;
      link.classList.toggle("text-primary", isActive);
      link.classList.toggle("text-dark", !isActive);
    });
  }

  window.addEventListener("scroll", runScrollSpy);
  runScrollSpy();

  /* Init filter */
  function tryBind() {
    if (document.getElementById("pilihUnit")) {
      bindFilterEvents();
      return true;
    }
    return false;
  }

  if (!tryBind()) {
    const obs = new MutationObserver(function (m, o) {
      if (tryBind()) o.disconnect();
    });
    obs.observe(document.getElementById("example1_wrapper") || document.body,
      { childList: true, subtree: true });
    setTimeout(function () { obs.disconnect(); tryBind(); }, 1500);
  }

});