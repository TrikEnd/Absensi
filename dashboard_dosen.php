<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'dosen'){
    header("Location: login_dosen.php"); exit;
}

$host="localhost"; $user="root"; $pass=""; $db="praktikum";
$conn=new mysqli($host,$user,$pass,$db);
$dosen_id = $_SESSION['user']['id'];

// ================= Handle Actions =================
// Tambah kelas baru
if(isset($_POST['tambah_kelas'])){
    $nama_kelas = $_POST['nama_kelas'];
    $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, dosen_id) VALUES (?, ?)");
    $stmt->bind_param("si",$nama_kelas,$dosen_id);
    if($stmt->execute()){ $msg = "Kelas berhasil ditambahkan!"; }
    else { $msg = "Gagal menambahkan kelas."; }
}

// Input absensi
if(isset($_POST['absensi'])){
    $kelas_id = $_POST['kelas_id'];
    $nim = $_POST['nim'];
    $status = $_POST['hadir'];

    // cek apakah kelas milik dosen
    $cek = $conn->query("SELECT * FROM kelas WHERE id=$kelas_id AND dosen_id=$dosen_id");
    if($cek->num_rows>0){
        $stmt = $conn->prepare("INSERT INTO absensi (kelas_id,nim,hadir,tanggal) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss",$kelas_id,$nim,$status);
        $stmt->execute();
        $msg="Absensi berhasil ditambahkan!";
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
    <a href="?menu=absensi">Input Absensi</a>
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
    <td>
        <!-- Bisa tambah fitur hapus kelas sendiri nanti -->
        -
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php elseif($menu=='tambah_kelas'): ?>
<h3>Tambah Kelas Baru</h3>
<form method="POST">
    Nama Kelas: <input type="text" name="nama_kelas" required>
    <button type="submit" name="tambah_kelas">Tambah</button>
</form>

<?php elseif($menu=='absensi'): ?>
<h3>Input Absensi</h3>
<form method="POST">
    Kelas: 
    <select name="kelas_id" required>
        <?php
        $kelas_saya2 = $conn->query("SELECT * FROM kelas WHERE dosen_id=$dosen_id");
        while($row=$kelas_saya2->fetch_assoc()){
            echo "<option value='".$row['id']."'>".$row['nama_kelas']."</option>";
        }
        ?>
    </select><br><br>
    NIM Mahasiswa: <input type="text" name="nim" required><br><br>
    Status Hadir: 
    <select name="hadir">
        <option value="Hadir">Hadir</option>
        <option value="Tidak Hadir">Tidak Hadir</option>
    </select><br><br>
    <button type="submit" name="absensi">Simpan Absensi</button>
</form>
<?php endif; ?>
</div>
</body>
</html>
