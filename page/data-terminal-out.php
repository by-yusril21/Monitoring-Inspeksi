<?php
$deleteTerminal = false;

if (isset($_POST['delete_data'])) {
  // Hapus data dengan nilai 'sensor'
  $sql_delete = "DELETE FROM data_sensor WHERE sensor_relay1 = 'relay1'";
  if (mysqli_query($conn, $sql_delete)) {
    // Reset ID auto-increment ke 1 setelah penghapusan data
    $sql_reset = "ALTER TABLE data_sensor AUTO_INCREMENT = 1";
    mysqli_query($conn, $sql_reset);
    $deleteTerminal = true;
  } else {
    echo "Terjadi kesalahan: " . $conn->error;
  }
}

// Cek apakah tombol hapus ditekan
if (isset($_POST['reset-id'])) {
  $sql_truncate = "TRUNCATE TABLE data_sensor";
  mysqli_query($conn, $sql_truncate);
  if ($conn->query($sql_truncate) === TRUE) {
    $reset_id = true;
  } else {
    echo "Terjadi kesalahan: " . $conn->error;
  }
}

$sql = "SELECT * FROM data_sensor WHERE sensor_relay1 = 'relay1'";
$result = mysqli_query($conn, $sql);
?>

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Data Terminal Output</h1>
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
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Riwayat Terminal Out</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Serial Number</th>
                    <th>Name</th>
                    <th>Nilai</th>
                    <th>Topic</th>
                    <th>Waktu</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                      <td><?php echo $row['id'] ?></td>
                      <td><?php echo $row['serial_number'] ?></td>
                      <td><?php echo $row['name'] ?></td>
                      <td><?php echo $row['value'] ?></td>
                      <td><?php echo $row['mqtt_topic'] ?></td>
                      <td><?php echo $row['time'] ?></td>
                    </tr>
                  <?php } ?>
                  </tfoot>
              </table>
            </div>
            <!-- /.card-body -->
            <div class="col-lg-12">
              <div class="card-body">
                <form method="post" action="" class="d-flex">
                  <div class="mr-3">
                    <button type="submit" name="delete_data" class="btn btn-primary">Hapus Data terminal</button>
                  </div>
                  <div>
                    <button type="submit" name="reset-id" class="btn btn-primary">Reset Data</button>
                  </div>
                </form>
                <div>
                  <p1><b>Hapus Data Sensor</b> : hanya menghapus data terminal</p1><br><p1><b>Reset Data</b> : Menghapus semua data sensor-Treminal dan ID di kembalikan ke 0</p1>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content -->
</div>