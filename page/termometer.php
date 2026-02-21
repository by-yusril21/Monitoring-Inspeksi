<?php
// WAJIB: Session Start di baris paling awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="content-wrapper thermo-content">

    <div class="card card-custom p-3 transparent-card">
        <div class="gauge-dashboard-title mb-4">
            <i class="fas fa-tachometer-alt mr-2 text-primary"></i> Live Parameter Status:
            <span id="label-motor-gauge" class="text-danger">Menunggu Pilihan Motor...</span>
        </div>

        <div class="unified-dashboard-box w-100">

            <div class="instruments-container d-flex justify-content-around align-items-center flex-wrap w-100">

                <div class="thermo-panel unified-panel">
                    <div class="thermo-wrapper unified-wrapper">
                        <div class="thermo-item">
                            <div class="thermo-title">Bearing DE</div>
                            <div id="thermo-de"></div>
                            <div class="data-display" id="val-de">-- °C</div>
                        </div>

                        <div class="thermo-item">
                            <div class="thermo-title">Bearing NDE</div>
                            <div id="thermo-nde"></div>
                            <div class="data-display" id="val-nde">-- °C</div>
                        </div>

                        <div class="thermo-item">
                            <div class="thermo-title">Suhu Ruangan</div>
                            <div id="thermo-winding"></div>
                            <div class="data-display" id="val-winding">-- °C</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>