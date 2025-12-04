<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "praktikum";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim = $_POST['nim'];
    $nama = $_POST['nama'];
    $tanggal = $_POST['tanggal'];
    $hadir = $_POST['hadir'];

    // Prepare statement untuk keamanan
    $stmt = $conn->prepare("INSERT INTO absensi (nim, nama, tanggal, hadir) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nim, $nama, $tanggal, $hadir);

    if ($stmt->execute()) {
        echo "Absensi berhasil disimpan.<br><a href='rekap.php'>Lihat Rekap</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
