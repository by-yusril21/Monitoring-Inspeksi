<?php
session_start();
$delete = false;
$deleteTerminal = false;
$reset_id = false;

if (!isset($_SESSION['username'])) {
  echo "<script> location.href='login.php'; </script>";
  exit;
}

include "config/database.php";
include "inc/header.php";
include "inc/navbar.php";
include "inc/sidebar.php";
include "inc/alerts.php";


if (isset($_GET['page']) && file_exists(filename: "page/" . $_GET['page'] . ".php")) {
  include "page/" . $_GET['page'] . ".php";
} else {
  include "page/home.php";
}
?>

<script>
  // Di sinilah Anda paste kode Base64 yang sangat panjang itu
</script>
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/chart.js/Chart.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/pdfmake/pdfmake.min.js"></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="plugins/toastr/toastr.min.js"></script>
<script src="plugins/d3-min/d3.min.js"></script>
<script src="plugins/VibeTube/VibeTube.js"></script>
<script src="plugins/toastr/toastr.min.js"></script>
<script src="plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="plugins/d3-min/d3.min.js"></script>



<script src="config/config_motor.php?v=<?php echo time(); ?>"></script>

<script src="dist/js/adminlte.min.js"></script>
<script src="dist/js/tabel-motor.js"></script>
<script src="dist/js/inputData-motor.js"></script>
<script src="dist/js/echarts.min.js"></script>
<script src="dist/js/gauge-motor.js"></script>
<script src="dist/js/termometer-motor.js"></script>
<script src="dist/js/kondisi-motor.js"></script>
<script src="dist/js/navbar.js"></script>
<script src="dist/js/action-motor.js"></script>
<script src="dist/js/jadwal-regreasing.js"></script>



<?php if ($delete)
  echo "<script>toastr.success('Data berhasil dihapus.');</script>"; ?>