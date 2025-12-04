<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['role']!='dosen'){ header("Location: login.php"); exit; }
$host="localhost"; $user="root"; $pass=""; $db="praktikum";
$conn=new mysqli($host,$user,$pass,$db);

$kelas_id=$_GET['kelas_id'];
$mahasiswa=$conn->query("SELECT * FROM mahasiswa"); // bisa filter berdasarkan kelas jika ada relasi

if(isset($_POST['submit'])){
    $nim=$_POST['nim']; $tanggal=$_POST['tanggal']; $hadir=$_POST['hadir'];
    $stmt=$conn->prepare("INSERT INTO absensi (kelas_id,nim,tanggal,hadir) VALUES (?,?,?,?)");
    $stmt->bind_param("isss",$kelas_id,$nim,$tanggal,$hadir);
    $stmt->execute(); $stmt->close();
    echo "Absensi disimpan.";
}
?>
<h3>Absensi Kelas <?= $kelas_id ?></h3>
<form method="POST">
    Mahasiswa:
    <select name="nim">
        <?php while($m=$mahasiswa->fetch_assoc()): ?>
        <option value="<?= $m['nim'] ?>"><?= $m['nim'] ?> - <?= $m['nama'] ?></option>
        <?php endwhile; ?>
    </select><br>
    Tanggal: <input type="date" name="tanggal" required><br>
    Status: 
    <select name="hadir">
        <option value="Hadir">Hadir</option>
        <option value="Tidak Hadir">Tidak Hadir</option>
    </select><br>
    <button type="submit" name="submit">Simpan</button>
</form>
<a href="dashboard.php">Kembali</a>
