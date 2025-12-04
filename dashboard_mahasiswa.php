<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'mahasiswa'){
    header("Location: login_mahasiswa.php"); exit;
}

$conn = new mysqli("localhost","root","","praktikum");
$nim = $_SESSION['user']['nim'];
$msg = "";

// ================= Registrasi & Hapus Kelas =================
if(isset($_POST['daftar'])){
    $kelas_id = $_POST['kelas_id'];
    $stmt = $conn->prepare("INSERT INTO registrasi (nim, kelas_id) VALUES (?, ?)");
    $stmt->bind_param("si",$nim,$kelas_id);
    if($stmt->execute()) $msg="Berhasil daftar kelas!";
    else $msg="Gagal daftar kelas (mungkin sudah terdaftar).";
}

if(isset($_POST['hapus'])){
    $kelas_id = $_POST['kelas_id'];
    $stmt = $conn->prepare("DELETE FROM registrasi WHERE nim=? AND kelas_id=?");
    $stmt->bind_param("si",$nim,$kelas_id);
    $stmt->execute();
    $msg="Kelas berhasil dihapus.";
}

// ================= Absen Mandiri =================
if(isset($_POST['absen'])){
    $sesi_id = $_POST['sesi_id'];
    $stmt = $conn->prepare("INSERT INTO absensi (sesi_id,nim,status) VALUES (?, ?, 'Hadir')");
    $stmt->bind_param("is",$sesi_id,$nim);
    if($stmt->execute()) $msg="Absen berhasil!";
    else $msg="Anda sudah absen di sesi ini.";
}

// ================= Ambil Data =================
$kelas_all = $conn->query("SELECT * FROM kelas");
$kelas_saya = $conn->query("SELECT r.kelas_id, k.nama_kelas FROM registrasi r JOIN kelas k ON r.kelas_id=k.id WHERE r.nim='$nim'");
$menu = isset($_GET['menu']) ? $_GET['menu'] : 'kelas_saya';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Mahasiswa</title>
    <style>
        body { font-family: Arial; margin:0; padding:0; }
        .header { background:#0077cc; color:white; padding:15px; }
        .sidebar { width:200px; float:left; background:#f1f1f1; height:100vh; padding-top:20px; }
        .sidebar a { display:block; padding:10px; text-decoration:none; color:black; margin:5px; }
        .sidebar a:hover { background:#ddd; }
        .content { margin-left:210px; padding:20px; }
        table { border-collapse: collapse; width:100%; }
        table, th, td { border:1px solid black; padding:8px; text-align:left; }
        .msg { color:green; }
        button { cursor:pointer; }
    </style>
</head>
<body>
<div class="header">
    Halo, <?= $_SESSION['user']['nama'] ?> | <a href="logout.php" style="color:white;">Logout</a>
</div>

<div class="sidebar">
    <a href="?menu=kelas_saya">Daftar Kelas Saya</a>
    <a href="?menu=registrasi">Registrasi Kelas</a>
    <a href="?menu=sesi_absensi">Sesi Absensi</a>
    <a href="?menu=rekap_absensi">Rekap Absensi</a>
</div>

<div class="content">
<?php if($msg!="") echo "<p class='msg'>$msg</p>"; ?>

<?php if($menu=='kelas_saya'): ?>
<h3>Daftar Kelas Saya</h3>
<table>
<tr><th>Kelas</th><th>Aksi</th></tr>
<?php while($row=$kelas_saya->fetch_assoc()): ?>
<tr>
    <td><?= $row['nama_kelas'] ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="kelas_id" value="<?= $row['kelas_id'] ?>">
            <button type="submit" name="hapus">Hapus</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php elseif($menu=='registrasi'): ?>
<h3>Registrasi Kelas Baru</h3>
<form method="POST">
    <select name="kelas_id" required>
        <?php while($row=$kelas_all->fetch_assoc()): ?>
        <option value="<?= $row['id'] ?>"><?= $row['nama_kelas'] ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit" name="daftar">Daftar Kelas</button>
</form>

<?php elseif($menu=='sesi_absensi'): ?>
<h3>Pilih Kelas untuk Absen</h3>
<ul>
<?php
$kelas_saya = $conn->query("SELECT r.kelas_id, k.nama_kelas FROM registrasi r JOIN kelas k ON r.kelas_id=k.id WHERE r.nim='$nim'");
while($row=$kelas_saya->fetch_assoc()): ?>
    <li><a href="?menu=sesi_kelas&kelas_id=<?= $row['kelas_id'] ?>"><?= $row['nama_kelas'] ?></a></li>
<?php endwhile; ?>
</ul>

<?php elseif($menu=='sesi_kelas' && isset($_GET['kelas_id'])): ?>
<?php
$kelas_id = $_GET['kelas_id'];
$kelas = $conn->query("SELECT * FROM kelas WHERE id=$kelas_id")->fetch_assoc();
$sesi = $conn->query("SELECT * FROM sesi_absensi WHERE kelas_id=$kelas_id ORDER BY tanggal DESC");
?>
<h3>Sesi Absensi Kelas: <?= $kelas['nama_kelas'] ?></h3>
<table>
<tr><th>Keterangan</th><th>Tanggal</th><th>Aksi</th></tr>
<?php while($row=$sesi->fetch_assoc()):
    $cek = $conn->query("SELECT * FROM absensi WHERE sesi_id=".$row['id']." AND nim='$nim'");
if($cek->num_rows>0){
    $data = $cek->fetch_assoc();
    $btn = "Sudah Absen<br>Waktu: ".$data['waktu_absen'];
} else {
    $btn = "<form method='POST'>
                <input type='hidden' name='sesi_id' value='".$row['id']."'>
                <button type='submit' name='absen'>Absen</button>
            </form>";
}

?>
<tr>
    <td><?= $row['keterangan'] ?></td>
    <td><?= $row['tanggal'] ?></td>
    <td><?= $btn ?></td>
</tr>
<?php endwhile; ?>
</table>

<?php elseif($menu=='rekap_absensi'): ?>
<h3>Rekap Absensi</h3>
<?php
$absensi = $conn->query("
    SELECT a.*, s.keterangan, k.nama_kelas 
    FROM absensi a
    JOIN sesi_absensi s ON a.sesi_id=s.id
    JOIN kelas k ON k.id=s.kelas_id
    WHERE a.nim='$nim'
    ORDER BY s.tanggal DESC
");
?>
<table>
<tr><th>Kelas</th><th>Keterangan</th><th>Tanggal</th><th>Status</th></tr>
<?php while($row=$absensi->fetch_assoc()): ?>
<tr>
    <td><?= $row['nama_kelas'] ?></td>
    <td><?= $row['keterangan'] ?></td>
    <td><?= $row['tanggal'] ?></td>
    <td><?= $row['status'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

</div>
</body>
</html>
