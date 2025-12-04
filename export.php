<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['role']!='dosen'){ header("Location: login.php"); exit; }
$host="localhost"; $user="root"; $pass=""; $db="praktikum";
$conn=new mysqli($host,$user,$pass,$db);

$kelas_id=$_GET['kelas_id'];
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=absensi_kelas_'.$kelas_id.'.csv');

$output=fopen('php://output','w');
fputcsv($output,['NIM','Nama','Tanggal','Status']);

$result=$conn->query("SELECT a.*, m.nama FROM absensi a JOIN mahasiswa m ON a.nim=m.nim WHERE kelas_id=$kelas_id ORDER BY tanggal DESC");
while($row=$result->fetch_assoc()){
    fputcsv($output, [$row['nim'],$row['nama'],$row['tanggal'],$row['hadir']]);
}
fclose($output);
