<?php
$page = $_GET['page'];
$insert = false;

if (isset($_POST['edit_data'])) {
  $old_id = $_POST['edit_data'];
  $serial_number = $_POST['serial_number'];
  $controller_type = $_POST['controller'];
  $location = $_POST['location'];
  $avtive = $_POST['active'];

  $sql_edit = "UPDATE devices SET serial_number = '$serial_number', mcu_type = '$controller_type', location = '$location', active = '$avtive' WHERE serial_number = '$old_id'";
  mysqli_query($conn, $sql_edit);

} else if (isset($_POST['serial_number'])) {
  $serial_number = $_POST['serial_number'];
  $controller_type = $_POST['controller'];
  $location = $_POST['location'];

  $sql_insert = "INSERT INTO devices(serial_number, mcu_type, location) VALUES ('$serial_number', '$controller_type', '$location')";
  mysqli_query($conn, $sql_insert);
  $insert = true;
}

if (isset($_GET['edit'])) {
  $id = $_GET['edit'];
  $sql_select_data = "SELECT * FROM devices WHERE serial_number = '$id' LIMIT 1";

  $result = mysqli_query($conn, $sql_select_data);
  $data = mysqli_fetch_assoc($result);
}


$sql = "SELECT * FROM devices";
$result = mysqli_query($conn, $sql)
  ?>

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">perangkat</h1>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12">
          
          <?php if ($insert == true) {
            alertsSuccess("Data Berhasil Di Tambahkan");
          } ?>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">perangkat yang terdaftar</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Serial Number</th>
                    <th>Tipe kontroler</th>
                    <th>Lokasi</th>
                    <th>Waktu Di Daftarkan</th>
                    <th>Aktif</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                      <td><?php echo $row['serial_number'] ?></td>
                      <td><?php echo $row['mcu_type'] ?></td>
                      <td><?php echo $row['location'] ?></td>
                      <td><?php echo $row['created_time'] ?></td>
                      <td><?php echo $row['active'] ?></td>
                      <td><a href="?page=<?php echo $page ?>&edit=<?php echo $row['serial_number'] ?>"><i
                            class="far fa-edit"></i></a></td>
                    </tr>
                  <?php } ?>
                  </tfoot>
              </table>
            </div>
            <!-- /.card-body -->
          </div>
          <?php if (!isset($_GET['edit'])) { ?>
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Tambah Data</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="?page=<?php echo $page ?>">
                <div class="card-body">
                  <div class="form-group">
                    <label>Serial Number</label>
                    <input type="text" class="form-control" name="serial_number" placeholder="Serial Number" required>
                  </div>
                  <div class="form-group">
                    <label>Jenis Kontroler</label>
                    <input type="text" class="form-control" name="controller" required>
                  </div>
                  <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" class="form-control" name="location" required>
                  </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
              </form>
            </div>
          <?php } else { ?>
            <div class="card card-warning">
              <div class="card-header">
                <h3 class="card-title">Ubah Data</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="?page=<?php echo $page ?>">
                <div class="card-body">
                  <div class="form-group">
                    <label>Serial Number</label>
                    <input type="hidden" name="edit_data" value="<?php echo $data['serial_number'] ?>">
                    <input type="text" class="form-control" name="serial_number"
                      value="<?php echo $data['serial_number'] ?>" placeholder="Serial Number" required>
                  </div>
                  <div class="form-group">
                    <label>Jenis Kontroler</label>
                    <input type="text" class="form-control" name="controller" value="<?php echo $data['mcu_type'] ?>"
                      required>
                  </div>
                  <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" class="form-control" name="location" value="<?php echo $data['location'] ?>"
                      required>
                  </div>
                  <div class="form-group">
                    <label>Status</label>
                    <div class="input-grup">
                      <select class="form-control" name="active">
                        <?php if ($data['active'] == "Yes") { ?>
                          <option value="Yes">aktif</option>
                          <option value="No">tidak aktif</option>
                        <?php } else { ?>
                          <option value="No">tidak aktif</option>
                          <option value="Yes">aktif</option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                  <button type="submit" class="btn btn-warning">Ubah</button>
                </div>
              </form>
            </div>
          <?php } ?>
        </div>
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content -->
</div>