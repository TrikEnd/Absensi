<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['role']!='dosen'){ header("Location: login.php"); exit; }
$host="localhost"; $user="root"; $pass=""; $db="praktikum";
$conn=new mysqli($host,$user,$pass,$db);

$kelas_id=$_GET['kelas_id'];
$result=$conn->query("SELECT a.*, m.nama FROM absensi a JOIN mahasiswa m ON a.nim=m.nim WHERE kelas_id=$kelas_id ORDER BY tanggal DESC");

header('Content-Type: text/html; charset=utf-8');
echo "<h3>Rekap Absensi Kelas $kelas_id</h3>";
echo "<a href='export.php?kelas_id=$kelas_id'>Export CSV</a><br><br>";
echo "<table border=1 cellpadding=5><tr><th>NIM</th><th>Nama</th><th>Tanggal</th><th>Status</th></tr>";
while($row=$result->fetch_assoc()){
    echo "<tr><td>{$row['nim']}</td><td>{$row['nama']}</td><td>{$row['tanggal']}</td><td>{$row['hadir']}</td></tr>";
}
echo "</table>";
