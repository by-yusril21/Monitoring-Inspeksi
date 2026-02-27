// --- Bagian Pengambilan Data Action Terakhir ---

function updateLastAction() {
  // 1. Ambil semua baris data dari body tabel utama
  // Asumsi: tabel utama Anda memiliki id="example1" atau selector yang sesuai
  var rows = document.querySelectorAll("#example1 tbody tr");

  if (rows.length > 0) {
    // 2. Ambil baris terakhir (data terbaru)
    var lastRow = rows[rows.length - 1];

    // 3. Ambil data dari kolom (index kolom dimulai dari 0)
    // Jika Action di kolom 18, maka index-nya adalah 17
    // Jika Tanggal ada di kolom tertentu (misal kolom 1), sesuaikan index-nya
    var actionText = lastRow.cells[18].innerText;
    var tanggalText = lastRow.cells[1].innerText; // Sesuaikan index kolom tanggal Anda

    // 4. Update ke elemen HTML Dashboard
    var teksActionElement = document.getElementById("teks-action");
    var tanggalActionElement = document.getElementById("tanggal-action");

    if (teksActionElement) {
      teksActionElement.innerText = actionText || "Tidak ada catatan tindakan.";
    }

    if (tanggalActionElement) {
      tanggalActionElement.innerText = tanggalText || "--/--/----";
    }
  }
}

// Panggil fungsi ini di dalam document.ready atau setelah data tabel berhasil di-load
$(document).ready(function () {
  // Jika Anda menggunakan DataTables, gunakan event 'draw.dt' agar
  // data action selalu update saat tabel difilter atau di-sort
  $("#example1").on("draw.dt", function () {
    updateLastAction();
  });

  // Panggilan awal
  updateLastAction();
});
