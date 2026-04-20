<div class="tab-pane fade" id="content-pdf" role="tabpanel">
    <div class="card card-outline card-danger shadow-sm" style="border-radius: 10px;">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">Pengaturan Kop Surat PDF</h3>
        </div>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="form_update_pdf" value="1">
            <div class="card-body pt-0 text-left">
                <p class="text-muted text-sm mb-4 pb-2 border-bottom">
                    Sesuaikan teks judul dan logo yang akan ditampilkan saat mengunduh dokumen PDF.
                </p>
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Judul Baris 1 (Utama)</label>
                    <input type="text" name="pdf_judul_1" class="form-control" value="<?php echo htmlspecialchars($konfigurasi['pdf_judul_1']['setting_value'] ?? ''); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Judul Baris 2 (Nama Perusahaan)</label>
                    <input type="text" name="pdf_judul_2" class="form-control" value="<?php echo htmlspecialchars($konfigurasi['pdf_judul_2']['setting_value'] ?? ''); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Logo Watermark PDF</label>
                    <div class="mb-3 p-3 text-center" style="background-color: #f8f9fa; border: 1px dashed #ccc; border-radius: 5px;">
                        <?php if(!empty($konfigurasi['pdf_logo_base64']['setting_value'])): ?>
                            <img src="<?php echo $konfigurasi['pdf_logo_base64']['setting_value']; ?>" alt="Logo Preview" style="max-height: 100px;">
                            <div class="mt-2 text-success" style="font-size: 12px;"><i class="fas fa-check-circle"></i> Logo Terpasang</div>
                        <?php else: ?>
                            <div class="text-muted"><i class="fas fa-image fa-2x mb-2 text-black-50"></i><br>Belum ada logo terpasang.</div>
                        <?php endif; ?>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="logoUpload" name="logo_baru" accept="image/png, image/jpeg">
                        <label class="custom-file-label" for="logoUpload">Pilih file logo baru...</label>
                    </div>
                    <small class="text-muted mt-2 d-block">Biarkan kosong jika tidak ingin mengubah logo saat ini.</small>
                </div>
            </div>
            <div class="card-footer bg-light text-right" style="border-radius: 0 0 10px 10px;">
                <button type="submit" class="btn btn-danger px-4 shadow-sm"><i class="fas fa-save mr-1"></i> Simpan Pengaturan PDF</button>
            </div>
        </form>
    </div>
</div>