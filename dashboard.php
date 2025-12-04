<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['role']!='dosen') { header("Location: login.php"); exit; }
$host="localhost"; $user="root"; $pass=""; $db="praktikum";
$conn=new mysqli($host,$user,$pass,$db);

$dosen_id=$_SESSION['user']['id'];
$kelas=$conn->query("SELECT * FROM kelas WHERE dosen_id=$dosen_id");
?>

<h2>Selamat Datang, <?= $_SESSION['user']['nama'] ?></h2>
<a href="tambah_kelas.php">Tambah Kelas</a> | <a href="logout.php">Logout</a>
<h3>Daftar Kelas</h3>
<ul>
<?php while($row=$kelas->fetch_assoc()): ?>
    <li>
        <?= $row['nama_kelas'] ?> - 
        <a href="absensi.php?kelas_id=<?= $row['id'] ?>">Absensi</a> | 
        <a href="rekap.php?kelas_id=<?= $row['id'] ?>">Rekap</a>
    </li>
<?php endwhile; ?>
</ul>
