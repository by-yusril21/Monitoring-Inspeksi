<?php
if ($_SESSION['role'] != "admin") {
  echo "<script> location.href='index.php'; </script>";
  exit;
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
  <div class="content-header pb-1">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h4 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-users-cog mr-2"></i>Manajemen Pengguna
          </h4>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="container-fluid">

      <?php if ($insert == true): ?>
        <?php alertsSuccess("Data Berhasil Di Tambahkan"); ?>
      <?php endif; ?>

      <div class="row">

        <div class="col-lg-7 col-md-12 mb-4">
          <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header py-2">
              <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;">
                Daftar Pengguna Terdaftar
              </h3>
            </div>
            <div class="card-body text-sm p-3">
              <div class="table-responsive">
                <table id="tabelUser" class="table table-bordered table-hover table-striped table-sm text-nowrap"
                  style="width: 100%;">
                  <thead class="bg-light">
                    <tr>
                      <th>Username</th>
                      <th>Nama Lengkap</th>
                      <th class="text-center">Hak Akses</th>
                      <th class="text-center">Status</th>
                      <th class="text-center" style="width: 1%;">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                      <tr>
                        <td class="align-middle font-weight-bold text-primary">
                          <?php echo $row['username'] ?>
                        </td>
                        <td class="align-middle">
                          <?php echo $row['fullname'] ?>
                        </td>
                        <td class="align-middle text-center">
                          <?php if ($row['role'] == 'admin'): ?>
                            <span class="badge badge-danger"><i class="fas fa-user-shield mr-1"></i>Admin</span>
                          <?php else: ?>
                            <span class="badge badge-info"><i class="fas fa-user mr-1"></i>User</span>
                          <?php endif; ?>
                        </td>
                        <td class="align-middle text-center">
                          <?php if ($row['active'] == 'Yes'): ?>
                            <span class="badge badge-success">Aktif</span>
                          <?php else: ?>
                            <span class="badge badge-secondary">Tidak Aktif</span>
                          <?php endif; ?>
                        </td>
                        <td class="align-middle text-center">
                          <a href="?page=<?php echo $page ?>&edit=<?php echo $row['username'] ?>"
                            class="btn btn-sm btn-warning shadow-sm" title="Edit Data">
                            <i class="fas fa-edit"></i> Edit
                          </a>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-5 col-md-12">
          <?php if (!isset($_GET['edit'])) { ?>
            <div class="card card-outline card-success shadow-sm">
              <div class="card-header py-2">
                <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;">
                  <i class="fas fa-user-plus mr-1"></i> Tambah Pengguna
                </h3>
              </div>
              <form method="post" action="?page=<?php echo $page ?>">
                <div class="card-body text-sm p-3">
                  <div class="form-group mb-2">
                    <label class="mb-1">Username <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-at"></i></span>
                      </div>
                      <input type="text" class="form-control form-control-sm" name="username"
                        placeholder="Buat username baru" required>
                    </div>
                  </div>
                  <div class="form-group mb-2">
                    <label class="mb-1">Password <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span>
                      </div>
                      <input type="password" class="form-control form-control-sm" name="password"
                        placeholder="Buat password" required>
                    </div>
                  </div>
                  <div class="form-group mb-2">
                    <label class="mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i
                            class="fas fa-id-card"></i></span></div>
                      <input type="text" class="form-control form-control-sm" name="fullname"
                        placeholder="Nama asli pengguna" required>
                    </div>
                  </div>
                  <div class="form-group mb-2">
                    <label class="mb-1">Hak Akses <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i
                            class="fas fa-users-cog"></i></span></div>
                      <select class="custom-select custom-select-sm" name="role" required>
                        <option value="" disabled selected>-- Pilih Hak Akses --</option>
                        <option value="User">User / Pengguna Biasa</option>
                        <option value="admin">Admin / Administrator</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="card-footer bg-light py-2 px-3">
                  <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Data
                  </button>
                </div>
              </form>
            </div>

          <?php } else { ?>
            <div class="card card-outline card-warning shadow-sm">
              <div class="card-header py-2">
                <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;">
                  <i class="fas fa-user-edit mr-1"></i> Ubah Data Pengguna
                </h3>
              </div>
              <form method="post" action="?page=<?php echo $page ?>">
                <div class="card-body text-sm p-3">
                  <input type="hidden" name="edit_data" value="<?php echo $data['username'] ?>">
                  <div class="form-group mb-2">
                    <label class="mb-1">Username</label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-at"></i></span>
                      </div>
                      <input type="text" class="form-control form-control-sm" name="username"
                        value="<?php echo $data['username'] ?>" required>
                    </div>
                  </div>
                  <div class="form-group mb-2">
                    <label class="mb-1">Password Baru</label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-key"></i></span>
                      </div>
                      <input type="password" class="form-control form-control-sm" name="password"
                        placeholder="(Kosongkan jika tidak diubah)">
                    </div>
                    <small class="text-muted">Hanya isi jika ingin mengganti password.</small>
                  </div>
                  <div class="form-group mb-2">
                    <label class="mb-1">Nama Lengkap</label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i
                            class="fas fa-id-card"></i></span></div>
                      <input type="text" class="form-control form-control-sm" name="fullname"
                        value="<?php echo $data['fullname'] ?>" required>
                    </div>
                  </div>
                  <div class="form-group mb-2">
                    <label class="mb-1">Hak Akses</label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i
                            class="fas fa-users-cog"></i></span></div>
                      <select class="custom-select custom-select-sm" name="role">
                        <option value="admin" <?php echo ($data['role'] == 'admin') ? 'selected' : ''; ?>>Admin /
                          Administrator</option>
                        <option value="User" <?php echo ($data['role'] == 'User') ? 'selected' : ''; ?>>User / Pengguna
                          Biasa</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group mb-2">
                    <label class="mb-1">Status Akun</label>
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend"><span class="input-group-text"><i
                            class="fas fa-toggle-on"></i></span></div>
                      <select class="custom-select custom-select-sm" name="active">
                        <option value="Yes" <?php echo ($data['active'] == 'Yes') ? 'selected' : ''; ?>>Aktif (Bisa Login)
                        </option>
                        <option value="No" <?php echo ($data['active'] == 'No') ? 'selected' : ''; ?>>Tidak Aktif
                          (Diblokir)</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="card-footer bg-light py-2 px-3">
                  <button type="submit" class="btn btn-warning btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                  </button>
                  <a href="?page=<?php echo $page ?>" class="btn btn-default btn-sm shadow-sm ml-2">Batal</a>
                </div>
              </form>
            </div>
          <?php } ?>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    if ($('#tabelUser').length && $.fn.DataTable) {
      $('#tabelUser').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": false,
        "info": true,
        "autoWidth": false,
        "responsive": false, // Dimatikan karena kita sudah pakai div.table-responsive yang lebih bagus untuk HP
        "scrollX": false,
        "language": {
          "search": "",
          "searchPlaceholder": "Cari data..."
        }
      });
    }
  });
</script>