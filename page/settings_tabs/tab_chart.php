<div class="tab-pane fade show active" id="content-chart" role="tabpanel">
    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">Konfigurasi Visual & Sistem Grafik</h3>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="form_update_chart" value="1">
            <div class="card-body pt-0">
                <p class="text-muted text-sm mb-4 pb-2 border-bottom text-left">
                    Ubah parameter di bawah ini untuk menyesuaikan fungsionalitas Grafik. Centang data yang ingin Anda <b>sembunyikan</b> saat grafik pertama kali dimuat.
                </p>
                <?php 
                foreach ($konfigurasi as $key => $data) { 
                    if(strpos($key, 'chart_') !== false) {
                ?>
                    <div class="form-group mb-4 text-left">
                        <label for="<?php echo $key; ?>" class="font-weight-bold text-dark"><?php echo $data['setting_name']; ?></label>
                        <?php 
                        if ($key === 'chart_hidden_parameters') { 
                            $daftar_parameter = [
                                'DE_H'  => 'Vib. DE (H)',  'DE_V'  => 'Vib. DE (V)', 'DE_Ax' => 'Vib. DE (Ax)', 'DE_gE' => 'Vib. DE (gE)',
                                'NDE_H' => 'Vib. NDE (H)', 'NDE_V' => 'Vib. NDE (V)', 'NDE_Ax' => 'Vib. NDE (Ax)', 'NDE_gE' => 'Vib. NDE (gE)',
                                'TempDE' => 'Temp DE', 'TempNDE' => 'Temp NDE', 'Suhu' => 'Suhu Ruangan', 'Beban' => 'Beban Gen.', 'Damper' => 'Open Damper',
                                'CurrR' => 'Arus (R)', 'CurrS' => 'Arus (S)', 'CurrT' => 'Arus (T)'
                            ];
                            $hidden_array = array_map('trim', explode(',', $data['setting_value']));
                        ?>
                            <div class="row mt-2">
                                <?php foreach($daftar_parameter as $val => $label) { 
                                    $isChecked = in_array($val, $hidden_array) ? 'checked' : '';
                                ?>
                                <div class="col-md-3 col-sm-4 col-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="chk_<?php echo $val; ?>" name="<?php echo $key; ?>[]" value="<?php echo $val; ?>" <?php echo $isChecked; ?>>
                                        <label class="custom-control-label font-weight-normal text-muted" for="chk_<?php echo $val; ?>"><?php echo $label; ?></label>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        <?php 
                        } else if (strpos($key, 'default_') !== false) { 
                        ?>
                            <select class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" required>
                                <option value="1" <?php echo ($data['setting_value'] == '1') ? 'selected' : ''; ?>>Aktif (Menyala / Ditampilkan)</option>
                                <option value="0" <?php echo ($data['setting_value'] == '0') ? 'selected' : ''; ?>>Mati (Nonaktif / Disembunyikan)</option>
                            </select>
                        <?php 
                        } else { 
                            $inputType = "text"; 
                            if (strpos($key, 'width') !== false || strpos($key, 'radius') !== false || strpos($key, 'step') !== false || strpos($key, 'visible') !== false || strpos($key, 'interval') !== false) {
                                $inputType = "number";
                            } else if (strpos($key, 'bg') !== false || strpos($key, 'color') !== false) {
                                $inputType = "color";
                            }
                        ?>
                            <input type="<?php echo $inputType; ?>" class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($data['setting_value']); ?>" required <?php echo ($inputType == 'number') ? 'step="0.1"' : ''; ?>>
                        <?php } ?>
                        <small class="form-text text-muted mt-1"><i class="fas fa-info-circle mr-1 text-info"></i><?php echo $data['description']; ?></small>
                    </div>
                <?php } } ?>
            </div>
            <div class="card-footer bg-light text-right" style="border-radius: 0 0 10px 10px;">
                <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save mr-1"></i> Simpan Konfigurasi</button>
            </div>
        </form>
    </div>
</div>