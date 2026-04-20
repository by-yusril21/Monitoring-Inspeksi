<?php
// Pastikan session dan header adminLTE Anda sudah dipanggil di file induk/routing Anda
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>
    /* 1. MENGECILKAN UKURAN FONT HALAMAN */
    .qr-wrapper {
        font-size: 0.875rem;
        /* Mengecilkan teks dasar menjadi sekitar 14px */
    }

    .qr-wrapper .form-control,
    .qr-wrapper .input-group-text,
    .qr-wrapper .btn {
        font-size: 0.875rem;
        /* Mengecilkan teks pada input dan tombol */
    }

    .qr-wrapper .card-title {
        font-size: 1.05rem !important;
        /* Mengecilkan judul kotak card */
    }

    .content-header h1 {
        font-size: 1.5rem !important;
        /* Mengecilkan judul utama halaman */
    }

    /* 2. MEMAKSA PREVIEW QR CODE TETAP RAPI */
    #qrcode-box {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #qrcode-box img,
    #qrcode-box canvas {
        max-width: 100% !important;
        max-height: 220px !important;
        /* Sedikit dikecilkan menyesuaikan font */
        width: auto !important;
        height: auto !important;
    }
</style>

<div class="content-wrapper qr-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-6">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header bg-white">
                            <h3 class="card-title font-weight-bold">Pengaturan QR Code</h3>
                        </div>
                        <div class="card-body">

                            <div class="form-group">
                                <label for="input_link">1. Masukkan Link / URL <span
                                        class="text-danger">*</span></label>
                                <textarea id="input_link" class="form-control" rows="2"
                                    placeholder="Contoh: https://ptsementonasa.com/form-absensi"></textarea>
                            </div>

                            <div class="form-group mt-3">
                                <label for="input_level">2. Tingkat Kepadatan Titik (Error Correction)</label>
                                <select id="input_level" class="form-control">
                                    <option value="L">Level L (Low - 7%) : Titik Sangat Renggang (Awet Dicetak)</option>
                                    <option value="M" selected>Level M (Medium - 15%) : Standar Optimal (Disarankan)
                                    </option>
                                    <option value="Q">Level Q (Quartile - 25%) : Cukup Padat (Tahan Goresan)</option>
                                    <option value="H">Level H (High - 30%) : Sangat Padat (Anti Rusak / Layar Digital)
                                    </option>
                                </select>
                            </div>

                            <div class="form-group mt-3">
                                <label for="input_nama_file">3. Nama File Barcode</label>
                                <div class="input-group">
                                    <input type="text" id="input_nama_file" class="form-control"
                                        placeholder="Contoh: BOILER FEED WATER PUMP A">
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold">.png</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer bg-light text-right">
                            <button type="button" class="btn btn-default mr-2" onclick="resetForm()">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                            <button type="button" class="btn btn-primary" onclick="generateQR()">
                                <i class="fas fa-magic mr-1"></i> Buat QR Code
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header bg-white">
                            <h3 class="card-title font-weight-bold">Hasil Preview</h3>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center justify-content-center"
                            style="min-height: 290px; background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 5px; margin: 15px;">

                            <div id="qrcode-box" class="p-3 bg-white shadow-sm d-none" style="border-radius: 10px;">
                            </div>

                            <div id="pesan-kosong" class="text-muted text-center">
                                <i class="fas fa-qrcode fa-3x mb-2 opacity-50"></i>
                                <h6>Belum ada QR Code</h6>
                                <p class="mb-0 text-sm">Silakan atur parameter dan klik tombol buat.</p>
                            </div>

                        </div>

                        <div class="card-footer bg-light text-center">
                            <button type="button" id="btn-download" class="btn btn-success d-none font-weight-bold"
                                onclick="downloadQR()">
                                <i class="fas fa-download mr-2"></i> Simpan Kualitas HD
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<script>
    let qrCodeObj = null;

    // FUNGSI 1: MEMBUAT QR CODE
    function generateQR() {
        const linkInput = document.getElementById("input_link").value.trim();
        const levelInput = document.getElementById("input_level").value;
        const qrBox = document.getElementById("qrcode-box");
        const pesanKosong = document.getElementById("pesan-kosong");
        const btnDownload = document.getElementById("btn-download");

        if (!linkInput) {
            if (typeof toastr !== 'undefined') toastr.warning("Harap masukkan link atau teks terlebih dahulu!", "Perhatian");
            else alert("Harap masukkan link atau teks terlebih dahulu!");
            document.getElementById("input_link").focus();
            return;
        }

        let selectedLevel = QRCode.CorrectLevel.M;
        if (levelInput === 'L') selectedLevel = QRCode.CorrectLevel.L;
        if (levelInput === 'Q') selectedLevel = QRCode.CorrectLevel.Q;
        if (levelInput === 'H') selectedLevel = QRCode.CorrectLevel.H;

        qrBox.innerHTML = "";
        pesanKosong.classList.add("d-none");
        qrBox.classList.remove("d-none");
        btnDownload.classList.remove("d-none");

        qrCodeObj = new QRCode(qrBox, {
            text: linkInput,
            width: 1024,
            height: 1024,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: selectedLevel
        });

        if (typeof toastr !== 'undefined') toastr.info(`QR Code HD (Level ${levelInput}) berhasil dibuat.`, "Info");
    }

    // FUNGSI 2: MENDOWNLOAD GAMBAR KE FOLDER UNDUHAN
    function downloadQR() {
        const qrBox = document.getElementById("qrcode-box");

        let canvas = qrBox.querySelector("canvas");
        let img = qrBox.querySelector("img");
        let imageSrc = null;

        if (canvas) {
            imageSrc = canvas.toDataURL("image/png");
        } else if (img) {
            imageSrc = img.src;
        }

        if (imageSrc) {
            let namaFile = document.getElementById("input_nama_file").value.trim();
            if (!namaFile) namaFile = "QRCode_HD_Generator";
            if (!namaFile.toLowerCase().endsWith(".png")) namaFile += ".png";

            const downloadLink = document.createElement("a");
            downloadLink.href = imageSrc;
            downloadLink.download = namaFile;

            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);

            if (typeof toastr !== 'undefined') {
                toastr.success("Barcode HD berhasil disimpan:<br><b>" + namaFile + "</b>", "Berhasil!");
            } else {
                alert("SUKSES! Barcode HD berhasil disimpan:\n" + namaFile);
            }
        } else {
            if (typeof toastr !== 'undefined') toastr.error("Gagal mengambil gambar QR Code.", "Kesalahan System");
            else alert("Gagal mengambil gambar QR Code.");
        }
    }

    // FUNGSI 3: RESET FORM
    function resetForm() {
        document.getElementById("input_link").value = "";
        document.getElementById("input_nama_file").value = "";
        document.getElementById("input_level").value = "M";

        document.getElementById("qrcode-box").innerHTML = "";
        document.getElementById("qrcode-box").classList.add("d-none");

        document.getElementById("pesan-kosong").classList.remove("d-none");
        document.getElementById("btn-download").classList.add("d-none");

        if (typeof toastr !== 'undefined') toastr.info("Formulir telah di-reset.", "Reset");
    }
</script>