<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>
    .motor-row {
        transition: background-color 0.5s ease;
    }

    .highlight-row {
        background-color: #fff3cd !important;
        border-radius: 5px;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>

<div class="tab-pane fade" id="content-inspeksi" role="tabpanel">
    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">Konfigurasi Form Inspeksi (Link GForm)</h3>
        </div>

        <form id="formInspeksi" action="" method="POST">
            <input type="hidden" name="form_update_inspeksi" value="1">
            <div class="card-body pt-0 text-left">
                <p class="text-muted text-sm mb-3 pb-2 border-bottom">
                    Atur link Google Form untuk masing-masing motor. Sistem akan <b>menyimpan otomatis</b> saat Anda
                    menekan tombol Terapkan.
                </p>

                <div class="mb-4 position-relative">
                    <div class="input-group shadow-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-primary"></i></span>
                        </div>
                        <input type="text" id="searchMotorInspeksi" class="form-control"
                            placeholder="Ketik nama motor (contoh: PA FAN) untuk mencari cepat...">
                    </div>
                    <ul id="searchResultsInspeksi" class="list-group position-absolute w-100 shadow"
                        style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;"></ul>
                </div>

                <?php
                $gform_data = isset($konfigurasi['form_links_json']) ? json_decode($konfigurasi['form_links_json']['setting_value'], true) : [];
                $unit_mapping = [
                    'c6kv' => ['setting' => 'motor_list_c6kv', 'label' => 'PLTU UNIT C - MOTOR 6kV'],
                    'c380' => ['setting' => 'motor_list_c380', 'label' => 'PLTU UNIT C - MOTOR 380V'],
                    'd6kv' => ['setting' => 'motor_list_d6kv', 'label' => 'PLTU UNIT D - MOTOR 6kV'],
                    'd380' => ['setting' => 'motor_list_d380', 'label' => 'PLTU UNIT D - MOTOR 380V'],
                    'utility6kv' => ['setting' => 'motor_list_utility6kv', 'label' => 'PLTU UTILITY - MOTOR 6kV'],
                    'utility380' => ['setting' => 'motor_list_utility380', 'label' => 'PLTU UTILITY - MOTOR 380V'],
                    'utility240' => ['setting' => 'motor_list_utility240', 'label' => 'PLTU UTILITY - MOTOR 240V']
                ];
                ?>
                <div class="accordion" id="accordionGForm">
                    <?php
                    $i = 0;
                    foreach ($unit_mapping as $unit_key => $unit_info):
                        $is_expanded = ($i === 0) ? 'true' : 'false';
                        $collapse_class = ($i === 0) ? 'show' : '';
                        $motor_raw = isset($konfigurasi[$unit_info['setting']]) ? $konfigurasi[$unit_info['setting']]['setting_value'] : '';
                        $motor_list = array_filter(array_map('trim', explode("\n", $motor_raw)));
                        ?>
                        <div class="card mb-2 shadow-none border" style="border-radius: 8px; overflow: hidden;">
                            <div class="card-header bg-light p-0">
                                <h2 class="mb-0">
                                    <button
                                        class="btn btn-link btn-block text-left text-dark font-weight-bold p-3 text-decoration-none"
                                        type="button" data-toggle="collapse" data-target="#col_<?= $unit_key ?>"
                                        aria-expanded="<?= $is_expanded ?>">
                                        <i class="fas fa-folder-open text-primary mr-2"></i> <?= $unit_info['label'] ?>
                                    </button>
                                </h2>
                            </div>
                            <div id="col_<?= $unit_key ?>" class="collapse <?= $collapse_class ?>"
                                data-parent="#accordionGForm">
                                <div class="card-body p-3 bg-white">
                                    <?php if (empty($motor_list)): ?>
                                        <div class="text-muted text-center py-3">
                                            <i class="fas fa-exclamation-circle fa-2x opacity-50 mb-2"></i><br>Belum ada data
                                            motor di Master Data.
                                        </div>
                                    <?php else:
                                        foreach ($motor_list as $motor):
                                            $saved_edit = isset($gform_data[$unit_key][$motor]['edit']) ? $gform_data[$unit_key][$motor]['edit'] : '';
                                            $saved_user = isset($gform_data[$unit_key][$motor]['user']) ? $gform_data[$unit_key][$motor]['user'] : '';

                                            $id_safe = md5($unit_key . $motor);
                                            $input_id_edit = "edit_" . $id_safe;
                                            $input_id_user = "user_" . $id_safe;
                                            ?>
                                            <div class="form-group row align-items-center mb-2 pb-2 border-bottom motor-row"
                                                id="row_<?= $id_safe ?>" data-motor="<?= htmlspecialchars($motor) ?>"
                                                data-unit="<?= $unit_info['label'] ?>" data-collapse="#col_<?= $unit_key ?>">
                                                <label class="col-sm-4 col-form-label text-sm m-0">
                                                    <span class="text-dark"><i class="fas fa-cogs mr-2 text-muted"></i>
                                                        <?= htmlspecialchars($motor) ?></span>
                                                </label>

                                                <div class="col-sm-8 text-right">
                                                    <input type="hidden" name="gform_keys[<?= $unit_key ?>][]"
                                                        value="<?= htmlspecialchars($motor) ?>">
                                                    <input type="hidden" name="gform_edit_vals[<?= $unit_key ?>][]"
                                                        id="<?= $input_id_edit ?>" value="<?= htmlspecialchars($saved_edit) ?>">
                                                    <input type="hidden" name="gform_user_vals[<?= $unit_key ?>][]"
                                                        id="<?= $input_id_user ?>" value="<?= htmlspecialchars($saved_user) ?>">

                                                    <div class="btn-group mr-1 mb-1">
                                                        <button type="button"
                                                            class="btn btn-sm <?= !empty($saved_user) ? 'btn-dark' : 'btn-outline-dark' ?> btn-qr-trigger"
                                                            data-motor="<?= htmlspecialchars($motor) ?>"
                                                            data-unit="<?= $unit_info['label'] ?>"
                                                            data-target-user-input="<?= $input_id_user ?>"
                                                            title="Buat QR Code Form User">
                                                            <i class="fas fa-qrcode"></i>
                                                        </button>
                                                    </div>

                                                    <div class="btn-group mr-1 mb-1">
                                                        <button type="button"
                                                            class="btn btn-sm btn-set-link <?= !empty($saved_edit) ? 'btn-info' : 'btn-outline-info' ?>"
                                                            data-target-id="<?= $input_id_edit ?>"
                                                            data-title="Link Edit Form: <?= htmlspecialchars($motor) ?>"
                                                            data-btn-class="btn-info" title="Atur Link Edit Form">
                                                            <i class="fas fa-edit"></i> Edit Form
                                                        </button>
                                                        <a href="<?= !empty($saved_edit) ? htmlspecialchars($saved_edit) : '#' ?>"
                                                            target="_blank"
                                                            class="btn btn-sm btn-info btn-go-link font-weight-bold <?= empty($saved_edit) ? 'd-none' : '' ?>"
                                                            id="go_<?= $input_id_edit ?>" title="Buka Link Edit di Tab Baru">
                                                            GO
                                                        </a>
                                                    </div>

                                                    <div class="btn-group mb-1">
                                                        <button type="button"
                                                            class="btn btn-sm btn-set-link <?= !empty($saved_user) ? 'btn-success' : 'btn-outline-success' ?>"
                                                            data-target-id="<?= $input_id_user ?>"
                                                            data-title="Link Form User: <?= htmlspecialchars($motor) ?>"
                                                            data-btn-class="btn-success" title="Atur Link Form User">
                                                            <i class="fas fa-link"></i> Form User
                                                        </button>
                                                        <a href="<?= !empty($saved_user) ? htmlspecialchars($saved_user) : '#' ?>"
                                                            target="_blank"
                                                            class="btn btn-sm btn-success btn-go-link font-weight-bold <?= empty($saved_user) ? 'd-none' : '' ?>"
                                                            id="go_<?= $input_id_user ?>" title="Buka Link User di Tab Baru">
                                                            GO
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php $i++; endforeach; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="modalSetLink" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold" id="modalSetLinkTitle">Update Link</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalTargetInputId" value="">
                    <input type="hidden" id="modalTargetBtnClass" value="">
                    <div class="form-group">
                        <label>Masukkan URL / Link GForm:</label>
                        <input type="url" class="form-control" id="modalUrlValue"
                            placeholder="https://docs.google.com/forms/...">
                        <small class="text-muted mt-2 d-block">Kosongkan kolom ini jika ingin menghapus link
                            sebelumnya.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnApplyLink"><i class="fas fa-check mr-1"></i>
                        Terapkan & Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalGenerateQR" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-qrcode mr-2"></i> Unduh QR Code</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="qrLinkToEncode">
                    <input type="hidden" id="qrFilename">

                    <div class="alert alert-info text-sm mb-3">
                        <i class="fas fa-info-circle mr-1"></i> QR Code akan diunduh dengan resolusi HD.
                    </div>

                    <div class="form-group">
                        <label class="text-muted text-sm">Nama File Output:</label>
                        <input type="text" class="form-control bg-light" id="qrPreviewFilename" readonly>
                    </div>

                    <div class="form-group mt-3">
                        <label class="font-weight-bold">Tingkat Kualitas:</label>
                        <select id="qrQualityLevel" class="form-control custom-select">
                            <option value="L">Level L (Low - 7%)</option>
                            <option value="M" selected>Level M (Medium - 15%)</option>
                            <option value="Q">Level Q (Quartile - 25%)</option>
                            <option value="H">Level H (High - 30%)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnExecuteQR"><i class="fas fa-download mr-1"></i>
                        Buat & Unduh</button>
                </div>
            </div>
        </div>
    </div>

    <div id="hidden-qr-render-box" style="display: none;"></div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // --- 1. LOGIKA MODAL & AJAX AUTO-SAVE ---
        $('.btn-set-link').on('click', function () {
            let targetId = $(this).data('target-id');
            let title = $(this).data('title');
            let btnClass = $(this).data('btn-class');
            let currentLink = $('#' + targetId).val();

            $('#modalSetLinkTitle').text(title);
            $('#modalUrlValue').val(currentLink);
            $('#modalTargetInputId').val(targetId);
            $('#modalTargetBtnClass').val(btnClass);
            $('#modalSetLink').modal('show');
        });

        $('#btnApplyLink').on('click', function () {
            let btnApply = $(this);
            let targetId = $('#modalTargetInputId').val();
            let newValue = $('#modalUrlValue').val();
            let btnClass = $('#modalTargetBtnClass').val();

            // Update input hidden di dalam form terlebih dahulu
            $('#' + targetId).val(newValue);

            // Ubah tampilan tombol Terapkan jadi efek Loading
            let originalText = btnApply.html();
            btnApply.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

            // Ambil semua data di dalam form, dan tambahkan parameter '&is_ajax=1'
            let formData = $('#formInspeksi').serialize() + '&is_ajax=1';

            // LAKUKAN PROSES AJAX POST
            $.ajax({
                type: 'POST',
                url: '', // Submit ke URL halaman ini sendiri
                data: formData,
                success: function (response) {
                    // Jika Berhasil: Update Warna Tombol UI dan Tombol GO
                    let targetBtn = $('button[data-target-id="' + targetId + '"]');
                    let goBtn = $('#go_' + targetId);
                    let isUserForm = targetId.startsWith('user_');
                    let barcodeBtn = isUserForm ? $('button[data-target-user-input="' + targetId + '"]') : null;

                    if (newValue.trim() !== '') {
                        targetBtn.removeClass('btn-outline-' + btnClass.split('-')[1]).addClass(btnClass);
                        goBtn.attr('href', newValue).removeClass('d-none');
                        if (isUserForm && barcodeBtn) barcodeBtn.removeClass('btn-outline-dark').addClass('btn-dark');
                    } else {
                        targetBtn.removeClass(btnClass).addClass('btn-outline-' + btnClass.split('-')[1]);
                        goBtn.attr('href', '#').addClass('d-none');
                        if (isUserForm && barcodeBtn) barcodeBtn.removeClass('btn-dark').addClass('btn-outline-dark');
                    }

                    // Tutup modal, kembalikan tombol, munculkan Toast Notifikasi
                    $('#modalSetLink').modal('hide');
                    btnApply.html(originalText).prop('disabled', false);

                    if (typeof toastr !== 'undefined') {
                        toastr.success('Link Form berhasil disimpan ke Database!', 'Tersimpan');
                    }
                },
                error: function () {
                    // Jika Gagal koneksi
                    btnApply.html(originalText).prop('disabled', false);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Terjadi kesalahan saat menyimpan ke database.', 'Error');
                    } else {
                        alert('Gagal menyimpan ke database. Periksa koneksi internet Anda.');
                    }
                }
            });
        });


        // --- 2. LOGIKA GENERATOR QR CODE HD ---
        $('.btn-qr-trigger').on('click', function () {
            let motor = $(this).data('motor');
            let unit = $(this).data('unit');
            let targetInputId = $(this).data('target-user-input');
            let userLink = $('#' + targetInputId).val();

            if (!userLink || userLink.trim() === '') {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Link Form User masih kosong! Silakan isi terlebih dahulu.', 'Peringatan');
                } else {
                    alert('Link Form User masih kosong!');
                }
                return;
            }

            let cleanUnit = unit.replace('PLTU ', '');
            let filename = `${motor} - ${cleanUnit}.png`;

            $('#qrPreviewFilename').val(filename);
            $('#qrFilename').val(filename);
            $('#qrLinkToEncode').val(userLink);

            $('#modalGenerateQR').modal('show');
        });

        $('#btnExecuteQR').on('click', function () {
            let link = $('#qrLinkToEncode').val();
            let filename = $('#qrFilename').val();
            let level = $('#qrQualityLevel').val();

            let selectedLevel = QRCode.CorrectLevel.M;
            if (level === 'L') selectedLevel = QRCode.CorrectLevel.L;
            if (level === 'Q') selectedLevel = QRCode.CorrectLevel.Q;
            if (level === 'H') selectedLevel = QRCode.CorrectLevel.H;

            let qrBox = document.getElementById("hidden-qr-render-box");
            qrBox.innerHTML = "";

            new QRCode(qrBox, {
                text: link,
                width: 1024,
                height: 1024,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: selectedLevel
            });

            let btn = $(this);
            let originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);

            setTimeout(() => {
                let canvas = qrBox.querySelector("canvas");
                let img = qrBox.querySelector("img");
                let imageSrc = canvas ? canvas.toDataURL("image/png") : (img ? img.src : null);

                if (imageSrc) {
                    const downloadLink = document.createElement("a");
                    downloadLink.href = imageSrc;
                    downloadLink.download = filename;
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);

                    if (typeof toastr !== 'undefined') toastr.success(`QR Code berhasil diunduh.`, 'Berhasil');
                    $('#modalGenerateQR').modal('hide');
                } else {
                    alert("Gagal menghasilkan QR Code.");
                }
                btn.html(originalText).prop('disabled', false);
            }, 500);
        });


        // --- 3. LOGIKA PENCARIAN MOTOR ---
        let motorData = [];
        $('.motor-row').each(function () {
            motorData.push({
                name: $(this).data('motor').toLowerCase(),
                originalName: $(this).data('motor'),
                unit: $(this).data('unit'),
                collapseId: $(this).data('collapse'),
                rowId: $(this).attr('id')
            });
        });

        $('#searchMotorInspeksi').on('input', function () {
            let keyword = $(this).val().toLowerCase();
            let resultsBox = $('#searchResultsInspeksi');
            resultsBox.empty();

            if (keyword.length < 2) {
                resultsBox.hide();
                return;
            }

            let matches = motorData.filter(m => m.name.includes(keyword));

            if (matches.length > 0) {
                matches.forEach(m => {
                    let li = $('<li>').addClass('list-group-item list-group-item-action cursor-pointer')
                        .html(`<strong>${m.originalName}</strong> <br><span class="text-muted small"><i class="fas fa-map-marker-alt mr-1"></i> ${m.unit}</span>`)
                        .on('click', function () {
                            $(m.collapseId).collapse('show');
                            setTimeout(() => {
                                let targetRow = $('#' + m.rowId);
                                $('html, body').animate({ scrollTop: targetRow.offset().top - 100 }, 500);
                                $('.motor-row').removeClass('highlight-row');
                                targetRow.addClass('highlight-row');
                                setTimeout(() => targetRow.removeClass('highlight-row'), 2000);
                                resultsBox.hide();
                                $('#searchMotorInspeksi').val('');
                            }, 350);
                        });
                    resultsBox.append(li);
                });
                resultsBox.show();
            } else {
                resultsBox.append($('<li>').addClass('list-group-item text-muted text-center py-3').text('Nama motor tidak ditemukan.'));
                resultsBox.show();
            }
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.position-relative').length) {
                $('#searchResultsInspeksi').hide();
            }
        });

    });
</script>