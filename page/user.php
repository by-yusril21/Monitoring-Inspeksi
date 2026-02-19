<?php
if ($_SESSION['role'] != "admin") {
  echo "<script> location.href='index.php'; </script>";
}

$page = $_GET['page'];
$insert = false;

if (isset($_POST['edit_data'])) {
  $old_id = $_POST['edit_data'];

  $username = $_POST['username'];
  $fullname = $_POST['fullname'];
  $role = $_POST['role'];
  $active = $_POST['active'];

  if ($_POST['password'] == "") {
    $sql_edit = "UPDATE user SET username = '$username', fullname = '$fullname', role = '$role', active = '$active' WHERE username = '$old_id'";
  } else {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $sql_edit = "UPDATE user SET username = '$username', password = '$password', fullname = '$fullname', role = '$role', active = '$active' WHERE username = '$old_id'";
  }


  mysqli_query($conn, $sql_edit);

} else if (isset($_POST['username'])) {
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $fullname = $_POST['fullname'];
  $role = $_POST['role'];

  $sql_insert = "INSERT INTO user(username, password, fullname, role) VALUES ('$username', '$password', '$fullname', '$role')";
  mysqli_query($conn, $sql_insert);
  $insert = true;
}

if (isset($_GET['edit'])) {
  $id = $_GET['edit'];
  $sql_select_data = "SELECT * FROM user WHERE username = '$id' LIMIT 1";

  $result = mysqli_query($conn, $sql_select_data);
  $data = mysqli_fetch_assoc($result);
}


$sql = "SELECT * FROM user";
$result = mysqli_query($conn, $sql)
  ?>

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Pengguna</h1>
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
          <?php
          if ($insert == true) {
            alertsSuccess("Data Berhasil Di Tambahkan");
          }

          ?>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Pengguna yang terdaftar</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>Hak Akses</th>
                    <th>Aktif</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                      <td><?php echo $row['username'] ?></td>
                      <td><?php echo $row['fullname'] ?></td>
                      <td><?php echo $row['role'] ?></td>
                      <td><?php echo $row['active'] ?></td>
                      <td><a href="?page=<?php echo $page ?>&edit=<?php echo $row['username'] ?>"><i
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
                    <label>Username</label>
                    <input type="text" class="form-control" name="username" placeholder="Username tidak boleh sama"
                      required>
                  </div>
                  <div class="form-group">
                    <label>password</label>
                    <input type="password" class="form-control" name="password" placeholder="password tidak boleh sama"
                      required>
                  </div>
                  <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" class="form-control" name="fullname" required>
                  </div>
                  <div class="form-group">
                    <label>Hak Akses</label>
                    <div class="input-group">
                      <select class="form-control" name="role">
                        <option value="admin">Admin</option>
                        <option value="User">pengguna</option>
                      </select>
                    </div>
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
                    <label>Username</label>
                    <input type="hidden" name="edit_data" value="<?php echo $data['username'] ?>">
                    <input type="text" class="form-control" name="username" value="<?php echo $data['username'] ?>"
                      placeholder="Username tidak boleh sama" required>
                  </div>
                  <div class="form-group">
                    <label>password</label>
                    <input type="password" class="form-control" name="password"
                      placeholder="kosongkan jika tidak ingin merubah password">
                  </div>
                  <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" class="form-control" name="fullname" value="<?php echo $data['fullname'] ?>"
                      required>
                  </div>
                  <div class="form-group">
                    <label>Hak Akses</label>
                    <div class="input-grup">
                      <select class="form-control" name="role">
                        <?php if ($data['role'] == "admin") { ?>
                          <option value="admin">Admin</option>
                          <option value="User">pengguna</option>
                        <?php } else { ?>
                          <option value="User">pengguna</option>
                          <option value="admin">Admin</option>
                        <?php } ?>
                      </select>
                    </div>
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