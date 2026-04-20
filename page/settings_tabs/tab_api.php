<div class="tab-pane fade" id="content-api" role="tabpanel">
    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">Integrasi Web App Google Apps Script</h3>
        </div>
        <form action="" method="POST">
            <div class="card-body pt-0">
                <p class="text-muted text-sm mb-4 pb-2 border-bottom text-left">
                    Atur token keamanan dan URL untuk masing-masing unit hasil <b>Deploy as Web App</b> terbaru.
                </p>
                <?php 
                foreach ($konfigurasi as $key => $data) { 
                    if($key === 'api_token' || strpos($key, 'script_url_') !== false) {
                        $inputType = (strpos($key, 'script_url_') !== false) ? 'url' : 'text';
                ?>
                    <div class="form-group mb-4 text-left">
                        <label for="<?php echo $key; ?>" class="font-weight-bold text-dark"><?php echo $data['setting_name']; ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light"><i class="fas <?php echo ($key === 'api_token') ? 'fa-key text-warning' : 'fa-globe text-primary'; ?>"></i></span>
                            </div>
                            <input type="<?php echo $inputType; ?>" class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($data['setting_value']); ?>" placeholder="Masukkan <?php echo $data['setting_name']; ?>">
                        </div>
                    </div>
                <?php } } ?>
            </div>
            <div class="card-footer bg-light text-right" style="border-radius: 0 0 10px 10px;">
                <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save mr-1"></i> Simpan Koneksi</button>
            </div>
        </form>
    </div>
</div>