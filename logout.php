<?php
session_start();
session_destroy();
header("Location: login_mahasiswa.php"); // default ke login mahasiswa
exit;
?>
