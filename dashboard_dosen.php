<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'dosen'){
    header("Location: login_dosen.php"); exit;
}

$conn = new mysqli("localhost","root","","praktikum");
$dosen_id = $_SESSION['user']['id'];

// ================= Tambah Kelas =================
if(isset($_POST['tambah_kelas'])){
    $nama_kelas = $_POST['nama_kelas'];
    $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, dosen_id) VALUES (?, ?)");
    $stmt->bind_param("si",$nama_kelas,$dosen_id);
    $stmt->execute();
    $msg="Kelas berhasil ditambahkan!";
}

// ================= Buat Sesi Absensi =================
if(isset($_POST['buat_sesi'])){
    $kelas_id = $_POST['kelas_id'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];

    $cek = $conn->query("SELECT * FROM kelas WHERE id=$kelas_id AND dosen_id=$dosen_id");
    if($cek->num_rows>0){
        $stmt = $conn->prepare("INSERT INTO sesi_absensi (kelas_id,tanggal,keterangan) VALUES (?,?,?)");
        $stmt->bind_param("iss",$kelas_id,$tanggal,$keterangan);
        $stmt->execute();
        $msg="Sesi absensi berhasil dibuat!";
    } else { $msg="Kelas bukan milik Anda!"; }
}

// Ambil data kelas milik dosen
$kelas_saya = $conn->query("SELECT * FROM kelas WHERE dosen_id=$dosen_id");

// Ambil menu aktif
$menu = isset($_GET['menu']) ? $_GET['menu'] : 'kelas_saya';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Dosen</title>
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
    </style>
</head>
<body>
<div class="header">
    Halo, <?= $_SESSION['user']['nama'] ?> (Dosen) | <a href="logout.php" style="color:white;">Logout</a>
</div>

<div class="sidebar">
    <a href="?menu=kelas_saya">Daftar Kelas</a>
    <a href="?menu=tambah_kelas">Tambah Kelas</a>
    <a href="?menu=buat_sesi">Buat Sesi Absensi</a>
    <a href="?menu=rekap_absensi">Rekap Absensi</a>
</div>

<div class="content">
<?php if(isset($msg)) echo "<p class='msg'>$msg</p>"; ?>

<?php if($menu=='kelas_saya'): ?>
<h3>Daftar Kelas Saya</h3>
<table>
<tr><th>Kelas</th><th>Aksi</th></tr>
<?php while($row=$kelas_saya->fetch_assoc()): ?>
<tr>
    <td><?= $row['nama_kelas'] ?></td>
    <td>-</td>
</tr>
<?php endwhile; ?>
</table>

<?php elseif($menu=='tambah_kelas'): ?>
<h3>Tambah Kelas Baru</h3>
<form method="POST">
    Nama Kelas: <input type="text" name="nama_kelas" required>
    <button type="submit" name="tambah_kelas">Tambah</button>
</form>

<?php elseif($menu=='buat_sesi'): ?>
<h3>Buat Sesi Absensi</h3>
<form method="POST">
    Pilih Kelas: 
    <select name="kelas_id" required>
        <?php
        $kelas_saya2 = $conn->query("SELECT * FROM kelas WHERE dosen_id=$dosen_id");
        while($row=$kelas_saya2->fetch_assoc()){
            echo "<option value='".$row['id']."'>".$row['nama_kelas']."</option>";
        }
        ?>
    </select><br><br>
    Tanggal: <input type="date" name="tanggal" required><br><br>
    Keterangan: <input type="text" name="keterangan"><br><br>
    <button type="submit" name="buat_sesi">Buat Sesi</button>
</form>

<?php elseif($menu=='rekap_absensi'): ?>
<h3>Rekap Absensi</h3>
<?php
if(isset($_GET['kelas_id'])){
    $kelas_id = $_GET['kelas_id'];
    $rekap = $conn->query("
        SELECT s.tanggal, s.keterangan, m.nim, m.nama, a.status
        FROM sesi_absensi s
        LEFT JOIN absensi a ON s.id=a.sesi_id
        LEFT JOIN mahasiswa m ON a.nim=m.nim
        WHERE s.kelas_id=$kelas_id
        ORDER BY s.tanggal DESC
    ");
    echo "<table><tr><th>Tanggal</th><th>Keterangan</th><th>NIM</th><th>Nama</th><th>Status</th></tr>";
    while($row=$rekap->fetch_assoc()){
        echo "<tr>
            <td>{$row['tanggal']}</td>
            <td>{$row['keterangan']}</td>
            <td>{$row['nim']}</td>
            <td>{$row['nama']}</td>
            <td>{$row['status']}</td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "<p>Pilih kelas untuk melihat rekap: </p>";
    $kelas_list = $conn->query("SELECT * FROM kelas WHERE dosen_id=$dosen_id");
    while($row=$kelas_list->fetch_assoc()){
        echo "<a href='?menu=rekap_absensi&kelas_id=".$row['id']."'>".$row['nama_kelas']."</a><br>";
    }
}
?>
<?php endif; ?>
</div>
</body>
</html>
