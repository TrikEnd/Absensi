<?php
session_start();
$conn = new mysqli("localhost","root","","praktikum");

if(isset($_POST['login'])){
    $nim = $_POST['nim'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE nim=? AND password=?");
    $stmt->bind_param("ss", $nim, $password);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows > 0){
        $user = $res->fetch_assoc();
        $_SESSION['user'] = $user;
        $_SESSION['role'] = 'mahasiswa';
        header("Location: dashboard_mahasiswa.php"); exit;
    } else {
        $error = "NIM atau password salah!";
    }
}
?>
<h2>Login Mahasiswa</h2>
<form method="POST">
    NIM: <input type="text" name="nim" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit" name="login">Login</button>
</form>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<a href="login_dosen.php">Login Dosen</a>
