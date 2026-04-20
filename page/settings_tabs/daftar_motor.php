<?php
// Format data URL dan Token (Konfigurasi sudah dipanggil di settings.php)
$unit_apis = [];
if (isset($SCRIPT_URLS) && isset($API_TOKEN)) {
    foreach ($SCRIPT_URLS as $kode_unit => $url) {
        $unit_apis[] = [
            'nama_unit' => $kode_unit,
            'url'       => $url,
            'token'     => $API_TOKEN
        ];
    }
}
?>

<style>
    /* Kunci Kolom Nomor agar sangat ramping */
    .col-nomor {
        width: 35px !important;
        min-width: 35px !important;
        max-width: 35px !important;
        text-align: center;
    }

    /* Merapikan elemen bawaan DataTables agar rapi saat tabel mepet card */
    .dataTables_wrapper { padding: 0; }
    .dataTables_wrapper .dataTables_filter { padding: 12px 15px 0 15px; }
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 4px;
        padding: 4px 10px;
        height: 30px; 
    }
    .dataTables_wrapper .row:last-child {
        padding: 10px 15px;
        background-color: #fff;
        border-radius: 0 0 10px 10px;
    }
    .dataTables_wrapper .dataTables_paginate { padding-top: 0 !important; }
    
    /* Style untuk Badge Section No */
    .badge-section {
        font-weight: normal;
        font-size: 0.85rem;
        padding: 4px 8px;
    }

    /* Menghilangkan border kiri-kanan tabel agar menyatu dengan card */
    table.dataTable {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
    }
    table.dataTable thead th {
        border-bottom: 1px solid #dee2e6 !important;
    }
</style>

