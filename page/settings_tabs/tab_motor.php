<style>
    /* Transisi untuk efek highlight kotak motor saat dicari */
    .motor-wrapper {
        transition: background-color 0.5s ease, box-shadow 0.5s ease;
        border-radius: 8px;
        padding: 10px;
        border: 1px solid transparent;
    }

    .highlight-box {
        background-color: #fff3cd !important;
        border-color: #ffeeba;
        box-shadow: 0 0 15px rgba(255, 193, 7, 0.4);
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>

<div class="tab-pane fade" id="content-motor" role="tabpanel">
    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">Master Data Peralatan Motor</h3>
        </div>

        <form id="formMotor" action="" method="POST">
            <div class="card-body pt-0 text-left">
                <div class="mb-4 pb-3 border-bottom">
                    <p class="text-muted text-sm mb-3">
                        Kelola daftar motor yang akan muncul di dropdown halaman SCADA. <b>Pastikan setiap nama motor
                            berada di baris baru (tekan Enter).</b>
                    </p>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a class="btn btn-sm btn-outline-info" id="tab-daftar-motor" data-toggle="pill"
                            href="#content-daftar-motor">
                            <i class="fas fa-database mr-1"></i> Klik di sini untuk melihat daftar motor di Spreadsheet
                        </a>

                        <button type="button" class="btn btn-sm btn-primary font-weight-bold px-4 shadow-sm"
                            id="btn-save-master-motor" title="Simpan Seluruh Daftar Motor">
                            SIMPAN
                        </button>
                    </div>

                    <div class="position-relative">
                        <div class="input-group shadow-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i
                                        class="fas fa-search text-primary"></i></span>
                            </div>
                            <input type="text" id="searchMasterMotor" class="form-control"
                                placeholder="Cari nama motor di dalam master data...">
                        </div>
                        <ul id="searchResultsMaster" class="list-group position-absolute w-100 shadow"
                            style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;"></ul>
                    </div>
                </div>

                <div class="row">
                    <?php
                    foreach ($konfigurasi as $key => $data) {
                        if (strpos($key, 'motor_list_') !== false) {
                            ?>
                            <div class="col-md-6 mb-3 text-left motor-wrapper" id="wrapper_<?php echo $key; ?>">

                                <label for="<?php echo $key; ?>" class="font-weight-bold text-dark mb-2">
                                    <i class="fas fa-list-ul text-primary mr-1"></i> <?php echo $data['setting_name']; ?>
                                </label>

                                <textarea class="form-control motor-textarea" id="<?php echo $key; ?>"
                                    name="<?php echo $key; ?>" data-unit="<?php echo $data['setting_name']; ?>" rows="8"
                                    placeholder="Ketik nama motor di sini..."><?php echo htmlspecialchars($data['setting_value']); ?></textarea>
                                <small class="form-text text-muted mt-1"><?php echo $data['description']; ?></small>
                            </div>
                        <?php }
                    } ?>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // --- 1. LOGIKA SIMPAN MASTER (SATU TOMBOL UNTUK SEMUA) ---
        $('#btn-save-master-motor').on('click', function () {
            let btn = $(this);
            let originalText = btn.html();

            // Ubah tampilan tombol jadi loading
            btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop('disabled', true);

            // Ambil SEMUA data di form sekaligus
            let formData = $('#formMotor').serialize() + '&is_ajax=1';

            // Lakukan request AJAX ke settings_process.php
            $.ajax({
                type: 'POST',
                url: '', // Submit ke URL halaman ini sendiri
                data: formData,
                success: function (response) {
                    // Beri efek visual sukses (Warna hijau sementara)
                    btn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check mr-1"></i> Tersimpan');

                    // Tampilkan notifikasi toastr jika ada
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Seluruh daftar motor berhasil diperbarui!', 'Tersimpan');
                    }

                    // Kembalikan tombol seperti semula setelah 2 detik
                    setTimeout(() => {
                        btn.removeClass('btn-success').addClass('btn-primary').html(originalText).prop('disabled', false);
                    }, 2000);
                },
                error: function () {
                    // Kembalikan tombol jika gagal
                    btn.html(originalText).prop('disabled', false);

                    if (typeof toastr !== 'undefined') {
                        toastr.error('Gagal menyimpan daftar motor. Periksa koneksi internet Anda.', 'Error');
                    } else {
                        alert('Gagal menyimpan data. Periksa koneksi Anda.');
                    }
                }
            });
        });


        // --- 2. LOGIKA PENCARIAN MOTOR DI DALAM TEXTAREA ---
        $('#searchMasterMotor').on('input', function () {
            let keyword = $(this).val().toLowerCase();
            let resultsBox = $('#searchResultsMaster');
            resultsBox.empty();

            if (keyword.length < 2) {
                resultsBox.hide();
                return;
            }

            let matches = [];

            // Memindai isi dari semua textarea secara real-time
            $('.motor-textarea').each(function () {
                let textarea = $(this);
                let unitName = textarea.data('unit');
                let wrapperId = 'wrapper_' + textarea.attr('id');
                let textareaId = textarea.attr('id');

                // Memisahkan teks berdasarkan baris baru (enter)
                let motors = textarea.val().split('\n');

                motors.forEach(function (motor) {
                    let cleanMotor = motor.trim();
                    if (cleanMotor !== '' && cleanMotor.toLowerCase().includes(keyword)) {
                        matches.push({
                            originalName: cleanMotor,
                            unit: unitName,
                            wrapperId: wrapperId,
                            textareaId: textareaId
                        });
                    }
                });
            });

            if (matches.length > 0) {
                // Tampilkan hasil
                matches.forEach(m => {
                    let li = $('<li>').addClass('list-group-item list-group-item-action cursor-pointer')
                        .html(`<strong>${m.originalName}</strong> <br><span class="text-muted small"><i class="fas fa-map-marker-alt mr-1"></i> ${m.unit}</span>`)
                        .on('click', function () {
                            let targetWrapper = $('#' + m.wrapperId);
                            let targetTextarea = $('#' + m.textareaId);

                            // 1. Scroll ke kotak Unit
                            targetWrapper.get(0).scrollIntoView({ behavior: 'smooth', block: 'center' });

                            // 2. Beri efek Highlight
                            $('.motor-wrapper').removeClass('highlight-box');
                            targetWrapper.addClass('highlight-box');
                            setTimeout(() => targetWrapper.removeClass('highlight-box'), 2500);

                            // 3. Fokuskan kursor ke dalam textarea tersebut
                            targetTextarea.focus();

                            // 4. Sembunyikan hasil pencarian
                            resultsBox.hide();
                            $('#searchMasterMotor').val('');
                        });
                    resultsBox.append(li);
                });
                resultsBox.show();
            } else {
                resultsBox.append($('<li>').addClass('list-group-item text-muted text-center py-3').text('Motor tidak ditemukan di Master Data.'));
                resultsBox.show();
            }
        });

        // Sembunyikan hasil pencarian jika klik di luar area
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.position-relative').length) {
                $('#searchResultsMaster').hide();
            }
        });

    });
</script>