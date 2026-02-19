<?php
session_abort();
session_destroy();
echo "<script> location.href='login.php'; </script>";
