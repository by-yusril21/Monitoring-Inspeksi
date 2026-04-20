<style>
    /* Efek highlight saat motor dicari */
    .highlight-filter { 
        background-color: #fff3cd !important; 
        border-radius: 5px; 
        transition: background-color 0.5s ease; 
        border: 1px solid #ffeeba;
    }
    .cursor-pointer { cursor: pointer; }
</style>

<div class="tab-pane fade" id="content-filter" role="tabpanel">
    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">Pilih Motor untuk Jadwal Regreasing</h3>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="form_update_regreasing" value="1">
            <div class="card-body pt-0 text-left">
                <p class="text-muted text-sm mb-3 pb-2 border-bottom">
                    Berikut adalah daftar motor dari <b>Master List</b>. Centang motor yang ingin Anda munculkan di halaman Jadwal Regreasing.
                </p>

                <div class="mb-4 position-relative">
                    <div class="input-group shadow-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-primary"></i></span>
                        </div>
                        <input type="text" id="searchMotorFilter" class="form-control" placeholder="Ketik nama motor (contoh: PA FAN) untuk mencari cepat di semua unit...">
                    </div>
                    <ul id="searchResultsFilter" class="list-group position-absolute w-100 shadow" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;"></ul>
                </div>

                <?php 
                $mapping_filter = [
                    'regreasing_filter_c6kv' => ['master' => 'motor_list_c6kv', 'judul' => 'Motor 6kV PLTU Unit C'],
                    'regreasing_filter_c380' => ['master' => 'motor_list_c380', 'judul' => 'Motor 380V PLTU Unit C'],
                    'regreasing_filter_d6kv' => ['master' => 'motor_list_d6kv', 'judul' => 'Motor 6kV PLTU Unit D'],
                    'regreasing_filter_d380' => ['master' => 'motor_list_d380', 'judul' => 'Motor 380V PLTU Unit D'],
                    'regreasing_filter_utility6kv' => ['master' => 'motor_list_utility6kv', 'judul' => 'Motor 6kV PLTU Unit Utility'],
                    'regreasing_filter_utility380' => ['master' => 'motor_list_utility380', 'judul' => 'Motor 380V PLTU Unit Utility']
                ];

                foreach ($mapping_filter as $filter_key => $data_map) {
                    $master_key = $data_map['master'];
                    if (!isset($konfigurasi[$master_key])) continue;

                    $master_raw = $konfigurasi[$master_key]['setting_value'];
                    $master_array = array_filter(array_map('trim', explode("\n", $master_raw)));
                    $checked_raw = isset($konfigurasi[$filter_key]) ? $konfigurasi[$filter_key]['setting_value'] : '';
                    $checked_array = array_map('trim', explode(',', $checked_raw));
                ?>
                    <div class="mb-4">
                        <label class="font-weight-bold text-primary"><i class="fas fa-layer-group mr-1"></i> <?php echo $data_map['judul']; ?></label>
                        <div class="row bg-light p-3 border position-relative" id="container_<?php echo $filter_key; ?>" style="border-radius: 8px; max-height: 200px; overflow-y: auto;">
                            <?php foreach($master_array as $m) { 
                                $isSel = in_array($m, $checked_array) ? 'checked' : '';
                                $unique_id = md5($filter_key . '_' . $m); 
                            ?>
                            <div class="col-md-4 mb-2 p-1 motor-filter-item" id="item_<?php echo $unique_id; ?>" data-motor="<?php echo htmlspecialchars($m); ?>" data-unit="<?php echo $data_map['judul']; ?>">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="reg_<?php echo $unique_id; ?>" name="<?php echo $filter_key; ?>[]" value="<?php echo htmlspecialchars($m); ?>" <?php echo $isSel; ?>>
                                    <label class="custom-control-label font-weight-normal" for="reg_<?php echo $unique_id; ?>"><?php echo htmlspecialchars($m); ?></label>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="card-footer bg-light text-right" style="border-radius: 0 0 10px 10px;">
                <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save mr-1"></i> Update Filter</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Ekstrak data motor dari HTML (khusus halaman filter)
    let filterMotorData = [];
    $('.motor-filter-item').each(function() {
        filterMotorData.push({
            name: $(this).data('motor').toLowerCase(),
            originalName: $(this).data('motor'),
            unit: $(this).data('unit'),
            rowId: $(this).attr('id')
        });
    });

    // 2. Logika saat mengetik di search bar
    $('#searchMotorFilter').on('input', function() {
        let keyword = $(this).val().toLowerCase();
        let resultsBox = $('#searchResultsFilter');
        resultsBox.empty();
        
        if (keyword.length < 2) {
            resultsBox.hide();
            return; 
        }
        
        // Filter array data motor
        let matches = filterMotorData.filter(m => m.name.includes(keyword));
        
        if (matches.length > 0) {
            matches.forEach(m => {
                let li = $('<li>').addClass('list-group-item list-group-item-action cursor-pointer')
                    .html(`<strong>${m.originalName}</strong> <br><span class="text-muted small"><i class="fas fa-map-marker-alt mr-1"></i> ${m.unit}</span>`)
                    .on('click', function() {
                        let targetItem = $('#' + m.rowId);
                        
                        // Scroll pintar: otomatis mengatur scroll global dan scroll container
                        targetItem.get(0).scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Efek Highlight sementara agar mudah terlihat
                        $('.motor-filter-item').removeClass('highlight-filter');
                        targetItem.addClass('highlight-filter');
                        setTimeout(() => targetItem.removeClass('highlight-filter'), 2000); 
                        
                        // Bersihkan dan sembunyikan dropdown pencarian
                        resultsBox.hide();
                        $('#searchMotorFilter').val('');
                    });
                resultsBox.append(li);
            });
            resultsBox.show();
        } else {
            resultsBox.append($('<li>').addClass('list-group-item text-muted text-center py-3').text('Motor tidak ditemukan di daftar ini.'));
            resultsBox.show();
        }
    });

    // 3. Sembunyikan hasil pencarian jika klik di luar area
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.position-relative').length) {
            $('#searchResultsFilter').hide();
        }
    });

});
</script>