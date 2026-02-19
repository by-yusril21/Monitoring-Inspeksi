<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">

                                <div class="col-6 col-md-3 text-center">
                                    <input type="text" class="knob-max-380" value="0" id="voltvalue" readonly>
                                    <div class="knob-label">VOLT</div>
                                </div>

                                <div class="col-6 col-md-3 text-center">
                                    <input type="text" class="knob-max-100" value="0" id="hzvalue" readonly>
                                    <div class="knob-label">Hz</div>
                                </div>

                                <div class="col-6 col-md-3 text-center">
                                    <input type="text" class="knob-max-50" value="0" id="arusvalue" readonly>
                                    <div class="knob-label">ARUS</div>
                                </div>

                                <div class="col-6 col-md-3 text-center">
                                    <input type="text" class="knob-max-2000" value="0" id="powervalue" readonly>
                                    <div class="knob-label">POWER</div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">

                                <div class="col-12 col-md-3 text-center">
                                    <div class="card card-secondary">
                                        <div class="card-header">
                                            <h3 class="card-title">OUTPUT A</h3>
                                        </div>

                                        <div class="card-body table-responsive pad">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-secondary" id="ch-outA-1">
                                                    <input type="radio" name="outA" onchange="publishoutA()" id="outA-1"
                                                        autocomplete="off">NYALA
                                                </label>
                                                <label class="btn btn-secondary" id="ch-outA-0">
                                                    <input type="radio" name="outA" onchange="publishoutA()" id="outA-0"
                                                        autocomplete="off">MATI
                                                </label>
                                                <label class="btn btn">
                                                    <i class="fas fa-power-off" id="iconA1"></i>
                                                    <!-- <i class="fas fa-bolt iconA" id="iconA1"></i> -->
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3 text-center">
                                    <div class="card card-secondary">
                                        <div class="card-header">
                                            <h3 class="card-title">OUTPUT B</h3>
                                        </div>
                                        <div class="card-body table-responsive pad">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-secondary" id="ch-outB-1">
                                                    <input type="radio" name="outB" onchange="publishoutB()" id="outB-1"
                                                        autocomplete="off">NYALA
                                                </label>
                                                <label class="btn btn-secondary" id="ch-outB-0">
                                                    <input type="radio" name="outB" onchange="publishoutB()" id="outB-0"
                                                        autocomplete="off">MATI
                                                </label>
                                                <label class="btn btn">
                                                    <i class="fas fa-power-off" id="iconB1"></i>
                                                    <!-- <i class="fas fa-bolt iconB" id="iconB1"></i> -->
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3 text-center">
                                    <div class="card card-secondary">
                                        <div class="card-header">
                                            <h3 class="card-title">OUTPUT C</h3>
                                        </div>
                                        <div class="card-body table-responsive pad">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-secondary" id="ch-outC-1">
                                                    <input type="radio" name="outC" onchange="publishoutC()" id="outC-1"
                                                        autocomplete="off">NYALA
                                                </label>
                                                <label class="btn btn-secondary" id="ch-outC-0">
                                                    <input type="radio" name="outC" onchange="publishoutC()" id="outC-0"
                                                        autocomplete="off">MATI
                                                </label>
                                                <label class="btn btn">
                                                    <i class="fas fa-power-off" id="iconC1"></i>
                                                    <!-- <i class="fas fa-bolt iconC" id="iconC1"></i> -->
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3 text-center">
                                    <div class="card card-secondary">
                                        <div class="card-header">
                                            <h3 class="card-title">OUTPUT D</h3>
                                        </div>
                                        <div class="card-body table-responsive pad">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-secondary" id="ch-outD-1">
                                                    <input type="radio" name="outD" onchange="publishoutD()" id="outD-1"
                                                        autocomplete="off">NYALA
                                                </label>
                                                <label class="btn btn-secondary" id="ch-outD-0">
                                                    <input type="radio" name="outD" onchange="publishoutD()" id="outD-0"
                                                        autocomplete="off">MATI
                                                </label>
                                                <label class="btn btn">
                                                    <i class="fas fa-power-off" id="iconD1"></i>
                                                    <!-- <i class="fas fa-bolt iconD" id="iconD1"></i> -->
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3 text-center">
                                    <div class="card card-secondary">
                                        <div class="card-header">
                                            <h3 class="card-title">MASTER OUT</h3>
                                        </div>
                                        <div class="card-body table-responsive pad">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-secondary" id="ch-outM-1">
                                                    <input type="radio" name="masterOut" onchange="publishMasterOut()"
                                                        id="outM-1" autocomplete="off">NYALA
                                                </label>
                                                <label class="btn btn-secondary" id="ch-outM-0">
                                                    <input type="radio" name="masterOut" onchange="publishMasterOut()"
                                                        id="outM-0" autocomplete="off">MATI
                                                </label>
                                                <label class="btn btn">
                                                    <i class="fas fa-bolt iconMaster" id="iconMaster"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-md-6">

                            <div class="card card-primary card-outline">
                                <div class="card-header bg-primary">
                                    <h3 class="card-title">
                                        <i class="far fa-chart-bar"></i>
                                        VOLTAGE
                                    </h3>

                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="chart-voltage" style="height:300px;"></div>
                                </div>
                            </div>

                            <div class="card card-success card-outline">
                                <div class="card-header bg-success">
                                    <h3 class="card-title">
                                        <i class="far fa-chart-bar"></i>
                                        FREQUENCY
                                    </h3>

                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="chart-frequency" style="height:300px;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-danger card-outline">
                                <div class="card-header bg-danger">
                                    <h3 class="card-title">
                                        <i class="far fa-chart-bar"></i>
                                        CURRENT
                                    </h3>

                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="chart-amper" style="height:300px;"></div>
                                </div>
                            </div>

                            <div class="card card-warning card-outline">
                                <div class="card-header bg-warning">
                                    <h3 class="card-title">
                                        <i class="far fa-chart-bar"></i>
                                        DAYA
                                    </h3>

                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="chart-power" style="height:300px;"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /.row -->
                </div><!-- /.container-fluid -->
            </section>


            <div class="row">
                <!--STATUS -->
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Status Perangkat</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0" style="height: 300px;">
                            <table class="table table-head-fixed text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Serial number</th>
                                        <th>location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['serial_number'] ?></td>
                                            <td><?php echo $row['location'] ?></td>
                                            <td style="color:red;"
                                                id="panelsound/status/<?php echo $row['serial_number'] ?>">Offline</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col-md-6 -->
            </div>

            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>