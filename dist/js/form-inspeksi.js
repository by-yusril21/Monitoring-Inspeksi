/* File: dist/js/form-inspeksi.js */

$(document).ready(function () {
  const gformLinks = window.DataGFormGlobal || {};

  // 1. KITA BUAT FUNGSI KHUSUS AGAR BISA DIPANGGIL KAPAN SAJA
  function checkAndShowLinks() {
    let unitRaw = $("#pilihUnit").val();
    let unitKey = unitRaw ? unitRaw.toLowerCase().trim() : "";
    let motorName = $("#pilihMotor").val() ? $("#pilihMotor").val().trim() : "";

    // Reset tombol
    $("#btnLinkUser, #btnLinkEdit").addClass("d-none").attr("href", "#");

    // Pencocokan Data
    if (gformLinks && gformLinks[unitKey]) {
      let motorData = gformLinks[unitKey][motorName];

      if (motorData) {
        // Terapkan Keamanan Level 3 (Base64 + Routing)
        if (motorData.user && motorData.user !== "") {
          let linkAmanUser = "?page=buka_form&target=" + btoa(motorData.user);
          $("#btnLinkUser").attr("href", linkAmanUser).removeClass("d-none");
        }

        if (motorData.edit && motorData.edit !== "") {
          let linkAmanEdit = "?page=buka_form&target=" + btoa(motorData.edit);
          $("#btnLinkEdit").attr("href", linkAmanEdit).removeClass("d-none");
        }
      }
    }
  }

  // 2. Jalankan fungsi saat dropdown motor diganti secara manual oleh user
  $(document).on("change", "#pilihMotor", function () {
    checkAndShowLinks();
  });

  // 3. Sembunyikan tombol saat dropdown unit diganti
  // (karena biasanya pilihan motor akan ikut tereset)
  $(document).on("change", "#pilihUnit", function () {
    $("#btnLinkUser, #btnLinkEdit").addClass("d-none").attr("href", "#");
  });

  // 4. KUNCI PERBAIKAN: Jalankan fungsi secara otomatis saat halaman di-refresh
  // Kita gunakan setTimeout (500 milidetik) untuk memberi waktu jika
  // dropdown motor Anda diisi menggunakan proses AJAX oleh script lain.
  setTimeout(function () {
    // Cek apakah dropdown motor tidak kosong saat halaman baru dimuat
    if ($("#pilihMotor").val() && $("#pilihMotor").val() !== "") {
      checkAndShowLinks();
    }
  }, 500);
});