<div class="tab-pane fade" id="content-daftar-motor" role="tabpanel">
    <div class="card card-outline card-primary shadow-sm" style="border-radius: 10px;">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">Acuan Daftar Nama Motor dari Spreadsheet</h3>
        </div>
        
        <div class="card-body bg-light p-0 pt-2">
            <?php if (empty($unit_apis)): ?>
                <div class="alert alert-warning m-3">
                    <i class="fas fa-exclamation-triangle"></i> Konfigurasi API tidak ditemukan di config.php.
                </div>
            <?php else: ?>

            <div class="row m-0 px-2 pb-2">
                <?php 
                foreach ($unit_apis as $index => $unit) {
                    $tab_id = preg_replace('/[^a-zA-Z0-9]/', '_', $unit['nama_unit']);
                ?>
                <div class="col-md-6 mb-2 px-1">
                    <div class="card shadow-sm collapsed-card h-100 mb-0" style="border-radius: 10px; overflow: hidden;">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title text-bold" style="font-size: 15px;">
                                <i class="fas fa-motorcycle mr-1 text-primary"></i> <?php echo htmlspecialchars($unit['nama_unit']); ?>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool btn-copy-data text-success" data-index="<?php echo $index; ?>" title="Copy Semua Nama Motor" style="display:none;">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                                <button type="button" class="btn btn-tool btn-refresh-data" data-index="<?php echo $index; ?>" title="Refresh Data" style="display:none;">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-tool btn-expand-unit" data-card-widget="collapse" data-loaded="false" data-index="<?php echo $index; ?>">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body p-0" style="display: none; background-color: #fff;">
                            <div id="loading-<?php echo $tab_id; ?>" class="text-center py-4">
                                <div class="spinner-border text-primary" style="width: 1.5rem; height: 1.5rem;" role="status"></div>
                                <div class="text-muted mt-2 text-sm">Menyiapkan data...</div>
                            </div>
                            
                            <div id="error-<?php echo $tab_id; ?>" class="alert alert-danger m-2 p-2" style="display:none;"><small></small></div>
                            
                            <div class="table-responsive" style="margin: 0;">
                                <table id="tabel-<?php echo $tab_id; ?>" class="table table-sm table-bordered table-striped m-0" style="display:none; width: 100%;">
                                    <thead>
                                        <tr class="bg-primary">
                                            <th class="col-nomor text-center">No</th>
                                            <th class="text-left">Nama Lengkap Motor</th> 
                                            <th class="text-center" style="width: 90px;">Section No</th> 
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-<?php echo $tab_id; ?>"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dataUnits = <?php echo json_encode($unit_apis); ?>;
    const cacheCopyMotor = {};

    async function muatDataMotor(unit) {
        const tabId = unit.nama_unit.replace(/[^a-zA-Z0-9]/g, '_');
        const tbody = document.getElementById(`tbody-${tabId}`);
        const loading = document.getElementById(`loading-${tabId}`);
        const errorBox = document.getElementById(`error-${tabId}`);
        const table = document.getElementById(`tabel-${tabId}`);
        
        const refreshBtn = document.querySelector(`.btn-refresh-data[data-index="${dataUnits.indexOf(unit)}"]`);
        const copyBtn = document.querySelector(`.btn-copy-data[data-index="${dataUnits.indexOf(unit)}"]`);

        loading.style.display = 'block';
        errorBox.style.display = 'none';
        table.style.display = 'none';

        try {
            const response = await fetch(`${unit.url}?action=get_master&token=${unit.token}`);
            const result = await response.json();

            if (result.status === "success") {
                let htmlRows = "";
                let copyTextArray = []; 

                result.data.forEach((item, idx) => {
                    let sectionHtml = item.sectionNo && item.sectionNo.trim() !== '-' && item.sectionNo.trim() !== '' 
                                      ? `<span class="badge badge-light border text-muted badge-section">${item.sectionNo}</span>` 
                                      : `<span class="text-muted">-</span>`;

                    htmlRows += `<tr>
                        <td class="col-nomor">${idx + 1}</td>
                        <td class="text-left text-bold">${item.namaMotor}</td>
                        <td class="text-center">${sectionHtml}</td>
                    </tr>`;
                    
                    copyTextArray.push(item.namaMotor);
                });

                cacheCopyMotor[tabId] = copyTextArray.join('\n');
                tbody.innerHTML = htmlRows;
                loading.style.display = 'none';
                table.style.display = 'table';
                
                if(refreshBtn) refreshBtn.style.display = 'inline-block';
                if(copyBtn) copyBtn.style.display = 'inline-block';

                if ($.fn.DataTable.isDataTable(`#tabel-${tabId}`)) {
                    $(`#tabel-${tabId}`).DataTable().destroy();
                }
                
                $(`#tabel-${tabId}`).DataTable({
                  "responsive": true, 
                  "autoWidth": false, 
                  "pageLength": 10, 
                  "lengthChange": false, 
                  "ordering": false,
                  "columnDefs": [
                      { "width": "35px", "targets": 0 },
                      { "width": "90px", "targets": 2 }
                  ],
                  "language": {
                      "search": "", 
                      "searchPlaceholder": "Cari motor...",
                      "info": "<span class='text-muted text-sm'>_START_-_END_ dari _TOTAL_</span>",
                      "paginate": { "next": "<i class='fas fa-chevron-right' style='font-size: 0.7rem;'></i>", "previous": "<i class='fas fa-chevron-left' style='font-size: 0.7rem;'></i>" }
                  },
                  "dom": "<'row m-0'<'col-sm-12'f>>" +
                         "<'row m-0'<'col-sm-12 p-0'tr>>" +
                         "<'row m-0 pt-2 align-items-center'<'col-sm-5'i><'col-sm-7'p>>",
                });
            } else { throw new Error(result.message); }
        } catch (error) {
            loading.style.display = 'none';
            errorBox.style.display = 'block';
            errorBox.querySelector('small').innerText = "Error: " + error.message;
        }
    }

    $('.btn-expand-unit').on('click', function() {
        const btn = $(this);
        const isCollapsed = btn.closest('.card').hasClass('collapsed-card');
        if (isCollapsed && btn.attr('data-loaded') === 'false') {
            muatDataMotor(dataUnits[btn.attr('data-index')]);
            btn.attr('data-loaded', 'true');
        }
    });

    $('.btn-refresh-data').on('click', function(e) {
        e.stopPropagation();
        muatDataMotor(dataUnits[$(this).attr('data-index')]);
    });

    $('.btn-copy-data').on('click', function(e) {
        e.stopPropagation(); 
        const index = $(this).attr('data-index');
        const unit = dataUnits[index];
        const tabId = unit.nama_unit.replace(/[^a-zA-Z0-9]/g, '_');
        const textToCopy = cacheCopyMotor[tabId];

        if (textToCopy) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                if (typeof toastr !== 'undefined') {
                    toastr.success(`Berhasil menyalin ${textToCopy.split('\\n').length} nama motor dari ${unit.nama_unit}!`);
                } else {
                    alert('Data berhasil disalin!');
                }
            }).catch(err => {
                alert('Browser Anda tidak mendukung fitur copy otomatis.');
            });
        }
    });
});
</script>